<?php

namespace Uneca\Chimera\Http\Controllers;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Models\DataSource;
use Uneca\Chimera\Services\APCA;
use Uneca\Chimera\Services\ColorPalette;
use Uneca\Chimera\Services\PageBuilder;

class HomeController extends Controller
{
    public function __invoke()
    {
        $dataSources = DataSource::active()->showOnHomePage()->orderBy('rank')->get();
        /*$pages = collect(PageBuilder::pages())
            ->map(function ($page, $route) {
                return [
                    'title' => $page->title,
                    'description' => $page->description,
                    'link' => route('page', $route),
                    'slug' => $page->slug,
                ];
            })
            ->values();
        $currentPalette = ColorPalette::current()->colors;
        $totalColors = count($currentPalette);
        $graphicalMenu = collect([
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
        ])->map(function ($page, $index) use ($totalColors, $currentPalette) {
            $page['bg-color'] = $currentPalette[$index % $totalColors];
            $page['fg-color'] = APCA::decideBlackOrWhiteTextColor($currentPalette[$index]);
            return $page;
        })->all();*/

        return view('chimera::home', compact('dataSources'));
    }
}
