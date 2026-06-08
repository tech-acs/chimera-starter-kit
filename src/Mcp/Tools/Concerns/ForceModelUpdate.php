<?php

namespace Uneca\Chimera\Mcp\Tools\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait ForceModelUpdate
{
    private function forceUpdate(Model $model, array $update): void
    {
        if (empty($update)) {
            return;
        }

        $translatable = method_exists($model, 'getTranslatableAttributes')
            ? $model->getTranslatableAttributes()
            : [];

        $dbUpdate = [];
        foreach ($update as $key => $value) {
            if (in_array($key, $translatable)) {
                $encoded = json_encode([app()->getLocale() => $value], JSON_UNESCAPED_UNICODE);
                $dbUpdate[$key] = $encoded;
            } else {
                $dbUpdate[$key] = $value;
            }
        }

        $dbUpdate[$model->getUpdatedAtColumn()] = $model->freshTimestampString();

        DB::table($model->getTable())
            ->where($model->getKeyName(), $model->getKey())
            ->update($dbUpdate);
    }
}
