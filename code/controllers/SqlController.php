<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SqlController extends Controller
{
    public function create()
    {

    }

    public function store()
    {
        DB::connection('listing')->beginTransaction();
        DB::select();
        DB::connection('listing')->rollBack();
    }
}
