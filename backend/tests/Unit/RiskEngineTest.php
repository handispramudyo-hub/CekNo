<?php

namespace Tests\Unit;

use App\Services\RiskEngine;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class RiskEngineTest extends TestCase
{
    /**
     * Test renormalisasi bobot: tanpa IndoBERT total bobot tetap 1.
     */
    public function test_weights_without_indobert_sum_to_one(): void
    {
        $engine = $this->partial();

        $weights = $engine->weights(null);
        $this->assertSame(1.0, round(array_sum($weights), 4));

        // Bobot XGBoost naik menyerap porsi IndoBERT
        $this->assertGreaterThan(0.40, $weights[0]);
        $this->assertEquals(0.0, $weights[1]);
    }

    public function test_weights_with_indobert_unchanged(): void
    {
        $engine = $this->partial();
        $weights = $engine->weights(0.9);

        $this->assertSame([0.40, 0.25, 0.20, 0.15], $weights);
    }

    public function test_classify_thresholds(): void
    {
        $engine = $this->partial();

        $this->assertSame('low', $engine->classify(0));
        $this->assertSame('low', $engine->classify(24));
        $this->assertSame('caution', $engine->classify(25));
        $this->assertSame('caution', $engine->classify(49));
        $this->assertSame('risky', $engine->classify(50));
        $this->assertSame('risky', $engine->classify(74));
        $this->assertSame('high', $engine->classify(75));
        $this->assertSame('high', $engine->classify(100));
    }

    private function partial(): RiskEngine
    {
        // Instantiate tanpa constructor dependencies (hanya cek weights & classify)
        return (new \ReflectionClass(RiskEngine::class))->newInstanceWithoutConstructor();
    }
}