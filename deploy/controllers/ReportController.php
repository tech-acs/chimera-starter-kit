<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Support\MessageBag;

class ReportController extends Controller
{
    public function index()
    {
        $records = Report::enabled()
            ->orderBy('title')
            ->paginate(config('chimera.records_per_page'));
        return view('report.index', compact('records'));
    }

    public function download(Report $report)
    {
        try {
            return $report->blueprintInstance->download();
        } catch (\Exception $exception) {
            return redirect()->back()->withErrors(new MessageBag(['Unable to download the requested report at this time']));
        }
    }

    public function generate(Report $report)
    {
        try {
            $report->blueprintInstance->generate();
            return redirect()->back()->withMessage('The report is now being generated');
        } catch (\Exception $exception) {
            return redirect()->back()->withErrors(new MessageBag(['Unable to generate the requested report at this time']));
        }
    }
}
