<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnnouncementRequest;
use App\Models\Announcement;
use App\Models\User;
use App\Notifications\BroadcastMessageNotification;
use Illuminate\Support\Facades\Notification;

class AnnouncementController extends Controller
{
    public function index()
    {
        $records = Announcement::paginate(config('chimera.records_per_page'));
        return view('announcement.index', compact('records'));
    }

    public function create()
    {
        return view('announcement.create');
    }

    public function store(AnnouncementRequest $request)
    {
        $announcement = auth()->user()->announcements()->create($request->safe()->all());
        $sender = auth()->user();
        Notification::sendNow(User::whereKeyNot($sender->id)->get(), new BroadcastMessageNotification($announcement));
        return redirect()->route('announcement.index')->withMessage('The announcement has been broadcast to all users.');
    }
}
