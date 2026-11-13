<?php

namespace App\Http\Controllers;

use App\Exceptions\UserMessage;
use App\Models\EcoPointTransaction;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\EventRegistration;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\ResidentProfile;
use App\Support\Audit;
use App\Support\Listing;
use App\Support\Notices;
use App\Support\OrderFlow;
use App\Support\ProductWriter;
use App\Support\Uploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function dashboard(): View
    {
        return view('pages.program.dashboard', [
            'publishedEvents' => Event::query()->where('status', 'published')->count(),
            'drafts' => Event::query()->where('status', 'draft')->count() + Product::query()->where('status', 'draft')->count(),
            'products' => Product::query()->where('status', 'published')->count(),
        ]);
    }

    public function events(): View
    {
        $events = Event::query()->withCount('attendances')->orderByDesc('event_date');
        Listing::search($events, ['title', 'location', 'category']);
        if ($status = Listing::oneOf('status', ['draft', 'published'])) {
            $events->where('status', $status);
        }

        return view('pages.program.events', [
            'events' => $events->listing(),
        ]);
    }

    public function createEvent(): View
    {
        return view('pages.program.event-create');
    }

    public function storeEvent(Request $request): RedirectResponse
    {
        $event = $this->saveEvent(new Event, $request);

        return redirect()->route('program.events.show', $event)->with('success', $event->status === 'published' ? 'Kegiatan langsung tayang.' : 'Kegiatan disimpan sebagai draft.');
    }

    public function showEvent(Event $event): View
    {
        $attended = $event->attendances()->pluck('resident_profile_id');

        return view('pages.program.event-show', [
            'event' => $event,
            'residents' => ResidentProfile::query()->where('status', 'active')->orderBy('full_name')->get(),
            'attended' => $attended,
            'rows' => $event->attendances()->with('residentProfile')->latest()->get(),
        ]);
    }

    public function updateEvent(Request $request, Event $event): RedirectResponse
    {
        abort_if($event->hasStarted(), 403, 'Kegiatan yang sudah berlangsung tidak dapat diubah.');
        $before = $event->only(['title', 'event_date', 'location', 'status']);
        $this->saveEvent($event, $request);
        Audit::record('Mengubah kegiatan '.$event->reference_code, $event, $before, $event->only(['title', 'event_date', 'location', 'status']));

        return redirect()->route('program.events.show', $event)->with('success', 'Kegiatan diperbarui.');
    }

    public function storeAttendance(Request $request, Event $event): RedirectResponse
    {
        $ids = $request->validate([
            'residents' => ['required', 'array', 'min:1'],
            'residents.*' => ['integer', 'exists:resident_profiles,id'],
        ])['residents'];

        $existing = $event->attendances()->pluck('resident_profile_id')->all();
        $added = 0;

        DB::transaction(function () use ($request, $event, $ids, $existing, &$added): void {
            foreach (array_unique($ids) as $profileId) {
                if (in_array((int) $profileId, $existing, true)) {
                    continue;
                }
                $attendance = EventAttendance::query()->create([
                    'reference_code' => 'HDR-'.$event->id.'-'.$profileId,
                    'event_id' => $event->id,
                    'resident_profile_id' => $profileId,
                    'status' => 'attended',
                    'checked_in_at' => now(),
                    'created_by' => $request->user()->id,
                    'verified_by' => $request->user()->id,
                    'verified_at' => now(),
                ]);
                EcoPointTransaction::query()->create([
                    'reference_code' => 'ECO-'.$attendance->reference_code,
                    'resident_profile_id' => $profileId,
                    'points_delta' => $event->attendance_points ?: 50,
                    'kind' => 'earn',
                    'description' => 'Kehadiran '.$event->title,
                    'pointable_type' => $attendance->getMorphClass(),
                    'pointable_id' => $attendance->id,
                    'created_by' => $request->user()->id,
                    'approved_by' => $request->user()->id,
                ]);
                EventRegistration::query()
                    ->where('event_id', $event->id)
                    ->where('resident_profile_id', $profileId)
                    ->where('status', '!=', 'cancelled')
                    ->update(['status' => 'attended']);
                $added++;
            }
        });

        Audit::record('Mencatat kehadiran '.$event->reference_code, $event, null, ['jumlah_baru' => $added]);

        return back()->with('success', $added.' warga ditandai hadir dan poin langsung masuk.');
    }

    public function catalog(): View
    {
        $products = Product::query()->with('seller')->latest();
        Listing::search($products, ['name', 'code'], ['seller' => 'full_name']);
        if ($status = Listing::oneOf('status', ['draft', 'published'])) {
            $products->where('status', $status);
        }

        return view('pages.program.catalog', [
            'products' => $products->listing(),
        ]);
    }

    public function createProduct(): View
    {
        return view('pages.program.product-form', [
            'product' => null,
            'audience' => 'pengurus',
            'action' => route('program.catalog.store'),
            'back' => route('program.catalog'),
            'phone' => null,
        ]);
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        $data = $request->validate(ProductWriter::rules(true), ProductWriter::messages());
        $product = ProductWriter::save(new Product, $data, $request, [
            'owner_kind' => 'rw',
            'seller_profile_id' => null,
            'contact_name' => $data['contact_name'],
            'contact_phone' => $data['contact_phone'],
            'created_by' => $request->user()->id,
        ]);

        Audit::record('Menambah produk '.$product->code, $product, null, ['status' => $product->status, 'harga' => $product->price]);

        return redirect()->route('program.catalog')->with('success', $product->status === 'published' ? 'Produk pengurus langsung tayang di katalog.' : 'Produk disimpan sebagai draft.');
    }

    public function editProduct(Product $product): View
    {
        $this->rwProduct($product);

        return view('pages.program.product-form', [
            'product' => $product,
            'audience' => 'pengurus',
            'action' => route('program.catalog.update', $product),
            'back' => route('program.catalog'),
            'phone' => $product->contact_phone,
        ]);
    }

    public function updateProduct(Request $request, Product $product): RedirectResponse
    {
        $this->rwProduct($product);
        $data = $request->validate(ProductWriter::rules(true), ProductWriter::messages());
        $before = $product->only(['name', 'price', 'stock', 'status']);
        ProductWriter::save($product, $data, $request, [
            'owner_kind' => 'rw',
            'seller_profile_id' => null,
            'contact_name' => $data['contact_name'],
            'contact_phone' => $data['contact_phone'],
            'created_by' => $product->created_by ?: $request->user()->id,
        ]);
        Audit::record('Mengubah produk '.$product->code, $product, $before, $product->only(['name', 'price', 'stock', 'status']));

        return redirect()->route('program.catalog')->with('success', 'Produk pengurus diperbarui.');
    }

    public function completeOrder(ProductOrder $order): RedirectResponse
    {
        $order->loadMissing('product');
        abort_unless($order->product && $order->product->owner_kind !== 'warga', 403, 'Pesanan warga ditandai oleh penjualnya. Pengurus memantau dan bisa menahan transaksi yang janggal.');

        try {
            OrderFlow::complete($order);
        } catch (UserMessage $exception) {
            return back()->with('warning', $exception->getMessage());
        }

        Audit::record('Menandai pesanan diterima '.$order->reference_code, $order, null, ['status' => 'completed']);

        return back()->with('success', 'Pesanan ditandai sudah diterima pembeli.');
    }

    public function orders(): View
    {
        $orders = ProductOrder::query()->with(['product', 'buyer', 'seller'])->latest();
        Listing::search($orders, ['reference_code', 'external_buyer_name'], ['product' => 'name', 'buyer' => 'full_name']);
        if ($status = Listing::oneOf('status', ['awaiting_seller', 'awaiting_buyer', 'held_for_review', 'completed', 'cancelled'])) {
            $orders->where('status', $status);
        }

        return view('pages.program.orders', [
            'orders' => $orders->listing(),
        ]);
    }

    public function holdOrder(Request $request, ProductOrder $order): RedirectResponse
    {
        $reason = $request->validate(['reason' => ['required', 'string', 'max:500']])['reason'];
        $before = ['status' => $order->status];
        $order->forceFill([
            'status' => 'held_for_review',
            'held_reason' => $reason,
            'reviewed_by' => $request->user()->id,
        ])->save();
        Audit::record('Menahan pesanan '.$order->reference_code, $order, $before, ['status' => 'held_for_review', 'alasan' => $reason], $reason);
        $this->notifyOrderParties($order, 'Pesanan ditahan', 'Pesanan '.$order->reference_code.' ditahan pengurus. '.$reason);

        return back()->with('success', 'Pesanan ditahan. Warga yang terlibat mendapat pemberitahuan.');
    }

    public function releaseOrder(Request $request, ProductOrder $order): RedirectResponse
    {
        abort_unless($order->status === 'held_for_review', 403);
        $order->forceFill([
            'status' => 'awaiting_seller',
            'held_reason' => null,
            'reviewed_by' => $request->user()->id,
        ])->save();
        Audit::record('Melepas tahan pesanan '.$order->reference_code, $order, ['status' => 'held_for_review'], ['status' => 'awaiting_seller']);
        $this->notifyOrderParties($order, 'Pemeriksaan dilepas', 'Pemeriksaan pesanan '.$order->reference_code.' sudah dilepas. Transaksi bisa dilanjutkan.');

        return back()->with('success', 'Pesanan dikembalikan ke alur biasa.');
    }

    private function rwProduct(Product $product): void
    {
        abort_if($product->owner_kind === 'warga', 403, 'Produk warga diubah oleh pemiliknya. Pengurus memantau pesanan dan bisa menahan transaksi yang janggal.');
    }

    private function notifyOrderParties(ProductOrder $order, string $title, string $body): void
    {
        $order->loadMissing(['product.creator', 'product.seller.user', 'buyer.user', 'seller.user']);
        Notices::toProfile($order->buyer, $title, $body, route('citizen.orders'));
        $product = $order->product;
        $seller = $product?->owner_kind === 'warga' ? $product->seller?->user : $product?->creator;
        if ($seller && $seller->id !== $order->buyer?->user_id) {
            Notices::send(
                $seller,
                $title,
                $body,
                $product?->owner_kind === 'warga' ? route('citizen.products') : route('program.orders')
            );
        }
    }

    private function saveEvent(Event $event, Request $request): Event
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'category' => ['nullable', 'string', 'max:80'],
            'event_date' => ['required', 'date'],
            'event_time' => ['nullable', 'string', 'max:80'],
            'location' => ['required', 'string', 'max:180'],
            'quota' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:3000'],
            'attendance_points' => ['required', 'integer', 'min:0', 'max:500', 'multiple_of:2'],
            'poster' => Uploads::rules(),
            'publish' => ['required', 'in:draft,published'],
        ], Uploads::messages('poster'));

        $poster = $event->poster_path;
        if ($request->hasFile('poster')) {
            $poster = Uploads::store($request->file('poster'), 'events');
        }

        $event->forceFill([
            'reference_code' => $event->reference_code ?: 'KGT-'.now()->format('ymdHis'),
            'category' => $data['category'] ?? null,
            'title' => $data['title'],
            'event_date' => $data['event_date'],
            'event_time' => $data['event_time'] ?? null,
            'location' => $data['location'],
            'quota' => $data['quota'] ?? null,
            'description' => $data['description'] ?? null,
            'poster_path' => $poster,
            'attendance_points' => $data['attendance_points'],
            'status' => $data['publish'],
            'created_by' => $event->created_by ?: $request->user()->id,
            'approved_by' => $data['publish'] === 'published' ? $request->user()->id : null,
            'published_at' => $data['publish'] === 'published' ? ($event->published_at ?? now()) : null,
        ])->save();

        return $event;
    }
}
