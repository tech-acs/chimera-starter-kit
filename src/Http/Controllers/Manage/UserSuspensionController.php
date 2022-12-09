<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Models\User;

class UserSuspensionController extends Controller
{
    public function __invoke(User $user)
    {
        if ($user->is_suspended) {
            $user->update(['is_suspended' => false]);
            return redirect()->route('user.index')
                ->withMessage('The account has been restored. The user will now be able to login and use the dashboard.');
        } else {
            $user->update(['is_suspended' => true]);
            return redirect()->route('user.index')
                ->withMessage('The account has been suspended. The user will not be able to login.');
        }
    }
}
