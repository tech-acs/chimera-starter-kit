<?php

namespace Uneca\Chimera\Jobs;

use Uneca\Chimera\Models\Area;
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

class ImportAreaSpreadsheetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;

    public function __construct(private string $filePath, private array $areaLevels, private array $columnMapping, private User $user)
    {
    }

    public function handle()
    {
        DB::transaction(function() {
            $insertedCount = 0;
            SimpleExcelReader::create($this->filePath)->getRows()
                ->each(function($row) use (&$insertedCount) {
                    $areas = [];
                    $path = '';
                    $timestamp = Carbon::now();
                    foreach ($this->columnMapping as $levelName => $columnMapping) {
                        $name = Str::of($row[$columnMapping['name']])->trim()->lower()->limit(80)->title();
                        $code = Str::padLeft($row[$columnMapping['code']], $columnMapping['zeroPadding'], '0');
                        $path = (str($path)->isEmpty() ? $path : str($path)->append('.')) . $code;
                        $areas[] = [
                            'name' => json_encode(['en' => $name]),
                            'code' => $code,
                            'level' => array_search($levelName, $this->areaLevels), // Safe?
                            'path' => $path,
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ];
                    }
                    $insertedCount += Area::insertOrIgnore($areas);
                });

            Notification::sendNow($this->user, new TaskCompletedNotification(
                'Task completed',
                "$insertedCount areas have been imported."
            ));
        });
    }

    public function failed(\Throwable $exception)
    {
        logger('ImportAreaSpreadsheet Job Failed', ['Exception: ' => $exception->getMessage()]);
        Notification::sendNow($this->user, new TaskFailedNotification(
            'Task failed',
            $exception->getMessage()
        ));
    }
}
