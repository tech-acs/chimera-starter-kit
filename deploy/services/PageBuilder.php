<?php

namespace App\Services;

use App\Models\Page;

class PageBuilder
{
    public static function pages()
    {
        try {
            $pages = Page::select('title', 'slug', 'description')
                ->published()
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
