<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Indicator;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class MapPageController extends Controller
{
    public function __invoke(Request $request)
    {
        //$indicator = Indicator::first();
        return view('map.index');
    }
}
