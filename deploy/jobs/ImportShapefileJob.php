<?php

namespace App\Jobs;

use App\Models\Area;
use App\Notifications\TaskCompletedNotification;
use App\Services\Traits\Geospatial;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class ImportShapefileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use Geospatial;

    public function __construct(private array $features, private int $level, private User $user)
    {
    }

    private function augumentData(array $features, int $level)
    {
        return array_map(function ($feature) use ($level) {
            if ($level > 0) {
                $ancestor = self::findContainingGeometry($level - 1, $feature['geom']);
                $feature['path'] = empty($ancestor) ? null : $this->makePath($ancestor, $feature['attribs']['code']);
            } else {
                $feature['path'] = $this->makePath(null, $feature['attribs']['code']);
            }
            return $feature;
        }, $features);
    }

    private function makePath($ancestor, $code)
    {
        return is_null($ancestor) ? $code : $ancestor->path . '.' . $code;
    }

    public function handle()
    {
        // Find and add parent info for all areas (unless root level)
        $augmentedFeatures = $this->augumentData($this->features, $this->level);

        // Check that there are no "orphan" areas
        $orphanFeatures = array_filter($augmentedFeatures, fn ($feature) => empty($feature['path']));
        if (! empty($orphanFeatures)) {
            $orphans = collect($orphanFeatures)->pluck('attribs.code')->join(', ', ' and ');
            throw ValidationException::withMessages([
                'shapefile' => [count($orphanFeatures) . " orphan area(s) found [code: $orphans]. All areas require a containing parent area."],
            ]);
        }

        $results = [];
        foreach ($augmentedFeatures as $feature) {
            $results[] = Area::create([
                'name' => $feature['attribs']['name'],
                'code' => $feature['attribs']['code'],
                'level' => $this->level,
                'geom' => $feature['geom'],
                'path' => $feature['path'],
            ]);
        }
        $insertedCount = collect($results)->filter()->count();

        Notification::sendNow($this->user, new TaskCompletedNotification(
            'Task completed',
            "$insertedCount areas have been imported."
        ));
    }
}
