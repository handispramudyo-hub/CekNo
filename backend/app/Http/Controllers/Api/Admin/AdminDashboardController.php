<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModerationLog;
use App\Models\PhoneNumber;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(): JsonResponse
    {
        $this->authorize('moderate', User::class);

        return response()->json([
            'pending_reports' => \App\Models\Report::where('status', 'pending')->count(),
            'pending_reviews' => \App\Models\Review::where('status', 'pending')->count(),
            'pending_tags' => \App\Models\PhoneTag::where('status', 'pending')->count(),
            'new_users' => User::where('created_at', '>=', now()->subDays(7))->count(),
            'total_numbers' => PhoneNumber::count(),
            'recent_moderation' => ModerationLog::with('admin')->latest()->limit(10)->get(),
        ]);
    }
}