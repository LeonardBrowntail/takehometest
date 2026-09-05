<?php

namespace App\Services;

class MoneyService
{
    /**
     * Split a float money into major and minor (cents).
     *
     * @return array{major: int, minor: int}
     */
    public static function split(float $amount): array
    {
        $totalCents = (int) round($amount * 100);

        $major = intdiv($totalCents, 100);
        $minor = $totalCents % 100;

        return [
            'major' => $major,
            'minor' => $minor,
        ];
    }

    /**
     * Combines major and minor into a float.
     *
     * @param  int  $major  Major value (ex: 1 USD)
     * @param  int  $minor  Minor value (ex: 99 cents)
     * @return float combination (ex: 1.99 USD)
     */
    public static function combine(int $major, int $minor): float
    {
        $totalCents = ($major * 100) + $minor;

        return $totalCents * 0.01;
    }

    /**
     * Converts a major + minor pair into a total amount in cents.
     *
     * @param  int  $major  Major value (ex: 1 USD)
     * @param  int  $minor  Minor value (ex: 99 cents)
     */
    public static function totalCents(int $major, int $minor): int
    {
        return ($major * 100) + $minor;
    }

    /**
     * Converts total cents into an exact decimal string (ex: 10050 -> "100.50").
     */
    public static function toDecimal(int $cents): string
    {
        return sprintf('%d.%02d', intdiv($cents, 100), $cents % 100);
    }
}
