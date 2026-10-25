<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SubscriberController extends Controller
{
    public function index(Request $request)
    {
        $subscribers = Subscription::filter($request->input('search'), $request->input('status'))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        // The cards count every subscriber, not just the 20 on this page
        $stats = DB::table('subscriptions')->selectRaw(
            'COUNT(*) as total,
             SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
             SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as recent',
            [now()->subDays(30)]
        )->first();

        return view('admin_views.subscribers.index', [
            'subscribers' => $subscribers,
            'stats' => [
                'total' => (int) $stats->total,
                'active' => (int) $stats->active,
                'inactive' => (int) $stats->total - (int) $stats->active,
                'recent' => (int) $stats->recent,
            ],
        ]);
    }

    public function create()
    {
        return view('admin_views.subscribers.create');
    }

    public function store(Request $request)
    {
        $request->validate(['email' => 'required|email|max:255|unique:subscriptions,email']);

        Subscription::create([
            'email' => $request->email,
            'is_active' => $request->is_active == '1',
            'subscribed_at' => now(),
        ]);

        return redirect()->route('admin.subscribers.index')->with('success', 'Subscriber added successfully!');
    }

    public function edit(Subscription $subscriber)
    {
        return view('admin_views.subscribers.edit', compact('subscriber'));
    }

    public function update(Request $request, Subscription $subscriber)
    {
        $request->validate(['email' => ['required', 'email', 'max:255', Rule::unique('subscriptions', 'email')->ignore($subscriber->id)]]);

        $subscriber->update([
            'email' => $request->email,
            'is_active' => $request->is_active == '1',
        ]);

        return redirect()->route('admin.subscribers.index')->with('success', 'Subscriber updated successfully!');
    }

    public function destroy(Subscription $subscriber)
    {
        $subscriber->delete();

        return redirect()->route('admin.subscribers.index')->with('success', 'Subscriber deleted successfully!');
    }

    public function toggleStatus(Subscription $subscriber): JsonResponse
    {
        $subscriber->update(['is_active' => !$subscriber->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Subscriber ' . ($subscriber->is_active ? 'activated' : 'deactivated') . ' successfully!',
            'is_active' => $subscriber->is_active,
        ]);
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $validator = validator($request->all(), [
            'action' => 'required|in:activate,deactivate,delete',
            'subscriber_ids' => 'required|array',
            'subscriber_ids.*' => 'integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid request data.'], 422);
        }

        $subscribers = Subscription::whereIn('id', $request->subscriber_ids);
        $count = match ($request->action) {
            'activate' => $subscribers->update(['is_active' => true]),
            'deactivate' => $subscribers->update(['is_active' => false]),
            'delete' => $subscribers->delete(),
        };
        $done = ['activate' => 'activated', 'deactivate' => 'deactivated', 'delete' => 'deleted'][$request->action];

        return response()->json(['success' => true, 'message' => "Successfully {$done} {$count} subscriber(s)."]);
    }

    // CSV of the list as filtered on the page
    public function export(Request $request)
    {
        $query = Subscription::filter($request->input('search'), $request->input('status'))->orderByDesc('created_at');

        return response()->streamDownload(function () use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Email', 'Status', 'Subscribed Date', 'Last Updated']);

            foreach ($query->lazy() as $subscriber) {
                fputcsv($file, [
                    $this->csvCell($subscriber->email),
                    $subscriber->is_active ? 'Active' : 'Inactive',
                    $subscriber->subscribed_at?->format('Y-m-d H:i:s'),
                    $subscriber->updated_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        }, 'subscribers_' . date('Y-m-d_H-i-s') . '.csv', ['Content-Type' => 'text/csv']);
    }

    // A value starting with = + - @ would run as a formula when the file is opened in Excel
    private function csvCell(?string $value): ?string
    {
        return $value !== null && preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }
}
