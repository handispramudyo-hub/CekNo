<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(): JsonResponse
    {
        $this->authorize('viewAnalytics', auth()->user());

        $riskDistribution = DB::table('phone_numbers')
            ->selectRaw('risk_level, count(*) as total')
            ->groupBy('risk_level')
            ->pluck('total', 'risk_level');

        $reports7d = DB::table('reports')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $reportsByCategory = DB::table('reports')
            ->selectRaw('category, count(*) as total')
            ->where('status', 'approved')
            ->groupBy('category')
            ->pluck('total', 'category');

        return response()->json([
            'risk_distribution' => $riskDistribution,
            'reports_last_7_days' => $reports7d,
            'reports_by_category' => $reportsByCategory,
        ]);
    }
}