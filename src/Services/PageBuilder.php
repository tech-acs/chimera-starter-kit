<?php

namespace Uneca\Chimera\Services;

use Uneca\Chimera\Models\Page;

class PageBuilder
{
    public static function pages()
    {
        try {
            $pages = Page::select('title', 'slug', 'description')
                ->published()
                ->orderBy('rank')
                ->get()
                ->keyBy('slug')
                ->map(fn($model) => $model)
                ->all();
        } catch (\Exception $exception) {
            $pages = [];
        }
        return $pages;
    }
}
