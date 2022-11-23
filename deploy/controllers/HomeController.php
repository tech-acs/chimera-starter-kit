<?php

namespace App\Http\Controllers;

use App\Models\Questionnaire;
use App\Services\AreaTree;

class HomeController extends Controller
{
    public function __invoke()
    {
        /*$areaTree = new AreaTree(removeLastNLevels: 1);
        $hierarchies = $areaTree->hierarchies;
        $selectionsFromSession = ['region' => '02']; //session()->get('area-filter', []);
        $restrictions = []; //['region' => '02', 'constituency' => '02.0201'];

        $dropdowns = collect($hierarchies)
            ->mapWithKeys(function ($levelName, $level) use ($areaTree, $selectionsFromSession, $hierarchies) {
                $areaList = $level === 0 ?
                    $areaTree->areas()->pluck('name', 'path')->all() :
                    $areaTree->areas($selectionsFromSession[$hierarchies[$level - 1]] ?? 'x')->pluck('name', 'path')->all();
                return [$levelName => [
                    'populate' => $areaList,
                    'select' => $selectionsFromSession[$levelName] ?? null,
                    'restrict' => null,
                ]];
            });

        echo "<pre>";
        print_r($dropdowns);
        echo "</pre>";
        dd();*/

        $questionnaires = Questionnaire::showOnHomePage()->get();
        return view('home', compact('questionnaires'));
    }
}
