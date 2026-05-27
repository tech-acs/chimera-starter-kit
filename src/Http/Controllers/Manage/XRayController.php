<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Process;

class XRayController extends Controller
{
    public function __invoke()
    {
        Process::run("echo -n '' > ".config('chimera.xray_file'));

        return view('chimera::developer.x-ray.index');
    }
}
