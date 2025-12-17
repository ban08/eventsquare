<?php

namespace App\Http\Controllers;

use App\Models\EventReport;
use App\Models\Admin;
use App\Models\AdminAction;
use App\Models\AdminReportAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'open');

        $reports = EventReport::with(['event', 'user'])
            ->when($status, function ($query, $status) {
                if ($status === 'closed') {
                    return $query->whereIn('status', ['dismissed', 'resolved']);
                }
                if ($status !== 'all') {
                    return $query->where('status', $status);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.reports.index', compact('reports', 'status'));
    }

    public function update(Request $request, EventReport $report)
    {
        $validated = $request->validate([
            'action' => 'required|in:resolve,dismiss',
        ]);

        $currentUser = Auth::user();
        $admin = Admin::where('email', $currentUser->email)->first();

        if (!$admin) {
            return back()->withErrors(['error' => 'Admin account not found.']);
        }

        DB::transaction(function () use ($report, $validated, $admin) {
            // Update report status
            $report->status = $validated['action'] === 'resolve' ? 'resolved' : 'dismissed';
            $report->resolved_at = now();
            $report->save();

            $actionType = $validated['action'] === 'resolve' ? 'resolve report' : 'dismiss report';

            $adminAction = AdminAction::create([
                'id_admin' => $admin->id_admin,
                'details' => ucfirst($validated['action']) . ' report #' . $report->id_report,
                'created_at' => now(),
            ]);

            AdminReportAction::create([
                'id_action' => $adminAction->id_action,
                'action' => $actionType,
                'id_report' => $report->id_report,
            ]);
        });

        return back()->with('success', 'Report updated successfully.');
    }
}
