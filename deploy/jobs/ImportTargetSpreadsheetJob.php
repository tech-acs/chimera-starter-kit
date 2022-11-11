<?php

namespace App\Jobs;

use App\Notifications\TaskCompletedNotification;
use App\Notifications\TaskFailedNotification;
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

class ImportTargetSpreadsheetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private string $filePath, private array $columnMapping, private User $user)
    {
        //
    }

    public function handle()
    {
        $insertedCount = 0;
        SimpleExcelReader::create($this->filePath)->getRows()
            ->each(function($row) use (&$insertedCount) {
                $values = [];
                //$path = '';
                foreach ($this->columnMapping as $columnMapping) {
                    $code = Str::padLeft($row[$columnMapping['code']], $columnMapping['zeroPadding'] ?? 0, '0');
                    //$path = (str($path)->isEmpty() ? $path : str($path)->append('.')) . $code;
                    $areas[] = [
                        'code' => $code,
                        'level' => 0, //array_search($levelName, $this->areaLevels), // Safe?
                        'indicator' => $columnMapping['name'],
                        'value' => $row[$columnMapping['name']],
                        //'path' => $path,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
                $insertedCount += DB::table('areas')->insertOrIgnore($areas);
            });

        Notification::sendNow($this->user, new TaskCompletedNotification(
            'Task completed',
            "$insertedCount areas have been imported."
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
