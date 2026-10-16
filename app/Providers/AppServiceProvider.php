<?php

namespace App\Providers;

use App\Models\DefectCategory;
use App\Models\DefectReason;
use App\Models\Downtime;
use App\Models\DowntimeCategory;
use App\Models\DowntimeReason;
use App\Models\Machine;
use App\Models\NgRecord;
use App\Models\Product;
use App\Models\ProductCycleTime;
use App\Models\ProductionLine;
use App\Models\ProductionRecord;
use App\Models\Shift;
use App\Models\SystemSetting;
use App\Models\User;
use App\Observers\AuditTrailObserver;
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
        // Register Universal Audit Trail Observer
        ProductionRecord::observe(AuditTrailObserver::class);
        Downtime::observe(AuditTrailObserver::class);
        NgRecord::observe(AuditTrailObserver::class);
        ProductionLine::observe(AuditTrailObserver::class);
        Machine::observe(AuditTrailObserver::class);
        Product::observe(AuditTrailObserver::class);
        ProductCycleTime::observe(AuditTrailObserver::class);
        Shift::observe(AuditTrailObserver::class);
        DefectCategory::observe(AuditTrailObserver::class);
        DefectReason::observe(AuditTrailObserver::class);
        DowntimeCategory::observe(AuditTrailObserver::class);
        DowntimeReason::observe(AuditTrailObserver::class);
        SystemSetting::observe(AuditTrailObserver::class);
        User::observe(AuditTrailObserver::class);
    }
}
