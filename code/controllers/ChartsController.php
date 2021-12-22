<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class ChartsController extends Controller
{
    private function paginate(array $list, $route, $pageNumber = 1)
    {
        $list = is_countable($list) ? $list : [];
        $perPage = config('chimera.indicators_per_page', 2);
        $totalCount = count($list);
        $startingPoint = ((int)$pageNumber * $perPage) - $perPage;
        $slice = array_slice($list, $startingPoint, $perPage, true);
        $paginatedList = new LengthAwarePaginator($slice, $totalCount, $perPage, $pageNumber);
        return $paginatedList->withPath($route);
    }

    private function generatePreviewContent(array $list)
    {
        $list = is_countable($list) ? collect($list) : collect([]);
        $perPage = config('chimera.indicators_per_page', 2);
        return $list->map(function($indicator) {
            return $indicator['title'];
        })->values()->chunk($perPage);
    }

    public function multi(Request $request)
    {
        $page = Route::currentRouteName();
        try {
            $this->authorize($page, Auth::user());
        } catch (AuthorizationException $authorizationException) {
            return redirect('faq');
        }

        $indicators = config("chimera.pages.{$page}.indicators");
        if (strtolower($page) === 'home') {
            $view = 'home';
            $preview = null;
            $indicators = collect($indicators)->mapToGroups(function($i, $key) {
                return [$i['connection'] => array_merge(['indicator' => $key], $i)];
            });
        } else {
            $view = 'multi';
            $preview = $this->generatePreviewContent($indicators);
            $indicators = $this->paginate($indicators, $page, $request->get('page', 1));
        }
        return view("charts.$view")->with([
            'page' => $page,
            'indicators' => $indicators,
            'connection' => config("chimera.pages.{$page}.connection"),
            'preview' => $preview
        ]);
    }

    public function single($page, $chart)
    {
        $this->authorize($page, Auth::user());
        try {
            $metadata = config("chimera.pages.$page.indicators")[$chart];
        } catch (Exception $exception) {
            abort(404);
            exit;
        }
        return view('charts.single')->with([
            'page' => $page,
            'chart' => $chart,
            'metadata' => $metadata,
            'connection' => config("chimera.pages.{$page}.connection"),
        ]);
    }
}
