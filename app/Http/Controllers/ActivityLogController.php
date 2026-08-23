<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActivityLogController extends Controller
{
    /**
     * Menampilkan daftar activity log.
     */
    public function index(Request $request)
    {
        $query = ActivityLog::query();

        if ($request->filled('user_id')) {
            $query->where(
                'user_id',
                $request->user_id
            );
        }

        if ($request->filled('action')) {
            $query->where(
                'action',
                $request->action
            );
        }

        if ($request->filled('module')) {
            $query->where(
                'module',
                $request->module
            );
        }

        if ($request->filled('date_from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->date_from
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->date_to
            );
        }

        $logs = $query
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return view(
            'activity_logs.index',
            compact('logs')
        );
    }

    /**
     * Form manual untuk membuat activity log.
     *
     * Biasanya log dibuat otomatis oleh sistem,
     * bukan melalui form user.
     */
    public function create()
    {
        return view(
            'activity_logs.create'
        );
    }

    /**
     * Membuat activity log.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'action' => [
                'required',
                'string',
                'max:100',
            ],

            'module' => [
                'required',
                'string',
                'max:100',
            ],

            'description' => [
                'required',
                'string',
            ],

            'user_id' => [
                'nullable',
                'integer',
                Rule::exists(
                    'users',
                    'id'
                ),
            ],

            'reference_type' =>
                'nullable|string|max:100',

            'reference_id' =>
                'nullable|integer',

            'old_values' =>
                'nullable',

            'new_values' =>
                'nullable',

            'ip_address' =>
                'nullable|ip',

            'user_agent' =>
                'nullable|string|max:1000',
        ]);

        $log = ActivityLog::create([
            'user_id' =>
                $validated['user_id']
                ?? auth()->id(),

            'action' =>
                $validated['action'],

            'module' =>
                $validated['module'],

            'description' =>
                $validated['description'],

            'reference_type' =>
                $validated[
                    'reference_type'
                ] ?? null,

            'reference_id' =>
                $validated[
                    'reference_id'
                ] ?? null,

            'old_values' =>
                $validated[
                    'old_values'
                ] ?? null,

            'new_values' =>
                $validated[
                    'new_values'
                ] ?? null,

            'ip_address' =>
                $validated[
                    'ip_address'
                ]
                ?? $request->ip(),

            'user_agent' =>
                $validated[
                    'user_agent'
                ]
                ?? $request->userAgent(),
        ]);

        return redirect()
            ->route(
                'activity-logs.show',
                $log
            )
            ->with(
                'success',
                'Activity log berhasil dicatat.'
            );
    }

    /**
     * Detail activity log.
     */
    public function show(
        ActivityLog $activityLog
    ) {
        $activityLog->load(
            'user'
        );

        return view(
            'activity_logs.show',
            compact('activityLog')
        );
    }

    /**
     * Activity log tidak dapat diedit.
     */
    public function edit(
        ActivityLog $activityLog
    ) {
        return redirect()
            ->route(
                'activity-logs.show',
                $activityLog
            )
            ->with(
                'error',
                'Activity log tidak dapat diedit.'
            );
    }

    /**
     * Update tidak diperbolehkan.
     */
    public function update(
        Request $request,
        ActivityLog $activityLog
    ) {
        return redirect()
            ->route(
                'activity-logs.show',
                $activityLog
            )
            ->with(
                'error',
                'Activity log tidak dapat diubah.'
            );
    }

    /**
     * Activity log tidak boleh dihapus.
     */
    public function destroy(
        ActivityLog $activityLog
    ) {
        return redirect()
            ->route(
                'activity-logs.show',
                $activityLog
            )
            ->with(
                'error',
                'Activity log tidak dapat dihapus.'
            );
    }
}