<?php

namespace App\Console\Commands;

use App\Models\Maintenances;
use App\Models\MaintenancesDetails;
use Illuminate\Console\Command;

class RecalculateMaintenances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenances:recalculate 
                            {--status : Update old status values to new enum}
                            {--subtotal : Recalculate subtotals and totals}
                            {--all : Run all recalculations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate maintenance records: update statuses and subtotals';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $runAll = $this->option('all');
        $updateStatus = $this->option('status') || $runAll;
        $updateSubtotal = $this->option('subtotal') || $runAll;

        if (! $updateStatus && ! $updateSubtotal) {
            $this->error('Please specify at least one option: --status, --subtotal, or --all');

            return 1;
        }

        $this->info('Starting maintenance recalculation...');
        $this->newLine();

        if ($updateStatus) {
            $this->updateStatuses();
        }

        if ($updateSubtotal) {
            $this->recalculateSubtotals();
        }

        $this->newLine();
        $this->info('✅ Recalculation complete!');

        return 0;
    }

    /**
     * Update old status values to new enum values
     */
    protected function updateStatuses(): void
    {
        $this->info('📊 Updating status values...');

        // Map old statuses to new ones
        $statusMap = [
            'PENDING' => 'IN_PROGRESS',
            'PROCESS' => 'IN_PROGRESS',
            'APPROVED' => 'COMPLETED',
            'DONE' => 'COMPLETED',
            'CANCEL' => 'CANCELLED',
            'REJECTED' => 'CANCELLED',
        ];

        $updated = 0;

        foreach ($statusMap as $oldStatus => $newStatus) {
            $count = Maintenances::where('status', $oldStatus)->count();

            if ($count > 0) {
                Maintenances::where('status', $oldStatus)->update(['status' => $newStatus]);
                $this->line("  - Updated {$count} records from '{$oldStatus}' to '{$newStatus}'");
                $updated += $count;
            }
        }

        if ($updated === 0) {
            $this->line('  No status updates needed.');
        } else {
            $this->info("  Total: {$updated} statuses updated.");
        }
    }

    /**
     * Recalculate subtotals for detail items and totals for maintenances
     */
    protected function recalculateSubtotals(): void
    {
        $this->info('💰 Recalculating subtotals and totals...');

        // Update all maintenance details subtotals
        $details = MaintenancesDetails::all();
        $detailsUpdated = 0;

        $this->output->progressStart($details->count());

        foreach ($details as $detail) {
            $subtotal = ($detail->price ?? 0) * ($detail->qty ?? 1);

            if ($detail->subtotal !== $subtotal) {
                $detail->update(['subtotal' => $subtotal]);
                $detailsUpdated++;
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->line("  - Updated {$detailsUpdated} detail subtotals.");

        // Recalculate all maintenance totals
        $maintenances = Maintenances::all();
        $maintenancesUpdated = 0;

        $this->output->progressStart($maintenances->count());

        foreach ($maintenances as $maintenance) {
            $total = MaintenancesDetails::where('maintenance_id', $maintenance->id)
                ->sum('subtotal');

            if ($maintenance->total_amount != $total) {
                $maintenance->update(['total_amount' => $total]);
                $maintenancesUpdated++;
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->line("  - Updated {$maintenancesUpdated} maintenance totals.");

        // Show summary
        $this->newLine();
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Details', $details->count()],
                ['Details Updated', $detailsUpdated],
                ['Total Maintenances', $maintenances->count()],
                ['Maintenances Updated', $maintenancesUpdated],
            ]
        );
    }
}
