<?php

namespace App\Http\Controllers;

use App\Exceptions\UserMessage;
use App\Models\Deposit;
use App\Models\EcoPointTransaction;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\ResidentProfile;
use App\Models\Reward;
use App\Support\Audit;
use App\Support\Listing;
use App\Support\OrderFlow;
use App\Support\ProductWriter;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CitizenController extends Controller
{
    public function profile(): View
    {
        $profile = auth()->user()->residentProfile;

        return view('pages.citizen.profile', [
            'user' => auth()->user(),
            'profile' => $profile,
            'rank' => $profile?->rank(),
        ]);
    }

    public function deposits(): View
    {
        $profile = auth()->user()->residentProfile;
        $base = $profile
            ? Deposit::query()->with(['material', 'payout'])->where('resident_profile_id', $profile->id)
            : null;
        if ($base) {
            Listing::search($base, ['reference_code'], ['material' => 'name']);
            if ($type = Listing::oneOf('type', ['sale', 'donation'])) {
                $base->where('type', $type);
            }
        }
        $deposits = $base ? (clone $base)->latest()->listing() : new LengthAwarePaginator([], 0, Listing::perPage(), 1, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'query' => request()->query(),
        ]);
        $earned = $base ? (int) (clone $base)->where('accepted', true)->sum('eco_points_awarded') : 0;
        $amountDue = $base
            ? (float) \App\Models\Payout::query()->where('resident_profile_id', $profile->id)->where('status', '!=', 'paid')->sum(DB::raw('GREATEST(amount - paid_amount, 0)'))
            : 0;

        return view('pages.citizen.deposits', compact('deposits', 'earned', 'amountDue'));
    }

    public function points(): View
    {
        $profile = auth()->user()->residentProfile;
        $ledger = $profile
            ? tap(EcoPointTransaction::query()->where('resident_profile_id', $profile->id)->latest(), fn ($query) => Listing::search($query, ['description', 'reference_code']))->listing('riwayat')
            : new LengthAwarePaginator([], 0, Listing::perPage(), 1, [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'riwayat',
                'query' => request()->query(),
            ]);
        $rewards = Reward::query()->where('is_active', true)->orderBy('points_required')->get();

        return view('pages.citizen.points', [
            'profile' => $profile,
            'ledger' => $ledger,
            'rewards' => $rewards,
            'redemptions' => $profile
                ? $profile->rewardRedemptions()->with('reward')->latest()->get()
                : collect(),
        ]);
    }

    public function orders(): View
    {
        $profile = auth()->user()->residentProfile;
        $orders = $profile
            ? tap(ProductOrder::query()->with(['product', 'seller'])->where('buyer_profile_id', $profile->id)->latest(), fn ($query) => Listing::search($query, ['reference_code'], ['product' => 'name']))->listing()
            : new LengthAwarePaginator([], 0, Listing::perPage(), 1, [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]);

        return view('pages.citizen.orders', [
            'orders' => $orders,
            'approved' => $profile?->status === 'active',
        ]);
    }

    public function products(): View
    {
        $profile = auth()->user()->residentProfile;
        $products = $profile
            ? Product::query()->where('seller_profile_id', $profile->id)->where('owner_kind', 'warga')->latest()->listing('produk')
            : $this->emptyPage('produk');
        $sales = $profile
            ? ProductOrder::query()->with(['product', 'buyer'])->where('seller_profile_id', $profile->id)->latest()->listing('masuk')
            : $this->emptyPage('masuk');

        return view('pages.citizen.products', [
            'products' => $products,
            'sales' => $sales,
            'approved' => $profile?->status === 'active',
        ]);
    }

    public function createProduct(): View
    {
        $profile = $this->approvedProfile();

        return view('pages.program.product-form', [
            'product' => new Product(['contact_phone' => $profile->phone ?: auth()->user()->phone]),
            'audience' => 'warga',
            'action' => route('citizen.products.store'),
            'back' => route('citizen.products'),
            'phone' => $profile->phone ?: auth()->user()->phone,
        ]);
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        $profile = $this->approvedProfile();
        $data = $request->validate(ProductWriter::rules(false), ProductWriter::messages());
        $product = ProductWriter::save(new Product, $data, $request, [
            'owner_kind' => 'warga',
            'seller_profile_id' => $profile->id,
            'contact_name' => $profile->full_name,
            'contact_phone' => $data['contact_phone'],
            'created_by' => $request->user()->id,
        ]);
        Audit::record('Warga menambah produk '.$product->code, $product, null, ['status' => $product->status]);

        return redirect()->route('citizen.products')->with('success', $product->status === 'published' ? 'Produk Anda sudah tayang di katalog.' : 'Produk disimpan sebagai draft.');
    }

    public function editProduct(Product $product): View
    {
        $profile = $this->approvedProfile();
        $this->ownProduct($product, $profile);

        return view('pages.program.product-form', [
            'product' => $product,
            'audience' => 'warga',
            'action' => route('citizen.products.update', $product),
            'back' => route('citizen.products'),
            'phone' => $product->contact_phone ?: $profile->phone,
        ]);
    }

    public function updateProduct(Request $request, Product $product): RedirectResponse
    {
        $profile = $this->approvedProfile();
        $this->ownProduct($product, $profile);
        $data = $request->validate(ProductWriter::rules(false), ProductWriter::messages());
        ProductWriter::save($product, $data, $request, [
            'owner_kind' => 'warga',
            'seller_profile_id' => $profile->id,
            'contact_name' => $profile->full_name,
            'contact_phone' => $data['contact_phone'],
        ]);

        return redirect()->route('citizen.products')->with('success', 'Produk Anda diperbarui.');
    }

    public function completeSale(ProductOrder $order): RedirectResponse
    {
        $profile = $this->approvedProfile();
        abort_unless((int) $order->seller_profile_id === (int) $profile->id, 403, 'Hanya penjual yang bisa menandai pesanan ini.');

        try {
            OrderFlow::complete($order);
        } catch (UserMessage $exception) {
            return back()->with('warning', $exception->getMessage());
        }

        return back()->with('success', 'Pesanan ditandai sudah diterima pembeli. Stok berkurang.');
    }

    public function cancelSale(ProductOrder $order): RedirectResponse
    {
        $profile = $this->approvedProfile();
        abort_unless((int) $order->seller_profile_id === (int) $profile->id, 403, 'Hanya penjual yang bisa membatalkan pesanan ini.');

        return $this->cancelOrder($order, 'Dibatalkan oleh penjual.');
    }

    public function cancelPurchase(ProductOrder $order): RedirectResponse
    {
        $profile = $this->approvedProfile();
        abort_unless((int) $order->buyer_profile_id === (int) $profile->id, 403, 'Hanya pembeli yang bisa membatalkan pesanan ini.');

        return $this->cancelOrder($order, 'Dibatalkan oleh pembeli.');
    }

    private function cancelOrder(ProductOrder $order, string $reason): RedirectResponse
    {
        try {
            OrderFlow::cancel($order, auth()->user(), $reason);
        } catch (UserMessage $exception) {
            return back()->with('warning', $exception->getMessage());
        }

        return back()->with('success', 'Pesanan dibatalkan.');
    }

    private function approvedProfile(): ResidentProfile
    {
        $profile = auth()->user()->residentProfile;
        abort_unless(auth()->user()->hasRole('warga') && $profile, 403, 'Halaman ini untuk akun warga.');
        if ($profile->status !== 'active') {
            throw new HttpResponseException(back()->with('warning', 'Akun belum disetujui pengurus. Produk bisa ditambahkan setelah akun disetujui.'));
        }

        return $profile;
    }

    private function ownProduct(Product $product, ResidentProfile $profile): void
    {
        abort_unless($product->owner_kind === 'warga' && (int) $product->seller_profile_id === (int) $profile->id, 403, 'Produk ini bukan milik akun Anda.');
    }

    private function emptyPage(string $pageName): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, Listing::perPage(), 1, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'pageName' => $pageName,
            'query' => request()->query(),
        ]);
    }
}
