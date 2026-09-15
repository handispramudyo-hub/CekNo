<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PhoneNumber;
use App\Services\AntiAbuseService;
use App\Services\PhoneNormalizer;
use App\Services\RiskEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Throwable;

class NumberController extends Controller
{
    public function __construct(
        protected PhoneNormalizer $normalizer,
        protected RiskEngine $riskEngine,
        protected AntiAbuseService $antiAbuse,
    ) {}

    public function search(Request $request): JsonResponse
    {
        $request->validate(['phone' => ['required', 'string', 'max:30']]);

        try {
            $normalized = $this->normalizer->normalize($request->input('phone'));
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $phone = Cache::remember('phone:'.$normalized, 3600, function () use ($normalized) {
            return PhoneNumber::firstOrCreate(
                ['normalized_number' => $normalized],
                ['phone_number' => $normalized, 'country_code' => '+62'],
            );
        });

        $phone->increment('search_count');

        $phone->searchHistories()->create([
            'user_id' => auth()->id(),
            'guest_id' => auth()->id() ? null : $request->cookie('guest_id'),
        ]);

        // Lazy recalc: reassess bila belum ada risk assessment atau cache berlaku
        $assessment = $phone->latestRiskAssessment;
        if (! $assessment || $assessment->isDirty()) {
            $assessment = cache()->remember('risk:'.$phone->id, 300, fn () => $this->riskEngine->assess($phone));
        }

        return response()->json([
            'phone_number' => $phone,
            'risk_assessment' => $assessment,
        ]);
    }

    public function show(Request $request, PhoneNumber $number): JsonResponse
    {
        $number->load([
            'tags',
            'reports' => fn ($q) => $q->where('status', 'approved'),
            'reviews' => fn ($q) => $q->where('status', 'approved'),
        ]);

        return response()->json([
            'phone_number' => $number,
            'risk_assessment' => $number->latestRiskAssessment,
        ]);
    }
}