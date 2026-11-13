<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductWriter
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(bool $namedContact): array
    {
        return [
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0', 'max:100000000'],
            'stock' => ['required', 'integer', 'min:0', 'max:10000'],
            'contact_name' => $namedContact ? ['required', 'string', 'max:150'] : ['nullable', 'string', 'max:150'],
            'contact_phone' => ['required', 'string', 'regex:/^[0-9+()\-\s]{9,25}$/'],
            'photo' => Uploads::rules(),
            'publish' => ['required', 'in:draft,published'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return array_merge(Uploads::messages('photo'), [
            'name.required' => 'Nama produk wajib diisi.',
            'price.required' => 'Harga wajib diisi.',
            'price.max' => 'Harga terlalu besar.',
            'stock.required' => 'Stok wajib diisi.',
            'stock.max' => 'Stok terlalu besar.',
            'contact_name.required' => 'Nama yang bisa dihubungi wajib diisi.',
            'contact_phone.required' => 'Nomor WhatsApp wajib diisi supaya pembeli bisa menghubungi penjual.',
            'contact_phone.regex' => 'Nomor WhatsApp belum sesuai. Contoh: 081234567890.',
            'publish.required' => 'Pilih simpan sebagai draft atau tayangkan.',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $owner
     */
    public static function save(Product $product, array $data, Request $request, array $owner): Product
    {
        return DB::transaction(function () use ($product, $data, $request, $owner): Product {
            $locked = $product->exists
                ? Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail()
                : $product;

            $open = $locked->exists
                ? (int) $locked->orders()->whereNotIn('status', ['cancelled', 'declined', 'completed'])->sum('quantity')
                : 0;

            if ((int) $data['stock'] < $open) {
                throw new HttpResponseException(back()->withInput()->withErrors([
                    'stock' => 'Stok tidak boleh lebih kecil dari pesanan yang masih berjalan ('.$open.').',
                ]));
            }

            $path = $locked->image_path;
            if ($request->hasFile('photo')) {
                $path = Uploads::store($request->file('photo'), 'products');
            }

            $locked->forceFill(array_merge($owner, [
                'code' => $locked->code ?: 'PRD-'.now()->format('ymdHis'),
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'stock' => $data['stock'],
                'image_path' => $path,
                'status' => $data['publish'],
                'approved_by' => $data['publish'] === 'published' ? $request->user()->id : $locked->approved_by,
                'published_at' => $data['publish'] === 'published' ? ($locked->published_at ?? now()) : null,
            ]))->save();

            return $locked;
        });
    }
}
