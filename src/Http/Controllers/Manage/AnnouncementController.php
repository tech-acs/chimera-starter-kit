<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Http\Requests\AnnouncementRequest;
use Uneca\Chimera\Models\Announcement;
use Uneca\Chimera\Models\User;
use Uneca\Chimera\Notifications\BroadcastMessageNotification;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

class AnnouncementController extends Controller
{
    public function index()
    {
        $records = Announcement::paginate(config('chimera.records_per_page'));
        return view('chimera::announcement.index', compact('records'));
    }

    private function recipientsList()
    {
        return Role::whereNotIn('name', ['Super Admin'])
            ->pluck('name', 'id')
            ->map(fn ($role) => "Users having $role role")
            ->prepend('Everyone', 0)
            ->all();
    }

    public function create()
    {
        $recipients = $this->recipientsList();
        return view('chimera::announcement.create', compact('recipients'));
    }

    public function store(AnnouncementRequest $request)
    {
        $sender = auth()->user();
        $recipients = $request->integer('recipients');
        $recipientUsers = match ($recipients) {
            0 => User::whereKeyNot($sender->id)->get(),
            default => Role::find($recipients)->users,
        };
        if ($recipientUsers->count() > 0) {
            $recipientsList = $this->recipientsList();
            $announcement = auth()->user()
                ->announcements()
                ->create(array_merge($request->safe()->all(), ['recipients' => $recipientsList[$recipients]]));
            try {
                Notification::sendNow($recipientUsers, new BroadcastMessageNotification($announcement));
            } catch (\Exception $exception) {
                //
            }
            return redirect()->route('announcement.index')->withMessage('The announcement has been sent to the specified recipients group.');
        }
        return redirect()->route('announcement.index')->withMessage('No users found for specified recipients group.');
    }
}
