<?php

namespace Uneca\Chimera\Http\Controllers;

use App\Http\Controllers\Controller;

class HelpController extends Controller
{
    public function __invoke()
    {
        return view('help.index');
    }
}
