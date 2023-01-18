<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Uneca\Chimera\Models\UsageStat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UsageStatsController extends Controller
{
    public function __invoke(Request $request)
    {
        $filter = null;
        if ($request->has('filter')) {
            list($column, $value) = explode(':', $request->get('filter'));
            $filter = $value;
            if ($column === 'email') {
                $records = UsageStat::with('user')->whereHas('user', function (Builder $query) use ($value) {
                    $query->where('email', $value);
                })
                ->orderBy('created_at', 'DESC')
                ->paginate(env('PAGE_SIZE', 20));
            } else {
                $records = UsageStat::with('user')
                    ->where($column, $value)
                    ->orderBy('created_at', 'DESC')
                    ->paginate(env('PAGE_SIZE', 20));
            }
        } else {
            $records = UsageStat::with('user')->orderBy('created_at', 'DESC')->paginate(env('PAGE_SIZE', 20));
        }
        return view('chimera::usage_stats.index', compact('records', 'filter'));
    }
}
