<?php

namespace App\Providers;

use App\Models\WorkOrderItem;
use App\Observers\WorkOrderItemObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        WorkOrderItem::observe(WorkOrderItemObserver::class);
    }
}
