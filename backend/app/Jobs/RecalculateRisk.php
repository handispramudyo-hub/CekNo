<?php

namespace App\Jobs;

use App\Models\PhoneNumber;
use App\Services\RiskEngine;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecalculateRisk implements ShouldQueue
{
    use Queueable;

    public int $timeout = 60;

    public function __construct(public PhoneNumber $phoneNumber)
    {
    }

    public function handle(RiskEngine $engine): void
    {
        $assessment = $engine->assess($this->phoneNumber);

        $this->phoneNumber->forceFill([
            'risk_score' => $assessment->final_score,
            'risk_level' => $assessment->risk_level,
        ])->save();

        // Invalidate cache risk score
        cache()->forget('risk:'.$this->phoneNumber->id);
    }
}