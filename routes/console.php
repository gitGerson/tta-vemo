<?php

use App\Services\PermissionSyncService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('permission:update', function (PermissionSyncService $permissionSyncService) {
    $this->info('Clearing config cache...');
    Artisan::call('config:clear');
    $this->output->write(Artisan::output());

    $permissionSyncService->sync(function (string $message): void {
        $this->info($message);
    });

    return 0;
})->purpose('Sync permissions and role templates from config/permission.php');
