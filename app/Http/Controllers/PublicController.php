<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Donor;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\ResidentProfile;
use App\Support\EcoRank;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('app.home');
        }

        $leaders = ResidentProfile::query()
            ->where('status', 'active')
            ->where('leaderboard_visible', true)
            ->withSum(['ecoPointTransactions as earned_points' => fn ($query) => $query->where('points_delta', '>', 0)], 'points_delta')
            ->orderByDesc('earned_points')
            ->limit(3)
            ->get();

        return view('pages.public.home', [
            'events' => Event::query()->where('status', 'published')->orderBy('event_date')->limit(2)->get(),
            'products' => Product::query()->with('seller')->where('status', 'published')->where('stock', '>', 0)->orderBy('name')->limit(3)->get(),
            'leaders' => $leaders,
        ]);
    }

    public function profile(): View
    {
        return view('pages.public.profile');
    }

    public function guide(): View
    {
        $materials = Material::query()
            ->where('is_active', true)
            ->with(['rates' => fn ($query) => $query->orderByDesc('effective_from')])
            ->orderBy('id')
            ->get();

        $priceOfficial = $materials->isNotEmpty() && $materials->every(
            fn (Material $material) => $material->rates->first()?->status === 'approved'
        );

        return view('pages.public.guide', compact('materials', 'priceOfficial'));
    }

    public function events(): View
    {
        $events = Event::query()
            ->where('status', 'published')
            ->with(['registrations' => fn ($query) => $query->where('status', '!=', 'cancelled')])
            ->orderBy('event_date')
            ->get();

        $profileId = auth()->user()?->residentProfile?->id;
        $mine = $profileId
            ? EventRegistration::query()->with('event')->where('resident_profile_id', $profileId)->latest('registered_at')->get()
            : collect();

        return view('pages.public.events', compact('events', 'mine'));
    }

    public function registerEvent(Request $request, Event $event): RedirectResponse
    {
        $profile = $this->activeCitizen($request);
        abort_unless($event->status === 'published', 404);

        $activeCount = $event->registrations()->where('status', '!=', 'cancelled')->count();
        if ($event->quota && $activeCount >= $event->quota) {
            return back()->with('warning', 'Kuota penuh. Pendaftaran tidak dapat dilanjutkan.');
        }

        $existing = EventRegistration::query()
            ->where('event_id', $event->id)
            ->where('resident_profile_id', $profile->id)
            ->first();

        if ($existing && $existing->status !== 'cancelled') {
            return back()->with('warning', 'Anda sudah terdaftar pada kegiatan ini.');
        }

        if ($existing) {
            $existing->forceFill([
                'status' => 'registered',
                'registered_at' => now(),
                'cancelled_at' => null,
            ])->save();
        } else {
            (new EventRegistration)->forceFill([
                'reference_code' => $this->code('REG'),
                'event_id' => $event->id,
                'resident_profile_id' => $profile->id,
                'status' => 'registered',
                'registered_at' => now(),
            ])->save();
        }

        return back()->with('success', 'Pendaftaran berhasil. Poin diberikan setelah hadir dan diverifikasi.');
    }

    public function cancelEvent(Request $request, Event $event): RedirectResponse
    {
        $profile = $this->activeCitizen($request);
        $registration = EventRegistration::query()
            ->where('event_id', $event->id)
            ->where('resident_profile_id', $profile->id)
            ->where('status', '!=', 'cancelled')
            ->first();

        abort_unless($registration, 404);
        $registration->forceFill(['status' => 'cancelled', 'cancelled_at' => now()])->save();

        return back()->with('success', 'Pendaftaran dibatalkan. Anda dapat mendaftar kembali selama kuota masih tersedia.');
    }

    public function catalog(): View
    {
        $products = Product::query()
            ->with(['seller', 'orders'])
            ->where('status', 'published')
            ->orderBy('name')
            ->get();

        return view('pages.public.catalog', compact('products'));
    }

    public function orderProduct(Request $request, Product $product): RedirectResponse
    {
        $profile = $this->activeCitizen($request);
        abort_unless($product->status === 'published', 404);

        $reserved = $product->orders()->whereNotIn('status', ['cancelled', 'declined', 'completed'])->sum('quantity');
        abort_if(((int) $product->stock - (int) $reserved) < 1, 422, 'Stok produk tidak tersedia.');

        (new ProductOrder)->forceFill([
            'reference_code' => $this->code('ORD'),
            'product_id' => $product->id,
            'buyer_profile_id' => $profile->id,
            'seller_profile_id' => $product->seller_profile_id,
            'quantity' => 1,
            'unit_price_snapshot' => $product->price,
            'total_amount' => $product->price,
            'status' => 'awaiting_seller',
            'created_by' => $request->user()->id,
        ])->save();

        return redirect()->route('citizen.orders')->with('success', 'Kode pesanan dibuat. Transaksi selesai setelah serah terima dikonfirmasi.');
    }

    public function leaderboard(): View
    {
        $rows = ResidentProfile::query()
            ->where('status', 'active')
            ->where('leaderboard_visible', true)
            ->withSum(['ecoPointTransactions as earned_points' => fn ($query) => $query->where('points_delta', '>', 0)], 'points_delta')
            ->withSum('ecoPointTransactions as point_balance', 'points_delta')
            ->orderByDesc('earned_points')
            ->get();

        $supporters = Donation::query()
            ->with('donor')
            ->where('status', 'verified')
            ->get()
            ->filter(fn (Donation $donation) => $donation->donor?->donor_type !== 'sponsor')
            ->sortByDesc('support_points_awarded')
            ->values();

        return view('pages.public.leaderboard', [
            'rows' => $rows,
            'supporters' => $supporters,
            'tiers' => EcoRank::tiers(),
        ]);
    }

    public function donation(): View
    {
        return view('pages.public.donation', [
            'total' => (float) Donation::query()->where('status', 'verified')->sum('amount'),
        ]);
    }

    public function storeDonation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'donorName' => ['required', 'string', 'max:180'],
            'amount' => ['required', 'integer', 'min:1000'],
            'purpose' => ['required', 'string', 'max:180'],
            'display' => ['required', 'in:Alias,Name,Anonim'],
            'alias' => ['nullable', 'string', 'max:120'],
            'confirm' => ['accepted'],
        ]);

        $profile = $request->user()?->residentProfile;
        $display = match ($data['display']) {
            'Name' => 'name',
            'Anonim' => 'anonim',
            default => 'alias',
        };

        DB::transaction(function () use ($data, $profile, $display, $request): void {
            $donor = $profile
                ? Donor::query()->firstOrNew(['resident_profile_id' => $profile->id])
                : new Donor;

            if (! $donor->exists) {
                $donor->forceFill([
                    'code' => $this->code('DNR'),
                    'resident_profile_id' => $profile?->id,
                    'donor_type' => 'individual',
                    'is_active' => true,
                ]);
            }

            $donor->forceFill([
                'name' => $data['donorName'],
                'display_mode' => $display,
                'public_alias' => $display === 'alias' ? ($data['alias'] ?: 'Sahabat RW 13') : null,
            ])->save();

            (new Donation)->forceFill([
                'reference_code' => $this->code('DON'),
                'donor_id' => $donor->id,
                'donor_name_snapshot' => $data['donorName'],
                'display_mode_snapshot' => $display,
                'alias_snapshot' => $donor->public_alias,
                'amount' => $data['amount'],
                'purpose' => $data['purpose'],
                'status' => 'pending_receipt',
                'support_points_awarded' => 0,
                'created_by' => $request->user()?->id,
                'note' => 'Catatan niat donasi dari halaman publik. Belum menjadi kas.',
            ])->save();
        });

        return back()->with('success', 'Catatan donasi terkirim. SupportPoints dan kas bertambah setelah bendahara mengonfirmasi uang diterima.');
    }

    private function activeCitizen(Request $request): ResidentProfile
    {
        $profile = $request->user()?->residentProfile;
        abort_unless($request->user()?->hasRole('warga') && $profile?->status === 'active', 403);

        return $profile;
    }

    private function code(string $prefix): string
    {
        return $prefix.'-'.now()->format('ymdHis').random_int(10, 99);
    }
}
