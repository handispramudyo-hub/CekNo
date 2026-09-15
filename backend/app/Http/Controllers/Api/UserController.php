<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function history(Request $request): JsonResponse
    {
        $items = $request->user()
            ->searchHistories()
            ->with('phoneNumber')
            ->latest()
            ->limit(100)
            ->get();

        return response()->json(['history' => $items]);
    }

    public function reports(Request $request): JsonResponse
    {
        $items = $request->user()
            ->reports()
            ->with('phoneNumber')
            ->latest()
            ->paginate(15);

        return response()->json($items);
    }

    public function reviews(Request $request): JsonResponse
    {
        $items = $request->user()
            ->reviews()
            ->with('phoneNumber')
            ->latest()
            ->paginate(15);

        return response()->json($items);
    }

    public function tags(Request $request): JsonResponse
    {
        $items = $request->user()
            ->phoneTags()
            ->with(['tag', 'phoneNumber'])
            ->latest()
            ->paginate(15);

        return response()->json($items);
    }
}