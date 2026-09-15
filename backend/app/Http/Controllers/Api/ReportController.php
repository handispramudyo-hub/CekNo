<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\PhoneNumber;
use App\Services\AntiAbuseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Request $request, PhoneNumber $number, AntiAbuseService $antiAbuse): JsonResponse
    {
        $data = $request->validate([
            'category' => ['required', 'string', 'in:'.implode(',', Report::CATEGORIES)],
            'description' => ['required', 'string', 'min:10', 'max:5000'],
            'evidence' => ['nullable', 'string', 'max:2048'],
        ]);

        $hash = hash('sha256', strtolower($data['category'].'|'.$data['description']));
        $duplicate = $number->reports()
            ->where('description_hash', $hash)
            ->where('created_at', '>=', now()->subDays(7))
            ->exists();

        if ($duplicate) {
            return response()->json(['message' => 'Laporan serupa sudah pernah dikirim untuk nomor ini.'], 422);
        }

        $report = $number->reports()->create([
            'user_id' => auth()->id(),
            'category' => $data['category'],
            'description' => $data['description'],
            'evidence' => $data['evidence'] ?? null,
            'evidence_type' => 'text',
            'description_hash' => $hash,
            'status' => 'pending',
        ]);

        return response()->json(['report' => $report], 201);
    }
}