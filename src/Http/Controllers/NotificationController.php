<?php

namespace Uneca\Chimera\Http\Controllers;

use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function __invoke()
    {
        return view('chimera::notification.index');
    }
}
