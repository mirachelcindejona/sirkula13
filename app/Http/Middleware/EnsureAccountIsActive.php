<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($user->status === 'inactive' || $user->status === 'rejected' || ! $user->login_enabled)) {
            $message = match (true) {
                $user->status === 'rejected' => 'Pendaftaran akun ini ditolak pengurus.',
                $user->status === 'inactive' => 'Akun ini dinonaktifkan pengurus. Hubungi pengurus RW jika ini tidak sesuai.',
                default => 'Akun ini tidak dapat digunakan. Hubungi pengurus RW.',
            };

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $errors = new ViewErrorBag;
            $errors->put('default', new MessageBag(['login' => [$message]]));
            $request->session()->flash('errors', $errors);

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 401);
            }

            return redirect()->route('login');
        }

        return $next($request);
    }
}
