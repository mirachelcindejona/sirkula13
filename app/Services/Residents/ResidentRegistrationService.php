<?php

namespace App\Services\Residents;

use App\Models\ResidentProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Kontrak A1 -> A2: attachOrCreateFor(User $user, array $residentData).
 * A1 membuat users berstatus pending dan login_enabled=false terlebih dahulu.
 * Pencocokan nomor HP tidak pernah otomatis mengaktifkan akun.
 */
class ResidentRegistrationService
{
    public static function normalizePhone(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '62')) {
            $digits = '0'.substr($digits, 2);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '0'.$digits;
        }

        if (! preg_match('/^08\d{8,13}$/', $digits)) {
            throw ValidationException::withMessages(['phone' => 'Nomor HP Indonesia tidak valid.']);
        }

        return $digits;
    }

    private function phoneVariants(string $phone): array
    {
        return [$phone, '62'.substr($phone, 1), '+62'.substr($phone, 1)];
    }

    private function normalizedData(User $user, array $data): array
    {
        $name = trim((string) ($data['full_name'] ?? $data['name'] ?? $user->name));
        $rt = strtoupper(trim((string) ($data['rt'] ?? '')));
        $rt = preg_replace('/^RT\s*/', '', $rt);
        if (ctype_digit($rt)) {
            $rt = str_pad($rt, 2, '0', STR_PAD_LEFT);
        }
        $house = trim((string) ($data['house_number'] ?? $data['houseNumber'] ?? ''));
        $phone = self::normalizePhone((string) ($data['phone'] ?? $user->phone ?? ''));

        if ($name === '' || mb_strlen($name) > 150 || ! preg_match('/^0[1-9]$/', $rt) || $house === '' || mb_strlen($house) > 30 || ! $phone) {
            throw ValidationException::withMessages(['resident' => 'Nama, nomor HP, RT 01–09, dan nomor rumah wajib valid.']);
        }

        if (! $user->phone || self::normalizePhone($user->phone) !== $phone) {
            throw ValidationException::withMessages(['phone' => 'Nomor HP pendaftaran harus sama dengan nomor HP akun.']);
        }

        $household = $data['household_name'] ?? $data['household'] ?? null;
        if ($household && mb_strlen((string) $household) > 150) {
            throw ValidationException::withMessages(['household_name' => 'Nama kepala keluarga terlalu panjang.']);
        }

        return [
            'full_name' => $name,
            'phone' => $phone,
            'rt' => $rt,
            'house_number' => $house,
            'household_name' => $household ?: null,
            'display_name' => $name,
        ];
    }

    public function attachOrCreateFor(User $user, array $residentData): ResidentProfile
    {
        return DB::transaction(function () use ($user, $residentData): ResidentProfile {
            $user = User::query()->lockForUpdate()->findOrFail($user->id);
            if ($user->status !== 'pending' || $user->login_enabled) {
                throw ValidationException::withMessages(['account' => 'Hanya akun pending dengan login nonaktif yang dapat didaftarkan.']);
            }

            $data = $this->normalizedData($user, $residentData);
            // Keunikan phone di users wajib dipastikan oleh A1; larang nomor yang terikat akun lain.
            $other = User::query()->where('id', '!=', $user->id)->whereIn('phone', $this->phoneVariants($data['phone']))->exists();
            if ($other) {
                throw ValidationException::withMessages(['phone' => 'Nomor HP sudah digunakan akun lain.']);
            }

            $own = ResidentProfile::query()->where('user_id', $user->id)->lockForUpdate()->first();
            if ($own) {
                // Idempoten; tidak menimpa profil aktif yang dulu dibuat operator.
                return $own;
            }

            $matches = ResidentProfile::query()->whereIn('phone', $this->phoneVariants($data['phone']))
                ->lockForUpdate()->get();
            if ($matches->count() > 1) {
                throw ValidationException::withMessages(['phone' => 'Ada lebih dari satu profil dengan nomor ini. Hubungi Superadmin.']);
            }
            $match = $matches->first();
            if ($match && $match->user_id) {
                throw ValidationException::withMessages(['phone' => 'Profil nomor ini sudah terhubung ke akun.']);
            }

            if ($match) {
                $match->update(['user_id' => $user->id]);
                ResidentAudit::record($user, 'resident_registration_attached_pending', ResidentProfile::class, $match->id, ['user_id' => null], ['user_id' => $user->id], 'Nomor HP cocok, menunggu pemeriksaan Superadmin');
                return $match;
            }

            $profile = ResidentProfile::query()->create($data + [
                'user_id' => $user->id,
                'status' => 'pending',
                'leaderboard_visible' => true,
            ]);
            ResidentAudit::record($user, 'resident_registration_created', ResidentProfile::class, $profile->id, null, ['status' => 'pending']);

            return $profile;
        });
    }

    /** Perbaikan dari A1 setelah pengguna yang sama sudah diautentikasi (bukan link ulang). */
    public function resubmit(User $user, array $residentData): ResidentProfile
    {
        return DB::transaction(function () use ($user, $residentData): ResidentProfile {
            $user = User::query()->lockForUpdate()->findOrFail($user->id);
            if ($user->status !== 'needs_info' || $user->login_enabled) {
                throw ValidationException::withMessages(['account' => 'Akun tidak sedang menunggu perbaikan.']);
            }
            $profile = ResidentProfile::query()->where('user_id', $user->id)->lockForUpdate()->firstOrFail();
            $data = $this->normalizedData($user, $residentData);
            if ($profile->resident_code && $profile->status === 'active') {
                // Data manual tetap stabil sampai Superadmin menyetujui kecocokan.
                if (self::normalizePhone($profile->phone) !== $data['phone']) {
                    throw ValidationException::withMessages(['phone' => 'Perubahan nomor untuk profil manual harus diperiksa pengurus.']);
                }
            } else {
                $profile->fill($data)->forceFill(['status' => 'pending', 'review_note' => null])->save();
            }
            $user->forceFill(['status' => 'pending', 'login_enabled' => false])->save();
            ResidentAudit::record($user, 'resident_registration_resubmitted', ResidentProfile::class, $profile->id, ['status' => 'needs_info'], ['status' => 'pending']);

            return $profile;
        });
    }

    /** Profil manual/pendaftaran baru diberi kode setelah sah; kode lama jangan diganti. */
    public function issueResidentCode(ResidentProfile $profile): string
    {
        if ($profile->resident_code) {
            return $profile->resident_code;
        }

        $code = 'W-'.str_pad((string) $profile->id, 6, '0', STR_PAD_LEFT);
        if (ResidentProfile::query()->where('resident_code', $code)->where('id', '!=', $profile->id)->exists()) {
            // Lindungi kode ID lama yang tidak selalu mengikuti ID numerik DB.
            $code = 'W-'.strtoupper(Str::random(12));
        }
        $profile->resident_code = $code;
        $profile->save();

        return $code;
    }

    public function approve(ResidentProfile $profile, User $reviewer, ?string $note = null): ResidentProfile
    {
        return DB::transaction(function () use ($profile, $reviewer, $note): ResidentProfile {
            $profile = ResidentProfile::query()->lockForUpdate()->findOrFail($profile->id);
            $account = $profile->user()->lockForUpdate()->first();
            if (! $account || ! in_array($account->status, ['pending', 'needs_info'], true) || $reviewer->id === $account->id) {
                throw ValidationException::withMessages(['account' => 'Pendaftaran ini tidak dapat disetujui.']);
            }
            $before = ['status' => $account->status, 'profile_status' => $profile->status];
            $code = $this->issueResidentCode($profile);
            $profile->forceFill(['status' => 'active', 'approved_by' => $reviewer->id, 'approved_at' => now(), 'review_note' => $note])->save();
            $account->forceFill([
                'user_code' => $account->user_code ?: $code,
                'status' => 'active',
                'login_enabled' => true,
            ])->save();
            $roleId = DB::table('roles')->where('slug', 'warga')->value('id');
            if (! $roleId) {
                throw ValidationException::withMessages(['role' => 'Role warga belum tersedia. Jalankan RoleSeeder.']);
            }
            DB::table('role_user')->updateOrInsert(
                ['user_id' => $account->id, 'role_id' => $roleId],
                ['assigned_by' => $reviewer->id, 'reason' => 'Persetujuan akun warga', 'assigned_at' => now(), 'revoked_at' => null, 'expires_at' => null, 'created_at' => now(), 'updated_at' => now()]
            );
            ResidentAudit::record($reviewer, 'resident_registration_approved', ResidentProfile::class, $profile->id, $before, ['status' => 'active', 'resident_code' => $code], $note);

            return $profile;
        });
    }

    public function review(ResidentProfile $profile, User $reviewer, string $decision, string $reason): ResidentProfile
    {
        if (! in_array($decision, ['needs_info', 'rejected'], true) || trim($reason) === '') {
            throw ValidationException::withMessages(['reason' => 'Alasan dan keputusan wajib diisi.']);
        }

        return DB::transaction(function () use ($profile, $reviewer, $decision, $reason): ResidentProfile {
            $profile = ResidentProfile::query()->lockForUpdate()->findOrFail($profile->id);
            $account = $profile->user()->lockForUpdate()->first();
            if (! $account || $reviewer->id === $account->id || ! in_array($account->status, ['pending', 'needs_info'], true)) {
                throw ValidationException::withMessages(['account' => 'Status pendaftaran tidak dapat diperiksa.']);
            }
            $before = ['status' => $account->status];
            $account->forceFill(['status' => $decision, 'login_enabled' => false])->save();
            if ($profile->resident_code && $profile->status === 'active') {
                // Profil manual tidak ikut ditolak karena transaksi lamanya tetap sah.
                if ($decision === 'rejected') {
                    $profile->user_id = null;
                }
                $profile->review_note = $reason;
                $profile->save();
            } else {
                $profile->forceFill(['status' => $decision, 'review_note' => $reason])->save();
            }
            ResidentAudit::record($reviewer, 'resident_registration_'.$decision, ResidentProfile::class, $profile->id, $before, ['status' => $decision], $reason);

            return $profile;
        });
    }

    /** Tautkan secara manual hanya setelah Superadmin memeriksa bukti kecocokan. */
    public function linkExisting(ResidentProfile $pending, ResidentProfile $manual, User $reviewer, string $reason): ResidentProfile
    {
        return DB::transaction(function () use ($pending, $manual, $reviewer, $reason): ResidentProfile {
            $pending = ResidentProfile::query()->lockForUpdate()->findOrFail($pending->id);
            $manual = ResidentProfile::query()->lockForUpdate()->findOrFail($manual->id);
            $account = $pending->user()->lockForUpdate()->first();
            if (! $account || $account->id === $reviewer->id || ! in_array($account->status, ['pending', 'needs_info'], true)
                || $manual->id === $pending->id || $manual->user_id || $manual->status !== 'active' || ! $manual->resident_code) {
                throw ValidationException::withMessages(['profile' => 'Profil target harus aktif, manual, dan belum terhubung ke akun.']);
            }
            if (trim($reason) === '') {
                throw ValidationException::withMessages(['reason' => 'Dasar pencocokan wajib diisi.']);
            }
            // Jangan menghapus profil yang sudah punya catatan transaksi atau kode warga.
            if ($pending->resident_code || $pending->status === 'active') {
                throw ValidationException::withMessages(['profile' => 'Profil asal tidak kosong. Perlu rekonsiliasi manual.']);
            }
            // Database menolak penghapusan jika ada relasi transaksi (RESTRICT), seluruh transaksi akan rollback.
            $pendingId = $pending->id;
            $pending->delete();
            $manual->user_id = $account->id;
            if (! $manual->phone) {
                $manual->phone = self::normalizePhone($account->phone);
            }
            $manual->review_note = $reason;
            $manual->save();
            ResidentAudit::record($reviewer, 'resident_registration_linked_manual', ResidentProfile::class, $manual->id,
                ['pending_profile_id' => $pendingId], ['user_id' => $account->id, 'resident_code' => $manual->resident_code], $reason);

            return $manual; // Akun TETAP pending; hanya approve() yang mengaktifkannya.
        });
    }
}
