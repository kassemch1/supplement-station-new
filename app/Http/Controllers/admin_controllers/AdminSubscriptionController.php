<?php

namespace App\Http\Controllers\admin_controllers;

use App\Http\Controllers\admin_controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminSubscriptionController extends Controller
{
    /**
     * Display a listing of subscribers
     */
    public function index(Request $request)
    {
        $query = Subscription::query();

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $query->where('email', 'like', '%' . $request->search . '%');
        }

        // Filter by status - only apply if status is explicitly set to 'active' or 'inactive'
        if ($request->has('status') && in_array($request->status, ['active', 'inactive'])) {
            $query->where('is_active', $request->status == 'active' ? 1 : 0);
        }

        // Sort by latest first
        $subscribers = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin_views.subscribers.index', compact('subscribers'));
    }

    /**
     * Show the form for creating a new subscriber
     */
    public function create()
    {
        return view('admin_views.subscribers.create');
    }

    /**
     * Store a newly created subscriber
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:subscriptions,email|max:255'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Subscription::create([
            'email' => $request->email,
            'is_active' => $request->is_active == '1',
            'subscribed_at' => now()
        ]);

        return redirect()->route('admin.subscribers.index')
            ->with('success', 'Subscriber added successfully!');
    }

    /**
     * Show the form for editing the specified subscriber
     */
    public function edit(Subscription $subscriber)
    {
        return view('admin_views.subscribers.edit', compact('subscriber'));
    }

    /**
     * Update the specified subscriber
     */
    public function update(Request $request, Subscription $subscriber)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:subscriptions,email,' . $subscriber->id . '|max:255'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $subscriber->update([
            'email' => $request->email,
            'is_active' => $request->is_active == '1'
        ]);

        return redirect()->route('admin.subscribers.index')
            ->with('success', 'Subscriber updated successfully!');
    }

    /**
     * Remove the specified subscriber
     */
    public function destroy(Subscription $subscriber)
    {
        $subscriber->delete();

        return redirect()->route('admin.subscribers.index')
            ->with('success', 'Subscriber deleted successfully!');
    }

    /**
     * Toggle subscriber status (active/inactive)
     */
    public function toggleStatus(Subscription $subscriber)
    {
        $subscriber->update([
            'is_active' => !$subscriber->is_active
        ]);

        $status = $subscriber->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => "Subscriber {$status} successfully!",
            'is_active' => $subscriber->is_active
        ]);
    }

    /**
     * Bulk actions for multiple subscribers
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:activate,deactivate,delete',
            'subscriber_ids' => 'required|array',
            'subscriber_ids.*' => 'exists:subscriptions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data.'
            ], 422);
        }

        $count = 0;
        $subscribers = Subscription::whereIn('id', $request->subscriber_ids);

        switch ($request->action) {
            case 'activate':
                $count = $subscribers->update(['is_active' => true]);
                $message = "Successfully activated {$count} subscriber(s).";
                break;

            case 'deactivate':
                $count = $subscribers->update(['is_active' => false]);
                $message = "Successfully deactivated {$count} subscriber(s).";
                break;

            case 'delete':
                $count = $subscribers->count();
                $subscribers->delete();
                $message = "Successfully deleted {$count} subscriber(s).";
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Export subscribers to CSV
     */
    public function export(Request $request)
    {
        $query = Subscription::query();

        // Apply same filters as index
        if ($request->has('search') && !empty($request->search)) {
            $query->where('email', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status == 'active' ? 1 : 0);
        }

        $subscribers = $query->orderBy('created_at', 'desc')->get();

        $filename = 'subscribers_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($subscribers) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, ['Email', 'Status', 'Subscribed Date', 'Last Updated']);

            foreach ($subscribers as $subscriber) {
                fputcsv($file, [
                    $subscriber->email,
                    $subscriber->is_active ? 'Active' : 'Inactive',
                    $subscriber->subscribed_at->format('Y-m-d H:i:s'),
                    $subscriber->updated_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
