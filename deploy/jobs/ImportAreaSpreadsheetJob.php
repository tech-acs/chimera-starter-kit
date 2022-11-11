<?php

namespace App\Jobs;

use App\Notifications\TaskCompletedNotification;
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

    public function __construct(private string $filePath, private array $areaLevels, private array $columnMapping, private User $user)
    {
    }

    public function handle()
    {
        $insertedCount = 0;
        SimpleExcelReader::create($this->filePath)->getRows()
            ->each(function($row) use (&$insertedCount) {
                $areas = [];
                $path = '';
                foreach ($this->columnMapping as $levelName => $columnMapping) {
                    $code = Str::padLeft($row[$columnMapping['code']], $columnMapping['zeroPadding'], '0');
                    $path = (str($path)->isEmpty() ? $path : str($path)->append('.')) . $code;
                    $areas[] = [
                        'code' => $code,
                        'name' => Str::of($row[$columnMapping['name']])->trim()->lower()->ucfirst(),
                        'level' => array_search($levelName, $this->areaLevels), // Safe?
                        'path' => $path,
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
}
