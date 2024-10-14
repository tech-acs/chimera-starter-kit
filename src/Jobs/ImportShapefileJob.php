<?php

namespace Uneca\Chimera\Jobs;

use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;
use Uneca\Chimera\Models\AreaHierarchy;
use Uneca\Chimera\Notifications\TaskCompletedNotification;
use Uneca\Chimera\Notifications\TaskFailedNotification;
use Uneca\Chimera\Notifications\TaskProgressNotification;
use Uneca\Chimera\Services\AreaTree;
use Uneca\Chimera\Services\ShapefileImporter;
use Uneca\Chimera\Traits\Geospatial;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class ImportShapefileJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use Geospatial;

    public $timeout = 1800; // 30 minutes

    public function __construct(
        private string $filePath,
        private int $level,
        private User $user,
        private string $locale
    ) {}

    private function areNamesAndCodesValid(array $features): bool
    {
        // Check that all areas have valid values for 'code' and 'name'
        $featuresWithInvalidCode = array_filter($features, function ($feature) {
            $codeValidator = Validator::make(
                $feature['attribs'],
                ['code' => ['required', 'max:255', 'regex:/^[A-Za-z0-9_]+$/i'], 'name' => 'required|min:1']
            );
            /*if ($codeValidator->fails()) {
                logger('Shapefile validation error', ['Error' => $codeValidator->errors()->all()]);
            }*/
            return $codeValidator->fails();
        });
        if (! empty($featuresWithInvalidCode)) {
            logger('Here are the ' . count($featuresWithInvalidCode) . ' problematic features: ', ['Problematic features' => $featuresWithInvalidCode]);
            return false;
        }
        return true;
    }

    private function addParentInfo(array $features, int $level): array
    {
        $hierarchies = (new AreaTree())->hierarchies; $locale = app()->getLocale();
        $thisAreaHierarchy = AreaHierarchy::whereRaw("name->>'{$locale}' = '{$hierarchies[$level]}'")->first();
        return array_map(function ($feature) use ($level, $thisAreaHierarchy) {
            $slimFeature = [
                'attribs' => [
                    'code' => $feature['attribs']['code'],
                    'name' => $feature['attribs']['name'],
                ],
                'geom' => $feature['geom']
            ];
            if ($level > 0) {
                $ancestor = self::findContainingGeometry($level - 1, $feature['geom']);
                $slimFeature['path'] = empty($ancestor) ? null : $this->makePath($ancestor, $feature['attribs']['code']);
            } else {
                $slimFeature['path'] = $this->makePath(null, $feature['attribs']['code']);
            }
            $slimFeature['zero_padded_code'] = Str::padLeft($feature['attribs']['code'], $thisAreaHierarchy->zero_pad_length, '0');
            return $slimFeature;
        }, $features);
    }

    private function makePath($ancestor, $code): string
    {
        return is_null($ancestor) ? $code : $ancestor->path . '.' . $code;
    }

    public function handle()
    {
        $importer = new ShapefileImporter();
        $features = $importer->import($this->filePath); // Returns LazyCollection
        $user = $this->user;

        $batch = Bus::batch([])->before(function (Batch $batch) use ($user) { // The batch has been created but no jobs have been added...
            Cache::put("batch_{$batch->id}", 0);
            Cache::put("batch_progress", time());
            Notification::sendNow($user, new TaskProgressNotification(
                'Task initiated',
                'The shapefile has been upload and is now being processed.'
            ));

        })->progress(function (Batch $batch) use ($user) { // A single job has completed successfully...
            $lastTimestamp = Cache::get("batch_progress");
            if (time() - $lastTimestamp > config('chimera.progress_update_interval_seconds')) {
                Notification::sendNow($user, new TaskProgressNotification(
                    'Task ongoing',
                    "The shapefile is being processed. The work is {$batch->progress()}% complete."
                ));
                Cache::put("batch_progress", time());
            }

        })->then(function (Batch $batch) { // All jobs completed successfully...

        })->catch(function (Batch $batch, Throwable $e) { // First batch job failure detected...

        })->finally(function (Batch $batch) use ($user) { // The batch has finished executing...
            $count = Cache::get("batch_{$batch->id}");
            Notification::sendNow($user, new TaskCompletedNotification(
                'Task completed',
                "The shapefile has been completely processed. $count areas in total were imported."
            ));
            Cache::forget("batch_{$batch->id}");
        }); //->allowFailures()

        $processBatch = true;
        foreach ($features->chunk(config('chimera.shapefile.import_chunk_size')) as $index => $featuresChunk) {
            $features = $featuresChunk->values()->toArray();

            if ($this->areNamesAndCodesValid($features)) {
                // Find and add parent info for all areas (unless root level)
                $augmentedFeaturesChunk = $this->addParentInfo($features, $this->level);

                $orphanFeatures = array_filter($augmentedFeaturesChunk, fn ($feature) => empty($feature['path']));
                if (! empty($orphanFeatures)) {
                    $orphans = collect($orphanFeatures)->pluck('attribs.name')->join(', ', ' and ');
                    logger(count($orphanFeatures) . " orphan area(s) found in chunk " . ($index + 1), ['Names' => $orphans]);
                }

                if (empty($orphanFeatures) || ! config('chimera.shapefile.stop_import_if_orphans_found')) {
                    if (! empty($orphanFeatures)) {
                        $augmentedFeaturesChunk = array_filter($augmentedFeaturesChunk, fn ($feature) => ! empty($feature['path']));
                    }
                    $batch->add(new ImportShapefileChunkJob($augmentedFeaturesChunk, $this->level, $this->user, $this->locale));
                } else {
                    $processBatch = false;
                    break;
                }
            } else {
                $processBatch = false;
                break;
            }
        }

        if ($processBatch) {
            $batch->dispatch();
        }else {
            Notification::sendNow($user, new TaskFailedNotification(
                'Task failed',
                "The shapefile could not be imported because there were some errors found in it. Please check the logs for details."
            ));
        }
    }

    public function failed(\Throwable $exception)
    {
        logger('ImportShapefile Job Failed', ['Exception: ' => $exception->getMessage()]);
        Notification::sendNow($this->user, new TaskFailedNotification(
            'Task failed',
            $exception->getMessage()
        ));
    }
}
