<?php

namespace Uneca\Chimera\Jobs;

use Uneca\Chimera\Models\ReferenceValue;
use Uneca\Chimera\Notifications\TaskCompletedNotification;
use Uneca\Chimera\Notifications\TaskFailedNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Spatie\SimpleExcel\SimpleExcelReader;

class ImportReferenceValueSpreadsheetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1200;

    public function __construct(private string $filePath, private array $columnMapping, private User $user)
    {
    }

    private function writeHigherLevelValues(array $indicatorMapping, int $level)
    {
        $aggMethod = $indicatorMapping['isAdditive'] ? 'SUM(reference_values.value) AS value' : 'AVG(reference_values.value) AS value';
        DB::insert("
            INSERT INTO reference_values(path, level, indicator, value)
            SELECT areas.path, nlevel(agg.path) - 1 AS level, agg.indicator, agg.value
            FROM (
                SELECT $aggMethod, subpath(areas.path, 0, $level) AS path, reference_values.indicator
                FROM reference_values INNER JOIN areas ON reference_values.path = areas.path
                WHERE reference_values.indicator = '{$indicatorMapping['name']}' AND reference_values.level = $level
                GROUP BY indicator, subpath(areas.path, 0, $level)
            ) AS agg INNER JOIN areas ON agg.path = areas.path
        ");
    }

    private function insertInitialValues($indicatorMapping)
    {
        SimpleExcelReader::create($this->filePath)->getRows()
            ->map(function($row) use ($indicatorMapping) {
                return [
                    'path' => $row[$indicatorMapping['path']],
                    'level' => $indicatorMapping['level'],
                    'indicator' => $indicatorMapping['name'],
                    'value' => $row[$indicatorMapping['name']],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            })
            ->chunk(500)
            ->each(function ($chunk) {
                DB::table('reference_values')->insertOrIgnore($chunk->all());
            });
    }

    public function handle()
    {
        $initialCount = ReferenceValue::count();
        foreach ($this->columnMapping as $mapping) {
            $this->insertInitialValues($mapping);
            for ($level = $mapping['level']; $level > 0; $level--){
                $this->writeHigherLevelValues($mapping, $level);
            }
        }
        $insertedCount = ReferenceValue::count() - $initialCount;

        Notification::sendNow($this->user, new TaskCompletedNotification(
            'Task completed',
            "$insertedCount reference values have been imported across " . count($this->columnMapping) . ' ' .
                str('indicator')->plural(count($this->columnMapping))
        ));
    }

    public function failed(\Throwable $exception)
    {
        logger('ImportReferenceValueSpreadsheet Job Failed', ['Exception: ' => $exception->getMessage()]);
        Notification::sendNow($this->user, new TaskFailedNotification(
            'Task failed',
            $exception->getMessage()
        ));
    }
}
