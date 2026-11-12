<?php

namespace App\Http\Controllers\BankSampah;

use App\Http\Controllers\Controller;
use App\Http\Requests\BankSampah\StoreMaterialRequest;
use App\Http\Requests\BankSampah\UpdateMaterialRequest;
use App\Models\Material;
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
            ->paginate(10)
            ->withQueryString();

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
        Material::create($request->validated());

        return redirect()
            ->route('bank-sampah.materials.index')
            ->with('success', 'Material berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit material.
     */
    public function edit(Material $material): View
    {
        return view(
            'modules.bank-sampah.materials.edit',
            compact('material')
        );
    }

    /**
     * Memperbarui data material.
     */
    public function update(
        UpdateMaterialRequest $request,
        Material $material
    ): RedirectResponse {
        $material->update($request->validated());

        return redirect()
            ->route('bank-sampah.materials.index')
            ->with('success', 'Material berhasil diperbarui.');
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
            ->route('bank-sampah.materials.index')
            ->with('success', "Material berhasil {$status}.");
    }
}
