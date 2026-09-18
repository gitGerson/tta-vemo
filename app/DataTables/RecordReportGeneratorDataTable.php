<?php

namespace App\DataTables;

/**
 * Report generator table: same columns and rows as the monthly fuel report,
 * fed with records filtered by a flexible period / multi-company selection.
 */
class RecordReportGeneratorDataTable extends RecordMonthlyReportDataTable
{
    public function id(): string
    {
        return 'record-report-generator';
    }
}
