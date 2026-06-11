<?php

use App\Services\PermissionSyncService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Push reminder notifications to users who linked Telegram. Requires a running
// scheduler (php artisan schedule:work or a system cron entry).
Schedule::command('telegram:dispatch-reminders')->dailyAt('08:00');

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
