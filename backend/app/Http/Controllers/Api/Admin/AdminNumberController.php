<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhoneNumber;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminNumberController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('manage', PhoneNumber::class);

        $numbers = PhoneNumber::query()
            ->with('latestRiskAssessment')
            ->when($request->input('level'), fn ($q, $l) => $q->where('risk_level', $l))
            ->when($request->input('search'), fn ($q, $s) => $q->where('normalized_number', 'like', "%$s%"))
            ->paginate(15);

        return response()->json($numbers);
    }

    public function updateStatus(Request $request, PhoneNumber $number): JsonResponse
    {
        $this->authorize('manage', PhoneNumber::class);

        $data = $request->validate(['status' => ['required', 'in:active,hidden']]);

        $number->forceFill(['status' => $data['status']])->save();

        // Buang cache lookup nomor agar status baru langsung terlihat.
        Cache::forget('phone:'.$number->normalized_number);

        return response()->json(['phone_number' => $number]);
    }
}