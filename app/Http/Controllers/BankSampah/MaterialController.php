<?php

namespace App\Http\Controllers\BankSampah;

use App\Http\Controllers\Controller;
use App\Http\Requests\BankSampah\StoreMaterialRequest;
use App\Http\Requests\BankSampah\UpdateMaterialRequest;
use App\Models\Material;
use App\Models\MaterialRate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaterialController extends Controller
{
    /**
     * Menampilkan daftar material.
     */
    public function index(Request $request): View
    {
        $materials = Material::query()
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $search = $request->input('search');

                $query->where(function (Builder $subQuery) use ($search) {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('short_name', 'like', "%{$search}%");
                });
            })
            ->when(
                $request->input('status') === 'active',
                fn (Builder $query) => $query->where('is_active', true)
            )
            ->when(
                $request->input('status') === 'inactive',
                fn (Builder $query) => $query->where('is_active', false)
            )
            ->orderBy('name')
            ->listing();

        return view('modules.bank-sampah.materials.index', compact('materials'));
    }

    /**
     * Menampilkan form tambah material.
     */
    public function create(): View
    {
        return view('modules.bank-sampah.materials.create');
    }

    /**
     * Menyimpan material baru.
     */
    public function store(StoreMaterialRequest $request): RedirectResponse
    {
        $material = Material::create($request->safe()->except(['buy_price_per_kg', 'eco_points_per_kg']));
        $this->saveRate($material, $request);

        return redirect()
            ->route('admin.materials.index')
            ->with('success', 'Material, harga, dan poin berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit material.
     */
    public function edit(Material $material): View
    {
        return view('modules.bank-sampah.materials.edit', [
            'material' => $material,
            'rate' => $material->rates()->orderByDesc('effective_from')->first(),
        ]);
    }

    /**
     * Memperbarui data material.
     */
    public function update(
        UpdateMaterialRequest $request,
        Material $material
    ): RedirectResponse {
        $material->update($request->safe()->except(['buy_price_per_kg', 'eco_points_per_kg']));
        $this->saveRate($material, $request);

        return redirect()
            ->route('admin.materials.index')
            ->with('success', 'Material, harga, dan poin berhasil diperbarui.');
    }

    private function saveRate(Material $material, Request $request): void
    {
        $rate = MaterialRate::query()->firstOrNew([
            'material_id' => $material->id,
            'effective_from' => now()->toDateString(),
        ]);
        $rate->forceFill([
            'buy_price_per_kg' => $request->input('buy_price_per_kg'),
            'eco_points_per_kg' => $request->input('eco_points_per_kg'),
            'donation_multiplier' => 1.5,
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'reason' => 'Ditetapkan bersama jenis material.',
        ])->save();
    }

    /**
     * Mengaktifkan atau menonaktifkan material.
     */
    public function toggleStatus(Material $material): RedirectResponse
    {
        $material->update([
            'is_active' => ! $material->is_active,
        ]);

        $status = $material->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('admin.materials.index')
            ->with('success', "Material berhasil {$status}.");
    }
}
