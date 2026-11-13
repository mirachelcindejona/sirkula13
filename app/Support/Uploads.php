<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Uploads
{
    public const MAX_KILOBYTES = 2048;

    /**
     * @return array<int, string>
     */
    public static function rules(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'file',
            'mimes:jpg,jpeg,png,webp',
            'max:'.self::MAX_KILOBYTES,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(string $field): array
    {
        return [
            $field.'.required' => 'Foto wajib diunggah.',
            $field.'.mimes' => 'Foto harus berupa JPG, PNG, atau WEBP.',
            $field.'.max' => 'Ukuran foto maksimal 2 MB.',
        ];
    }

    public static function store(UploadedFile $file, string $directory): string
    {
        $name = now()->format('YmdHis').'-'.$file->hashName();

        return $file->storeAs(trim($directory, '/'), $name, 'public');
    }

    public static function url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        if (str_starts_with($path, 'uploads/') || str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return Storage::disk('public')->url($path);
    }
}
