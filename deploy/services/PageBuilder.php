<?php

namespace App\Services;

use App\Models\Page;

class PageBuilder
{
    public static function pages()
    {
        try {
            $pages = Page::select('title', 'slug', 'description', 'connection')
                ->get()
                ->keyBy('slug')
                ->map(fn($model) => $model->toArray())
                ->all();
        } catch (\Exception $exception) {
            $pages = [];
        }
        return $pages;
    }
}
