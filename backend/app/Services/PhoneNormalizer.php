<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Normalisasi nomor telepon Indonesia menjadi format E.164: +6281234567890
 *
 * Input yang didukung:
 *   081234567890  -> +6281234567890
 *   +6281234567890
 *   6281234567890
 *   (dengan spasi/dash diabaikan)
 */
class PhoneNormalizer
{
    public const DEFAULT_COUNTRY_CODE = '+62';

    public function normalize(string $input): string
    {
        $clean = $this->clean($input);

        if ($clean === '') {
            throw new InvalidArgumentException('Nomor telepon tidak valid.');
        }

        if ($clean[0] === '+') {
            $result = $clean;
        } elseif (str_starts_with($clean, '62')) {
            $result = '+'.$clean;
        } elseif ($clean[0] === '0') {
            $result = self::DEFAULT_COUNTRY_CODE.substr($clean, 1);
        } else {
            $result = self::DEFAULT_COUNTRY_CODE.$clean;
        }

        if (! $this->isValid($result)) {
            throw new InvalidArgumentException('Format nomor telepon tidak dikenali.');
        }

        return $result;
    }

    public function clean(string $input): string
    {
        return preg_replace('/[\s\-().]/', '', trim($input)) ?? '';
    }

    public function isValid(string $normalized): bool
    {
        // +62 diikuti 8-13 digit
        return (bool) preg_match('/^\+62\d{8,13}$/', $normalized);
    }

    public function display(string $normalized): string
    {
        // +6281234567890 -> +62 812-3456-7890
        if (preg_match('/^\+62(\d{3})(\d{4})(\d{4,})$/', $normalized, $m)) {
            return '+62 '.$m[1].'-'.$m[2].'-'.$m[3];
        }

        return $normalized;
    }
}