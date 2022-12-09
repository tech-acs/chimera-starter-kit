<?php

namespace Uneca\Chimera\Http\Controllers;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Models\Indicator;
use Uneca\Chimera\Models\Page;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

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
        return $list->map(function ($indicator) {
            return $indicator->title;
        })->values()->chunk($perPage);
    }

    public function page(Page $page, Request $request)
    {
        $page->load(['indicators' => function ($query) {
            $query->where('published', true);
        }]);
        try {
            $this->authorize($page->permission_name, Auth::user());
        } catch (AuthorizationException $authorizationException) {
            return redirect('faq');
        }
        $indicators = $page->indicators?->filter(function ($indicator) {
            return Gate::allows($indicator->permission_name);
        })->all();
        $preview = $this->generatePreviewContent($indicators);
        $indicators = $this->paginate($indicators, $page->slug, $request->get('page', 1));
        return view('chimera::charts.multi', compact('indicators', 'preview'));
    }

    public function indicator(Indicator $indicator)
    {
        try {
            $this->authorize($indicator->permission_name, Auth::user());
        } catch (AuthorizationException $authorizationException) {
            abort(404);
            //exit;
        }
        return view('chimera::charts.single', compact('indicator'));
    }
}
