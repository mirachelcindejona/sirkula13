<?php

namespace App\Http\Controllers;

use App\Models\ResidentProfile;
use App\Support\Home;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('pages.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:72'],
        ]);

        $key = 'login:'.Str::lower($credentials['login']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => 'Terlalu banyak percobaan. Coba lagi beberapa menit lagi.']);
        }

        $field = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $user = User::query()->where($field, $credentials['login'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($key, 60);

            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => 'Email, nomor HP, atau kata sandi tidak sesuai.']);
        }

        if (in_array($user->status, ['inactive', 'rejected'], true) || ! $user->login_enabled) {
            RateLimiter::hit($key, 60);

            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => 'Akun ini tidak dapat digunakan. Hubungi pengurus RW.']);
        }

        RateLimiter::clear($key);
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route(Home::routeName($user)));
    }

    public function registerForm(): View
    {
        return view('pages.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'regex:/^[0-9+()\-\s]{9,25}$/', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
            'rt' => ['required', 'string', 'in:01,02,03,04,05,06,07,08,09'],
            'house_number' => ['required', 'string', 'max:30'],
            'household_name' => ['nullable', 'string', 'max:150'],
            'consent' => ['accepted'],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'status' => 'pending',
            'login_enabled' => true,
        ]);

        $roleId = Role::query()->where('slug', 'warga')->value('id');
        if ($roleId) {
            $user->roles()->attach($roleId, [
                'reason' => 'Pendaftaran mandiri warga.',
                'assigned_at' => now(),
            ]);
        }

        ResidentProfile::query()->create([
            'user_id' => $user->id,
            'full_name' => $data['name'],
            'phone' => $data['phone'],
            'rt' => $data['rt'] ?? null,
            'house_number' => $data['house_number'],
            'household_name' => $data['household_name'] ?? null,
            'display_name' => $data['name'],
            'donor_display' => 'alias',
            'status' => 'pending',
            'created_by' => $user->id,
        ]);

        return redirect()
            ->route('login')
            ->with('success', 'Pendaftaran terkirim. Akun bisa digunakan setelah pengurus memeriksa dan menyetujuinya.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('public.home');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $key = 'password:'.Str::lower($data['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->with('warning', 'Terlalu banyak permintaan. Coba lagi beberapa menit lagi.');
        }
        RateLimiter::hit($key, 60);

        try {
            Password::sendResetLink(['email' => $data['email']]);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('warning', 'Pemulihan lewat email belum dapat dikirim saat ini. Hubungi pengurus RW untuk verifikasi identitas.');
        }

        return back()->with('success', 'Jika email terdaftar, petunjuk pemulihan sudah dikirim. Periksa kotak masuk dan folder spam.');
    }

    public function resetForm(Request $request, string $token): View
    {
        return view('pages.auth.reset', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Kata sandi sudah diperbarui. Silakan masuk.')
            : back()->with('warning', 'Tautan pemulihan tidak berlaku atau sudah kedaluwarsa.');
    }
}
