<?php

namespace App\Providers;

use App\Models\AccountRequest;
use App\Observers\AccountRequestObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        AccountRequest::observe(AccountRequestObserver::class);
    }
}
