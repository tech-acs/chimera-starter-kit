<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __invoke()
    {
        $records = auth()->user()->notifications;
        return view('notification.index', compact('records'));
    }
}
