<?php

namespace Uneca\Chimera\Http\Controllers;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Models\Report;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Uneca\Chimera\Services\DashboardComponentFactory;

class ReportController extends Controller
{
    public function index()
    {
        $records = Report::published()
            ->orderBy('rank')
            ->paginate(config('chimera.records_per_page'));
        $records->setCollection(
            $records->getCollection()->filter(function ($report) {
                return Gate::allows($report->permission_name);
            })
            ->map(function ($report) {
                $implementedReport = DashboardComponentFactory::makeReport($report);
                $path = auth()->user()->areaRestrictions->first()?->path ?? '';
                $report->fileExists = Storage::disk('reports')->exists($implementedReport->filename($path));
                return $report;
            })
        );
        return view('chimera::report.index', compact('records'));
    }

    public function download(Report $report)
    {
        try {
            $implementedReport = DashboardComponentFactory::makeReport($report);
            $path = auth()->user()->areaRestrictions->first()?->path ?? '';
            return Storage::disk('reports')->download($implementedReport->filename($path));
        } catch (\Exception $exception) {
            return redirect()->back()->withErrors(new MessageBag(['Unable to download the requested report at this time']));
        }
    }

    /*public function generate(Report $report)
    {
        try {
            $report->blueprintInstance->generate();
            return redirect()->back()->withMessage('The report is now being generated');
        } catch (\Exception $exception) {
            return redirect()->back()->withErrors(new MessageBag(['Unable to generate the requested report at this time. Make sure the getCollection method returns data.']));
        }
    }*/
}
