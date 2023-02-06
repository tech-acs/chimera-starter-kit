<?php

namespace Uneca\Chimera\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Notification;
use Uneca\Chimera\Models\Area;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\SimpleExcel\SimpleExcelReader;
use Uneca\Chimera\Notifications\TaskFailedNotification;

class ImportAreaSpreadsheetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1200;

    public function __construct(
        private string $filePath,
        private int $start,
        private int $chunkSize,
        private array $areaLevels,
        private array $columnMapping,
        private Authenticatable $user,
        private string $locale
    ) {}

    public function handle()
    {
        DB::transaction(function() {
            $insertedCount = 0;
            SimpleExcelReader::create($this->filePath)
                ->skip($this->start)
                ->take($this->chunkSize + 1)
                ->getRows()
                ->each(function($row) use (&$insertedCount) {
                    $areas = [];
                    $path = '';
                    $timestamp = Carbon::now();
                    foreach ($this->columnMapping as $levelName => $columnMapping) {
                        $name = Str::of($row[$columnMapping['name']])->trim()->lower()->limit(80)->title();
                        $fallbackLocale = config('app.fallback_locale');
                        if ($this->locale !== $fallbackLocale) {
                            $name = json_encode([$fallbackLocale => $name, $this->locale => $name]);
                        } else {
                            $name = json_encode([$fallbackLocale => $name]);
                        }
                        $code = Str::padLeft($row[$columnMapping['code']], $columnMapping['zeroPadding'], '0');
                        $path = (str($path)->isEmpty() ? $path : str($path)->append('.')) . $code;
                        $areas[] = [
                            'name' => $name,
                            'code' => $code,
                            'level' => array_search($levelName, $this->areaLevels), // Is this safe?
                            'path' => $path,
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ];
                    }
                    $insertedCount += Area::insertOrIgnore($areas);
                });

            /*Notification::sendNow($this->user, new TaskCompletedNotification(
                'Task completed',
                "$insertedCount areas have been imported."
            ));*/
        });
    }

    /*public function failed(\Throwable $exception)
    {
        logger('ImportAreaSpreadsheet Job Failed', ['Exception: ' => $exception->getMessage()]);
        Notification::sendNow($this->user, new TaskFailedNotification(
            'Task failed',
            $exception->getMessage()
        ));
    }*/
}
