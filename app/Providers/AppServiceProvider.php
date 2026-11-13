<?php

namespace App\Providers;

use App\Support\Listing;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        App::setLocale('id');

        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('Atur ulang kata sandi SIRKULA RW 13')
                ->greeting('Halo '.$notifiable->name.',')
                ->line('Ada permintaan untuk mengatur ulang kata sandi akun SIRKULA Anda.')
                ->action('Atur ulang kata sandi', $url)
                ->line('Tautan ini berlaku 60 menit. Kalau Anda tidak meminta ini, abaikan emailnya.')
                ->salutation('Pengurus SIRKULA RW 13');
        });

        Paginator::defaultView('vendor.pagination.sirkula');

        Builder::macro('listing', function (string $pageName = 'page') {
            /** @var Builder $this */
            return $this->paginate(Listing::perPage(), ['*'], $pageName)->withQueryString();
        });
    }
}
