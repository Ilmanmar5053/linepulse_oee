<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DatabaseManagementController;
use App\Http\Controllers\Api\DowntimeController;
use App\Http\Controllers\Api\MasterDataController;
use App\Http\Controllers\Api\NgReportController;
use App\Http\Controllers\Api\ProductionController;
use App\Http\Controllers\Api\QualityController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// API v1 Routes
Route::prefix('api/v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::get('/dashboard/oee-kpi', [DashboardController::class, 'oeeKpi']);
    Route::get('/dashboard/oee-trend', [DashboardController::class, 'oeeTrend']);
    Route::get('/dashboard/six-big-losses', [DashboardController::class, 'sixBigLosses']);
    Route::get('/dashboard/pareto-downtime', [DashboardController::class, 'paretoDowntime']);
    Route::get('/dashboard/pareto-defects', [DashboardController::class, 'paretoDefects']);
    Route::get('/dashboard/machine-ranking', [DashboardController::class, 'machineRanking']);
    Route::get('/dashboard/line-ranking', [DashboardController::class, 'lineRanking']);
    Route::get('/dashboard/shift-comparison', [DashboardController::class, 'shiftComparison']);

    Route::get('/production-monitoring/realtime-status', [ProductionController::class, 'realtimeStatus']);
    Route::get('/production-records', [ProductionController::class, 'index']);
    Route::post('/production-records', [ProductionController::class, 'store']);
    Route::put('/production-records/{id}', [ProductionController::class, 'update']);
    Route::delete('/production-records/{id}', [ProductionController::class, 'destroy']);

    Route::get('/downtimes', [DowntimeController::class, 'index']);
    Route::post('/downtimes', [DowntimeController::class, 'store']);
    Route::put('/downtimes/{id}', [DowntimeController::class, 'update']);
    Route::delete('/downtimes/{id}', [DowntimeController::class, 'destroy']);

    // NG Detail Report Routes
    Route::get('/ng-reports/queue', [NgReportController::class, 'queue']);
    Route::get('/ng-reports/{id}', [NgReportController::class, 'show']);
    Route::post('/ng-reports', [NgReportController::class, 'store']);
    Route::delete('/ng-reports/{id}', [NgReportController::class, 'destroy']);

    Route::get('/quality-records', [QualityController::class, 'index']);
    Route::post('/quality-records', [QualityController::class, 'store']);

    Route::get('/reports/shift-summary', [ReportController::class, 'shiftSummary']);
    Route::get('/reports/trouble-summary', [ReportController::class, 'troubleSummary']);
    Route::get('/reports/export', [ReportController::class, 'export']);

    // User Management Routes
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::post('/users/generate-default', [UserController::class, 'generateDefaultUsers']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::get('/roles', [UserController::class, 'roles']);

    // Database & System Maintenance Routes
    Route::prefix('system')->group(function () {
        Route::get('/database-info', [DatabaseManagementController::class, 'getDatabaseInfo']);
        Route::post('/clean-transactions', [DatabaseManagementController::class, 'cleanTransactions']);
        Route::get('/backup-export', [DatabaseManagementController::class, 'exportBackup']);
        Route::post('/optimize-database', [DatabaseManagementController::class, 'optimizeDatabase']);
    });

    // Master Data Routes
    Route::prefix('master')->group(function () {
        Route::get('/plants', [MasterDataController::class, 'plants']);
        Route::post('/plants', [MasterDataController::class, 'storePlant']);
        Route::put('/plants/{id}', [MasterDataController::class, 'updatePlant']);
        Route::delete('/plants/{id}', [MasterDataController::class, 'destroyPlant']);

        Route::get('/production-lines', [MasterDataController::class, 'productionLines']);
        Route::post('/production-lines', [MasterDataController::class, 'storeProductionLine']);
        Route::put('/production-lines/{id}', [MasterDataController::class, 'updateProductionLine']);
        Route::delete('/production-lines/{id}', [MasterDataController::class, 'destroyProductionLine']);

        Route::get('/machines', [MasterDataController::class, 'machines']);
        Route::post('/machines', [MasterDataController::class, 'storeMachine']);
        Route::post('/machines/import', [MasterDataController::class, 'importMachines']);
        Route::put('/machines/{id}', [MasterDataController::class, 'updateMachine']);
        Route::delete('/machines/{id}', [MasterDataController::class, 'destroyMachine']);

        Route::get('/products', [MasterDataController::class, 'products']);
        Route::post('/products', [MasterDataController::class, 'storeProduct']);
        Route::put('/products/{id}', [MasterDataController::class, 'updateProduct']);
        Route::delete('/products/{id}', [MasterDataController::class, 'destroyProduct']);

        Route::get('/downtime-reasons', [MasterDataController::class, 'downtimeReasons']);
        Route::post('/downtime-reasons', [MasterDataController::class, 'storeDowntimeReason']);
        Route::put('/downtime-reasons/{id}', [MasterDataController::class, 'updateDowntimeReason']);
        Route::delete('/downtime-reasons/{id}', [MasterDataController::class, 'destroyDowntimeReason']);

        Route::get('/ng-sections', [MasterDataController::class, 'ngSections']);
        Route::post('/ng-sections', [MasterDataController::class, 'storeNgSection']);
        Route::post('/ng-sections/import', [MasterDataController::class, 'importNgSections']);
        Route::put('/ng-sections/{id}', [MasterDataController::class, 'updateNgSection']);
        Route::delete('/ng-sections/{id}', [MasterDataController::class, 'destroyNgSection']);

        Route::get('/defect-categories', [MasterDataController::class, 'defectCategories']);
        Route::get('/defect-reasons', [MasterDataController::class, 'defectReasons']);
        Route::post('/defect-reasons', [MasterDataController::class, 'storeDefectReason']);
        Route::post('/defect-reasons/update-countermeasure', [MasterDataController::class, 'updateCountermeasure']);
        Route::post('/defect-reasons/import', [MasterDataController::class, 'importDefectReasons']);
        Route::put('/defect-reasons/{id}', [MasterDataController::class, 'updateDefectReason']);
        Route::delete('/defect-reasons/{id}', [MasterDataController::class, 'destroyDefectReason']);
        Route::get('/shifts', [MasterDataController::class, 'shifts']);
        Route::post('/shifts', [MasterDataController::class, 'storeShift']);
        Route::put('/shifts/{id}', [MasterDataController::class, 'updateShift']);
        Route::delete('/shifts/{id}', [MasterDataController::class, 'destroyShift']);
        Route::get('/groups', [MasterDataController::class, 'groups']);
        Route::post('/groups', [MasterDataController::class, 'storeGroup']);
        Route::put('/groups/{id}', [MasterDataController::class, 'updateGroup']);
        Route::delete('/groups/{id}', [MasterDataController::class, 'destroyGroup']);
        Route::get('/settings', [MasterDataController::class, 'settings']);
        Route::post('/settings', [MasterDataController::class, 'updateSetting']);
        Route::get('/company-profile', [MasterDataController::class, 'getCompanyProfile']);
        Route::post('/company-profile', [MasterDataController::class, 'saveCompanyProfile']);
    });
    Route::get('/company-profile', [MasterDataController::class, 'getCompanyProfile']);
    Route::post('/company-profile', [MasterDataController::class, 'saveCompanyProfile']);
});

// Legacy / Alias Endpoints
Route::prefix('api')->group(function () {
    Route::get('/dashboard/oee', [DashboardController::class, 'oeeKpi']);
    Route::get('/dashboard/production', [DashboardController::class, 'lineRanking']);
    Route::get('/dashboard/downtime', [DashboardController::class, 'paretoDowntime']);
    Route::get('/dashboard/quality', [DashboardController::class, 'paretoDefects']);
    Route::get('/machines', [MasterDataController::class, 'machines']);
    Route::get('/production-lines', [MasterDataController::class, 'productionLines']);
    Route::get('/production', [ProductionController::class, 'index']);
    Route::post('/production', [ProductionController::class, 'store']);
    Route::get('/downtime', [DowntimeController::class, 'index']);
    Route::post('/downtime', [DowntimeController::class, 'store']);
    Route::get('/quality', [QualityController::class, 'index']);
    Route::post('/quality', [QualityController::class, 'store']);
});

// SPA View Fallback
Route::fallback(function () {
    return view('app');
});
