<?php

namespace Uneca\Chimera\Http\Controllers;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Models\Questionnaire;
use Uneca\Chimera\Services\PageBuilder;

class HomeController extends Controller
{
    private function pickImage(string $group)
    {
        $groupMembers = glob(public_path("images/graphical-menu/$group?.jpg"));
        if (empty($groupMembers)) {
            return 'https://via.placeholder.com/640x427.jpg?text=Image+missing.+Please+fix!';
        }
        return str($groupMembers[array_rand($groupMembers)])->after('public'.DIRECTORY_SEPARATOR);
    }

    public function __invoke()
    {
        $questionnaires = Questionnaire::active()->showOnHomePage()->orderBy('rank')->get();
        $pages = collect(PageBuilder::pages())
            ->map(function ($page, $route) {
                return [
                    'title' => $page->title,
                    'description' => $page->description,
                    'link' => route('page', $route),
                    'image' => asset($this->pickImage('page')),
                ];
            })
            ->all();
        $graphicalMenu = [
            [
                'title' => 'Reports',
                'description' => 'Download various types of csv reports for further analysis',
                'link' => route('report'),
                'image' => asset($this->pickImage('report')),
            ],[
                'title' => 'Maps',
                'description' => 'View key indicators conveniently visualized on a map',
                'link' => route('map'),
                'image' => asset($this->pickImage('map')),
            ],
            ...$pages,
            [
                'title' => 'Account Settings',
                'description' => 'Tweak and adjust various aspects of your account',
                'link' => route('profile.show'),
                'image' => asset($this->pickImage('profile')),
            ]
        ];

        return view('chimera::home', compact('questionnaires', 'graphicalMenu'));
    }
}
