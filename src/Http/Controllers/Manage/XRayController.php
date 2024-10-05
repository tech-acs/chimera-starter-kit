<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Process;

class XRayController extends Controller
{
    public function __invoke()
    {
        //Process::run('truncate ' . config('chimera.xray_file') . ' -s0');
        return view('chimera::developer.x-ray.index');
    }
}
