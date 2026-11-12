@php $editing = isset($profile); @endphp
<div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
        <label for="full_name" class="mb-1 block font-semibold">Nama warga *</label>
        <input id="full_name" name="full_name" required maxlength="150" value="{{ old('full_name', $profile->full_name ?? '') }}" @readonly($editing && $profile->user_id) class="w-full rounded-xl border border-primary-200 p-3">
        @error('full_name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="phone" class="mb-1 block font-semibold">Nomor HP (opsional untuk manual)</label>
        <input id="phone" name="phone" inputmode="tel" maxlength="25" value="{{ old('phone', $profile->phone ?? '') }}" @readonly($editing && $profile->user_id) class="w-full rounded-xl border border-primary-200 p-3 disabled:bg-gray-100">
        @if ($editing && $profile->user_id)<p class="mt-1 text-xs text-muted">Nomor HP akun online diperbarui melalui A1.</p>@endif
    </div>
    <div>
        <label for="rt" class="mb-1 block font-semibold">RT *</label>
        <select id="rt" name="rt" required class="w-full rounded-xl border border-primary-200 p-3">
            <option value="">Pilih RT</option>
            @for ($n = 1; $n <= 9; $n++)
                @php $rt = str_pad((string) $n, 2, '0', STR_PAD_LEFT); @endphp
                <option value="{{ $rt }}" @selected(old('rt', $profile->rt ?? '') === $rt)>RT {{ $rt }}</option>
            @endfor
        </select>
    </div>
    <div>
        <label for="house_number" class="mb-1 block font-semibold">Nomor rumah *</label>
        <input id="house_number" name="house_number" required maxlength="30" placeholder="Contoh: 12A" value="{{ old('house_number', $profile->house_number ?? '') }}" class="w-full rounded-xl border border-primary-200 p-3">
    </div>
    <div>
        <label for="household_name" class="mb-1 block font-semibold">Nama kepala keluarga</label>
        <input id="household_name" name="household_name" maxlength="150" value="{{ old('household_name', $profile->household_name ?? '') }}" class="w-full rounded-xl border border-primary-200 p-3">
    </div>
    <div>
        <label for="display_name" class="mb-1 block font-semibold">Nama tampil / alias</label>
        <input id="display_name" name="display_name" maxlength="100" value="{{ old('display_name', $profile->display_name ?? '') }}" class="w-full rounded-xl border border-primary-200 p-3">
    </div>
    <div class="flex items-center gap-2 rounded-xl bg-primary-50 px-3 py-2 md:col-span-2">
        <input type="hidden" name="leaderboard_visible" value="0">
        <input type="checkbox" id="leaderboard_visible" name="leaderboard_visible" value="1" @checked((bool) old('leaderboard_visible', $profile->leaderboard_visible ?? true)) class="h-5 w-5">
        <label for="leaderboard_visible" class="text-sm">Izinkan nama tampil pada leaderboard (hanya alias, tanpa nomor HP/alamat)</label>
    </div>
    @unless ($editing)
    <div class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 p-3 md:col-span-2">
        <input type="checkbox" id="checked_duplicate" name="checked_duplicate" value="1" @checked(old('checked_duplicate')) required class="mt-1 h-5 w-5">
        <label for="checked_duplicate" class="text-sm">Saya sudah mencari nama, nomor HP, RT, dan nomor rumah untuk mencegah profil ganda. *</label>
    </div>
    @endunless
</div>
