<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->from
            ? \DateTime::createFromFormat('d/m/Y', $request->from)?->format('Y-m-d')
            : null;

        $to = $request->to
            ? \DateTime::createFromFormat('d/m/Y', $request->to)?->format('Y-m-d')
            : null;

        $query = ActivityLog::query()
            ->with(['user:id,first_name,last_name'])
            ->orderByDesc('id');

        if ($request->filled('moduleId')) {
            $query->where('log_name', $request->moduleId);
        }

        if ($request->filled('actionId')) {
            $query->where('event', $request->actionId);
        }

        if ($from && $to) {
            $query->whereBetween('created_at', ["{$from} 00:00:00", "{$to} 23:59:59"]);
        } elseif ($from) {
            $query->where('created_at', '>=', "{$from} 00:00:00");
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($w) use ($search) {
                $w->orWhere('log_name', 'like', "%{$search}%")
                    ->orWhere('event', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(properties, '$')) LIKE ?", ["%{$search}%"]);
            });
        }

        $allData = $query->paginate($request->page_length ?? 10);
        $modules = ActivityLog::query()->whereNotNull('log_name')->distinct()->pluck('log_name')->toArray();
        $actions = ActivityLog::query()->whereNotNull('event')->distinct()->pluck('event')->toArray();

        return view('activity_log.index', compact('allData', 'request', 'modules', 'actions'));
    }
}
