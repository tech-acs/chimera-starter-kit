<?php

namespace App\Http\Controllers;

use App\Models\Indicator;
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

    public function page($slug, Request $request)
    {
        $page = Page::with('indicators')->where('slug', $slug)->first();
        try {
            $this->authorize($slug, Auth::user());
        } catch (AuthorizationException $authorizationException) {
            return redirect('faq');
        }
        $indicators = $page?->indicators?->all();
        $preview = $this->generatePreviewContent($indicators);
        $indicators = $this->paginate($indicators, $page->slug, $request->get('page', 1));

        return view("charts.multi")->with([
            'page' => $page->slug,
            'indicators' => $indicators,
            //'questionnaire' => $page?->questionnaire, //config("chimera.pages.{$page}.connection"),
            'preview' => $preview
        ]);
    }

    public function indicator($slug)
    {
        $indicator = Indicator::where('slug', $slug)->first();
        //$page = Page::where('slug', $slug)->first();
        try {
            $this->authorize($slug, Auth::user());
        } catch (AuthorizationException $authorizationException) {
            abort(404);
            exit;
        }
        return view('charts.single')->with([
            //'page' => $slug,
            'indicator' => $indicator,
            //'connection' => $page->connection, //config("chimera.pages.{$page}.connection"),
        ]);
    }
}
