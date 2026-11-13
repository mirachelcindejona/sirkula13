<?php

namespace App\Providers;

use App\Support\Listing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
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
        Paginator::defaultView('vendor.pagination.sirkula');

        Builder::macro('listing', function (string $pageName = 'page') {
            /** @var Builder $this */
            return $this->paginate(Listing::perPage(), ['*'], $pageName)->withQueryString();
        });
    }
}
