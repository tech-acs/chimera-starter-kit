<?php

namespace Uneca\Chimera\Http\Controllers;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Models\DataSource;

class HomeController extends Controller
{
    public function __invoke()
    {
        $dataSources = DataSource::active()->showOnHomePage()->orderBy('rank')->get();
        return view('chimera::home', compact('dataSources'));
    }
}
