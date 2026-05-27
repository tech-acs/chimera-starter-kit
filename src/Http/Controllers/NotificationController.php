<?php

namespace Uneca\Chimera\Http\Controllers;

use Illuminate\Routing\Controller;

class NotificationController extends Controller
{
    public function __invoke()
    {
        return view('chimera::notification.index');
    }
}
