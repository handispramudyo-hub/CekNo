<?php

namespace Tests\Unit;

use App\Services\PhoneNormalizer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PhoneNormalizerTest extends TestCase
{
    private PhoneNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizer = new PhoneNormalizer;
    }

    #[DataProvider('validProvider')]
    public function test_normalizes_valid_variants(string $input, string $expected): void
    {
        $this->assertSame($expected, $this->normalizer->normalize($input));
    }

    public static function validProvider(): array
    {
        return [
            '08xx' => ['081234567890', '+6281234567890'],
            'with leading dash' => ['0812-3456-7890', '+6281234567890'],
            'with plus' => ['+6281234567890', '+6281234567890'],
            '62 without plus' => ['6281234567890', '+6281234567890'],
            'spaces' => ['0812 3456 7890', '+6281234567890'],
            'dot' => ['0812.3456.7890', '+6281234567890'],
        ];
    }

    #[DataProvider('invalidProvider')]
    public function test_rejects_invalid(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->normalizer->normalize($input);
    }

    public static function invalidProvider(): array
    {
        return [
            'empty' => [''],
            'too short' => ['081'],
            'contains letters' => ['0812abcdef'],
            'wrong cc' => ['+14155550111'],
        ];
    }

    public function test_display_formats_e164(): void
    {
        $this->assertSame('+62 812-3456-7890', $this->normalizer->display('+6281234567890'));
        $this->assertSame('+62812', $this->normalizer->display('+62812'));
    }
}