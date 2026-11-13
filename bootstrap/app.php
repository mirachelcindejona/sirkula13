<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\SecureHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);
        $middleware->web(append: [
            EnsureAccountIsActive::class,
            SecureHeaders::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(function () {
            $user = auth()->user();

            return $user ? route(\App\Support\Home::routeName($user)) : route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        $exceptions->render(function (HttpException $exception, Request $request) {
            if ($exception->getStatusCode() !== 403 || $request->expectsJson()) {
                return null;
            }

            $message = $exception->getMessage();
            if ($message === '' || $message === 'Forbidden') {
                $message = 'Halaman ini bukan untuk akun yang sedang dipakai.';
            }

            return response()->view('errors.403', ['message' => $message], 403);
        });
    })->create();
