<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Questionnaire;
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
        $perPage = config('chimera.indicators_per_page');
        $totalCount = count($list);
        $startingPoint = ((int)$pageNumber * $perPage) - $perPage;
        $slice = array_slice($list, $startingPoint, $perPage, true);
        $paginatedList = new LengthAwarePaginator($slice, $totalCount, $perPage, $pageNumber);
        return $paginatedList->withPath($route);
    }

    private function generatePreviewContent(array $list)
    {
        $list = is_countable($list) ? collect($list) : collect([]);
        $perPage = config('chimera.indicators_per_page');
        return $list->map(function($indicator) {
            return $indicator['title'];
        })->values()->chunk($perPage);
    }

    public function multi(Request $request)
    {
        $pageSlug = Route::currentRouteName();
        try {
            $this->authorize($pageSlug, Auth::user());
        } catch (AuthorizationException $authorizationException) {
            return redirect('faq');
        }

        $page = Page::with('indicators')->where('slug', $pageSlug)->first();
        $indicators = $page?->indicators?->all();
        $preview = $this->generatePreviewContent($indicators);
        $indicators = $this->paginate($indicators, $pageSlug, $request->get('page', 1));

        return view("charts.multi")->with([
            'page' => $pageSlug,
            'indicators' => $indicators,
            //'questionnaire' => $page?->questionnaire, //config("chimera.pages.{$page}.connection"),
            'preview' => $preview
        ]);
    }

    public function single($pageSlug, $chart)
    {
        $page = Page::where('slug', $pageSlug)->first();
        $this->authorize($pageSlug, Auth::user());
        try {
            //$metadata = config("chimera.pages.$page.indicators")[$chart];
            $indicator = null;
        } catch (Exception $exception) {
            abort(404);
            exit;
        }
        return view('charts.single')->with([
            'page' => $pageSlug,
            'indicator' => $indicator,
            //'connection' => $page->connection, //config("chimera.pages.{$page}.connection"),
        ]);
    }
}
