<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RecalculateRisk;
use App\Models\ContactContribution;
use App\Models\ModerationLog;
use App\Models\PhoneNumber;
use App\Models\PhoneTag;
use App\Models\Report;
use App\Models\Review;
use App\Models\Tag;
use App\Services\ContributionAggregator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminModerationController extends Controller
{
    use AuthorizesRequests;

    public function reports(Request $request): JsonResponse
    {
        $this->authorize('moderate', Report::class);

        $items = Report::query()
            ->with(['phoneNumber', 'user', 'moderator'])
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s), fn ($q) => $q->where('status', 'pending'))
            ->latest()
            ->paginate(15);

        return response()->json($items);
    }

    public function moderateReport(Request $request, Report $report): JsonResponse
    {
        $this->authorize('moderate', Report::class);

        $data = $request->validate([
            'action' => ['required', 'in:approved,rejected'],
            'reason' => ['required_if:action,rejected', 'nullable', 'string', 'max:1000'],
        ]);

        $report->update([
            'status' => $data['action'],
            'moderated_by' => $request->user()->id,
            'moderated_at' => now(),
        ]);

        ModerationLog::create([
            'admin_id' => $request->user()->id,
            'target_type' => 'report',
            'target_id' => $report->id,
            'action' => $data['action'],
            'reason' => $data['reason'] ?? null,
        ]);

        if ($data['action'] === 'approved') {
            $report->load('phoneNumber');
            RecalculateRisk::dispatch($report->phoneNumber);
        }

        return response()->json(['report' => $report]);
    }

    public function reviews(Request $request): JsonResponse
    {
        $this->authorize('moderate', Review::class);

        $items = Review::query()
            ->with(['phoneNumber', 'user', 'moderator'])
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s), fn ($q) => $q->where('status', 'pending'))
            ->latest()
            ->paginate(15);

        return response()->json($items);
    }

    public function moderateReview(Request $request, Review $review): JsonResponse
    {
        $this->authorize('moderate', Review::class);

        $data = $request->validate([
            'action' => ['required', 'in:approved,rejected'],
            'reason' => ['required_if:action,rejected', 'nullable', 'string', 'max:1000'],
        ]);

        $review->update([
            'status' => $data['action'],
            'moderated_by' => $request->user()->id,
            'moderated_at' => now(),
        ]);

        ModerationLog::create([
            'admin_id' => $request->user()->id,
            'target_type' => 'review',
            'target_id' => $review->id,
            'action' => $data['action'],
            'reason' => $data['reason'] ?? null,
        ]);

        if ($data['action'] === 'approved') {
            $review->load('phoneNumber');
            RecalculateRisk::dispatch($review->phoneNumber);
        }

        return response()->json(['review' => $review]);
    }

    public function tags(Request $request): JsonResponse
    {
        $this->authorize('moderate', Tag::class);

        $items = PhoneTag::query()
            ->with(['phoneNumber', 'tag', 'user'])
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s), fn ($q) => $q->where('status', 'pending'))
            ->latest()
            ->paginate(15);

        return response()->json($items);
    }

    public function moderateTag(Request $request, PhoneTag $phoneTag): JsonResponse
    {
        $this->authorize('moderate', Tag::class);

        $data = $request->validate([
            'action' => ['required', 'in:approved,rejected'],
            'reason' => ['required_if:action,rejected', 'nullable', 'string', 'max:1000'],
        ]);

        $phoneTag->update([
            'status' => $data['action'],
        ]);

        ModerationLog::create([
            'admin_id' => $request->user()->id,
            'target_type' => 'phone_tag',
            'target_id' => $phoneTag->id,
            'action' => $data['action'],
            'reason' => $data['reason'] ?? null,
        ]);

        if ($data['action'] === 'approved') {
            $phoneTag->load('phoneNumber');
            RecalculateRisk::dispatch($phoneTag->phoneNumber);
        }

        return response()->json(['phone_tag' => $phoneTag]);
    }

    public function contributions(Request $request): JsonResponse
    {
        $this->authorize('moderate', ContactContribution::class);

        $items = ContactContribution::query()
            ->with(['phoneNumber', 'user'])
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s), fn ($q) => $q->where('status', 'pending'))
            ->latest()
            ->paginate(15);

        return response()->json($items);
    }

    public function moderateContribution(Request $request, ContactContribution $contribution, ContributionAggregator $aggregator): JsonResponse
    {
        $this->authorize('moderate', ContactContribution::class);

        $data = $request->validate([
            'action' => ['required', 'in:approved,rejected'],
            'reason' => ['required_if:action,rejected', 'nullable', 'string', 'max:1000'],
        ]);

        $contribution->update([
            'status' => $data['action'],
        ]);

        ModerationLog::create([
            'admin_id' => $request->user()->id,
            'target_type' => 'contact_contribution',
            'target_id' => $contribution->id,
            'action' => $data['action'],
            'reason' => $data['reason'] ?? null,
        ]);

        $contribution->load('phoneNumber');
        $aggregator->forNumber($contribution->phoneNumber);

        return response()->json(['contribution' => $contribution]);
    }
}