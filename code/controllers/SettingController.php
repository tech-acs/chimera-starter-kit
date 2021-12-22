<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConnectionRequest;
use App\Models\DatabaseConnection;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __invoke()
    {
        return view('setting.index');
    }
}
