<?php

namespace App\Jobs;

use App\Notifications\TaskCompletedNotification;
use App\Notifications\TaskFailedNotification;
use App\Services\AreaTree;
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

    public function __construct(private string $filePath, private array $columnMapping, private User $user)
    {
    }

    private function writeHigherLevelValues(string $indicator, ?string $currentLevel, bool $isAdditive)
    {
        while ($nextLevelUp = $this->nextLevelUp($currentLevel)) {
            echo("Now calculating $nextLevelUp level expected values...");

            $propagation = $isAdditive ? 'SUM(reference-values.value) AS value' : 'AVG(reference-values.value) AS value';
            $evsUp = DB::table('reference-values')
                ->join('areas', 'areas.code', '=', 'reference-values.code')
                ->select('areas.parent_code AS code', DB::raw($propagation))
                ->where('reference-values.indicator', 'ILIKE', $indicator)
                ->where('reference-values.area_type', $currentLevel)
                ->groupBy('areas.parent_code')
                ->get()
                ->map(function ($row) use ($nextLevelUp, $indicator){
                    return [
                        'code' => $row->code,
                        'level' => $nextLevelUp,
                        'indicator' => $indicator,
                        'value' => $row->value,
                        'created_at' => Carbon::now(),
                    ];
                });

            $inserted = 0;
            $evsUp->chunk(500)->each(function ($chunk) use (&$inserted) {
                $inserted += DB::table('reference-values')->insertOrIgnore($chunk->all());
            });

            echo("$inserted values written.");

            $currentLevel = $nextLevelUp;
        }
    }

    public function handle()
    {
        $insertedCount = 0;
        SimpleExcelReader::create($this->filePath)->getRows()
            ->each(function($row) use (&$insertedCount) {
                $targets = [];
                //$path = '';
                foreach ($this->columnMapping as $columnMapping) {
                    $code = Str::padLeft($row[$columnMapping['code']], $columnMapping['zeroPadding'] ?? 0, '0');
                    //$path = (str($path)->isEmpty() ? $path : str($path)->append('.')) . $code;
                    $targets[] = [
                        'code' => $code,
                        'level' => array_key_last((new AreaTree())->hierarchies),
                        'indicator' => $columnMapping['name'],
                        'value' => $row[$columnMapping['name']],
                        //'path' => $path,
                        'created_at' => Carbon::now(),
                    ];
                }
                $insertedCount += DB::table('reference_values')->insertOrIgnore($targets);
            });



        Notification::sendNow($this->user, new TaskCompletedNotification(
            'Task completed',
            "$insertedCount target values have been imported across " . count($this->columnMapping) . ' ' . str('indicator')->plural(count($this->columnMapping))
        ));
    }

    public function failed(\Throwable $exception)
    {
        Notification::sendNow($this->user, new TaskFailedNotification(
            'Task failed',
            $exception->getMessage()
        ));
    }
}
