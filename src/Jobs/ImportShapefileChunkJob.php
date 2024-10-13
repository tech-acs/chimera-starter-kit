<?php

namespace Uneca\Chimera\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Support\Str;
use Uneca\Chimera\Models\Area;
use Uneca\Chimera\Traits\Geospatial;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ImportShapefileChunkJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use Geospatial;

    public $tries = 1;
    public $timeout = 300; // 5 minutes

    public function __construct(
        private array $features,
        private int $level,
        private User $user,
        private string $locale
    ) {}

    public function handle()
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        DB::transaction(function() {
            $results = [];
            foreach ($this->features as $feature) {
                $name = Str::of($feature['attribs']['name'])->trim()->lower()->limit(80)->title()->toString();
                $results[] = Area::updateOrCreate(
                    [
                        'code' => $feature['zero_padded_code'],
                        'level' => $this->level,
                        'path' => $feature['path'],
                    ],
                    [
                        'name' => $name,
                        'geom' => $feature['geom'],
                    ]);
            }
            $insertedCount = collect($results)->filter()->count();
            Cache::increment("batch_{$this->batch()->id}", $insertedCount);
        });
    }

    /*public function failed(\Throwable $exception)
    {
        logger('ImportShapefile Job Failed', ['Exception: ' => $exception->getMessage()]);
        Notification::sendNow($this->user, new TaskFailedNotification(
            'Task failed',
            $exception->getMessage()
        ));
    }*/
}
