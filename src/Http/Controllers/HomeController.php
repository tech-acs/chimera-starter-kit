<?php

namespace Uneca\Chimera\Http\Controllers;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Models\DataSource;
use Uneca\Chimera\Services\PageBuilder;

class HomeController extends Controller
{
    public function __invoke()
    {
        $dataSources = DataSource::active()->showOnHomePage()->orderBy('rank')->get();
        $pages = collect(PageBuilder::pages())
            ->map(function ($page, $route) {
                return [
                    'title' => $page->title,
                    'description' => $page->description,
                    'link' => route('page', $route),
                    'slug' => $page->slug,
                ];
            })
            ->all();
        $graphicalMenu = [
            [
                'title' => 'Reports',
                'description' => 'Download various types of csv reports for further analysis',
                'link' => route('report'),
                'slug' => 'reports',
            ],[
                'title' => 'Maps',
                'description' => 'View key indicators conveniently visualized on a map',
                'link' => route('map'),
                'slug' => 'maps',
            ],
            ...$pages,
            [
                'title' => 'Account Settings',
                'description' => 'Tweak and adjust various aspects of your account',
                'link' => route('profile.show'),
                'slug' => 'profile',
            ]
        ];

        return view('chimera::home', compact('dataSources', 'graphicalMenu'));
    }
}
