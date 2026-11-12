<?php

namespace Tests\Feature\Residents;

use App\Models\ResidentProfile;
use App\Models\User;
use App\Services\Residents\ResidentRegistrationService;
use Database\Seeders\ResidentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ResidentModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Project belum memuat routes/modules/* dari routes/web.php (file bersama A1/core).
        Route::middleware('web')->group(base_path('routes/modules/residents.php'));
        Route::middleware('web')->group(base_path('routes/modules/readiness.php'));
        $this->seed(RoleSeeder::class);
    }

    private function user(string $phone, string $status = 'active'): User
    {
        $user = new User();
        $user->forceFill([
            'name' => 'Pengguna '.$phone, 'email' => null, 'password' => bcrypt('password'),
            'phone' => $phone, 'status' => $status, 'login_enabled' => $status === 'active',
        ])->save();

        return $user;
    }

    private function grant(User $user, string $role): void
    {
        DB::table('role_user')->insert([
            'user_id' => $user->id,
            'role_id' => DB::table('roles')->where('slug', $role)->value('id'),
            'assigned_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function residentData(string $phone): array
    {
        return ['name' => 'Ibu Sari', 'phone' => $phone, 'rt' => '03', 'houseNumber' => '12A', 'household' => 'Keluarga Sari'];
    }

    public function test_online_registration_creates_pending_profile_and_approval_activates_login(): void
    {
        $pending = $this->user('081234567801', 'pending');
        $admin = $this->user('081234567802');
        $this->grant($admin, 'superadmin');

        $service = app(ResidentRegistrationService::class);
        $profile = $service->attachOrCreateFor($pending, $this->residentData('+6281234567801'));
        $this->assertNull($profile->resident_code);
        $this->assertSame('pending', $profile->status);
        $this->assertFalse((bool) $pending->fresh()->login_enabled);
        $this->assertSame($profile->id, $service->attachOrCreateFor($pending, $this->residentData('081234567801'))->id);

        $this->actingAs($admin)->post(route('residents.approvals.review', $profile), [
            'decision' => 'approved', 'reason' => 'Data rumah dan identitas sesuai',
        ])->assertRedirect(route('residents.approvals.index'));
        $this->assertSame('active', $pending->fresh()->status);
        $this->assertTrue((bool) $pending->fresh()->login_enabled);
        $this->assertNotNull($profile->fresh()->resident_code);
        $this->assertDatabaseHas('role_user', ['user_id' => $pending->id, 'role_id' => DB::table('roles')->where('slug', 'warga')->value('id')]);
    }

    public function test_registration_matches_manual_phone_and_keeps_existing_resident_code(): void
    {
        $manual = ResidentProfile::query()->create([
            'resident_code' => 'W-001', 'full_name' => 'Ibu Sari', 'phone' => '081234567803',
            'rt' => '03', 'house_number' => '12A', 'status' => 'active',
        ]);
        $pending = $this->user('+6281234567803', 'pending');
        $service = app(ResidentRegistrationService::class);
        $linked = $service->attachOrCreateFor($pending, $this->residentData('081234567803'));
        $this->assertEquals($manual->id, $linked->id);
        $this->assertSame('W-001', $linked->resident_code);
        $this->assertSame('active', $linked->status); // layanan manual tetap aktif
        $this->assertSame('pending', $pending->fresh()->status); // akun online belum aktif
        $this->assertSame(1, ResidentProfile::count());
    }

    public function test_operator_can_create_manual_resident_but_cannot_approve_account(): void
    {
        $operator = $this->user('081234567804');
        $this->grant($operator, 'operator-bank-sampah');
        $this->actingAs($operator)->post(route('residents.store'), [
            'full_name' => 'Pak Deni', 'phone' => '', 'rt' => '02', 'house_number' => '9',
            'household_name' => 'Keluarga Deni', 'display_name' => 'Deni',
            'leaderboard_visible' => '0', 'checked_duplicate' => '1',
        ])->assertRedirect();
        $profile = ResidentProfile::firstOrFail();
        $this->assertNotNull($profile->resident_code);
        $this->assertNull($profile->user_id);
        $this->assertSame('active', $profile->status);
        $this->actingAs($operator)->get(route('residents.approvals.index'))->assertForbidden();
        $this->actingAs($operator)->get(route('readiness.index'))->assertForbidden();
    }

    public function test_manual_profile_link_is_explicit_and_preserves_code(): void
    {
        $admin = $this->user('081234567805');
        $this->grant($admin, 'superadmin');
        $pending = $this->user('081234567806', 'pending');
        $service = app(ResidentRegistrationService::class);
        $new = $service->attachOrCreateFor($pending, $this->residentData('081234567806'));
        $manual = ResidentProfile::query()->create([
            'resident_code' => 'W-777', 'full_name' => 'Ibu Sari', 'rt' => '03',
            'house_number' => '12A', 'phone' => null, 'status' => 'active',
        ]);
        $this->actingAs($admin)->post(route('residents.approvals.link', $new), [
            'target_profile_id' => $manual->id,
            'reason' => 'Kartu ID dan RT diverifikasi oleh pengurus',
        ])->assertRedirect(route('residents.approvals.index'));
        $this->assertDatabaseMissing('resident_profiles', ['id' => $new->id]);
        $this->assertSame($pending->id, $manual->fresh()->user_id);
        $this->assertSame('W-777', $manual->fresh()->resident_code);
        $this->assertSame('pending', $pending->fresh()->status);
    }

    public function test_need_info_and_rejection_keep_manual_record_active(): void
    {
        $admin = $this->user('081234567807');
        $pending = $this->user('081234567808', 'pending');
        $manual = ResidentProfile::query()->create([
            'resident_code' => 'W-100', 'full_name' => 'Ibu Sari', 'phone' => '081234567808',
            'rt' => '03', 'house_number' => '12A', 'status' => 'active',
        ]);
        $service = app(ResidentRegistrationService::class);
        $service->attachOrCreateFor($pending, $this->residentData('081234567808'));
        $service->review($manual, $admin, 'needs_info', 'Periksa nomor rumah');
        $this->assertSame('needs_info', $pending->fresh()->status);
        $this->assertSame('active', $manual->fresh()->status);
        $service->review($manual, $admin, 'rejected', 'Pendaftaran tidak sesuai identitas');
        $this->assertNull($manual->fresh()->user_id);
        $this->assertSame('rejected', $pending->fresh()->status);
        $this->assertSame('active', $manual->fresh()->status);
    }

    public function test_unauthorized_user_cannot_manage_residents(): void
    {
        $user = $this->user('081234567810');
        $this->actingAs($user)->get(route('residents.index'))->assertForbidden();
        $this->actingAs($user)->post(route('residents.store'), [])->assertForbidden();
    }

    public function test_duplicate_phone_cannot_be_registered_as_second_manual_profile(): void
    {
        $operator = $this->user('081234567811');
        $this->grant($operator, 'operator-bank-sampah');
        ResidentProfile::query()->create([
            'resident_code' => 'W-888', 'full_name' => 'Warga Lama', 'phone' => '081234567812',
            'rt' => '02', 'house_number' => '10', 'status' => 'active',
        ]);
        $this->actingAs($operator)->post(route('residents.store'), [
            'full_name' => 'Warga Baru', 'phone' => '+6281234567812', 'rt' => '02',
            'house_number' => '11', 'leaderboard_visible' => '1', 'checked_duplicate' => '1',
        ])->assertSessionHasErrors('phone');
        $this->assertSame(1, ResidentProfile::count());
    }

    public function test_readiness_requires_references_and_preserves_existing_progress(): void
    {
        $admin = $this->user('081234567809');
        $this->grant($admin, 'superadmin');
        $this->seed(ResidentSeeder::class);
        $rows = DB::table('readiness_items')->pluck('id');
        $checks = [];
        foreach ($rows as $index => $id) {
            $checks[$id] = ['completed' => $index === 0 ? '1' : '0', 'note' => $index === 0 ? 'Rapat RW 13 tanggal 12 November 2026' : ''];
        }
        $this->actingAs($admin)->put(route('readiness.update'), ['checks' => $checks])->assertRedirect(route('readiness.index'));
        $this->assertSame(1, DB::table('readiness_checks')->where('is_completed', true)->count());
        $this->seed(ResidentSeeder::class);
        $this->assertSame(1, DB::table('readiness_checks')->where('is_completed', true)->count());
        $checks[$rows[0]]['note'] = '';
        $this->actingAs($admin)->put(route('readiness.update'), ['checks' => $checks])->assertSessionHasErrors();
    }
}
