<?php

namespace Uneca\Chimera\Http\Controllers;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Enums\PageableTypes;
use Uneca\Chimera\Models\Page;
use Uneca\Chimera\Models\Report;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Uneca\Chimera\Services\APCA;
use Uneca\Chimera\Services\ColorPalette;
use Uneca\Chimera\Services\DashboardComponentFactory;

class ReportController extends Controller
{
    public function index()
    {
        $currentPalette = ColorPalette::current()->colors;
        $totalColors = count($currentPalette);
        $pages = Page::for(PageableTypes::Reports)
            ->get()
            ->map(function ($page, $index) use ($totalColors, $currentPalette) {
                return [
                    'title' => $page->title,
                    'description' => $page->description,
                    'link' => route('report.page', $page),
                    'slug' => $page->slug,
                    'bg-color' => $currentPalette[$index % $totalColors],
                    'fg-color' => APCA::decideBlackOrWhiteTextColor($currentPalette[$index]),
                ];
            })
            ->all();

        return view('chimera::report.index', compact('pages'));
    }

    public function show(Page $page)
    {
        $records = $page->reports()
            ->orderBy('rank')
            ->paginate(settings('records_per_page'));
        $records->setCollection(
            $records->getCollection()
                ->filter(fn($report) => Gate::allows($report->permission_name))
                ->map(function ($report) {
                    $implementedReport = DashboardComponentFactory::makeReport($report);
                    $path = auth()->user()->areaRestrictions->first()?->path ?? '';
                    $report->fileExists = Storage::disk('reports')->exists($implementedReport->filename($path));
                    $report->data_source_title = $report->getDataSource()->title;
                    return $report;
                })
        );
        return view('chimera::report.show', compact('records'));
    }

    public function download(Report $report)
    {
        try {
            $implementedReport = DashboardComponentFactory::makeReport($report);
            $path = auth()->user()->areaRestrictions->first()?->path ?? '';
            return Storage::disk('reports')
                ->download($implementedReport->filename($path));
        } catch (\Exception $exception) {
            return redirect()->back()
                ->withErrors(new MessageBag(['Unable to download the requested report at this time']));
        }
    }
}
