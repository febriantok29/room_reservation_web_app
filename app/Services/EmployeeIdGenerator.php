<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class EmployeeIdGenerator
{
    /**
     * Generate employee ID for a division user.
     * Format: {DIV_CODE}-{YEAR}-{NNNNN}
     * Example: OPS-2026-00001, KNP-2026-00003
     *
     * @param  string  $divisionCode  Short division code (e.g. OPS, KNP, HRD)
     * @param  int|null $year         Year to use (default: current year)
     */
    public static function generate(string $divisionCode, ?int $year = null): string
    {
        $year   = $year ?? now()->year;
        $prefix = strtoupper($divisionCode) . '-' . $year . '-';

        $last = DB::table('s_users')
            ->where('employee_id', 'LIKE', "{$prefix}%")
            ->orderByDesc('employee_id')
            ->value('employee_id');

        $lastNum = $last ? (int) substr($last, -5) : 0;
        $newNum  = $lastNum + 1;

        return $prefix . str_pad($newNum, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generate employee ID for an admin user (no division).
     * Format: ADM-{YEAR}-{NN}
     * Example: ADM-2026-01
     */
    public static function generateAdmin(?int $year = null): string
    {
        $year   = $year ?? now()->year;
        $prefix = 'ADM-' . $year . '-';

        $last = DB::table('s_users')
            ->where('employee_id', 'LIKE', "{$prefix}%")
            ->orderByDesc('employee_id')
            ->value('employee_id');

        $lastNum = $last ? (int) substr($last, -2) : 0;
        $newNum  = $lastNum + 1;

        if ($newNum > 99) {
            throw new \RuntimeException('Admin employee ID sequence limit reached for this year (max 99).');
        }

        return $prefix . str_pad($newNum, 2, '0', STR_PAD_LEFT);
    }
}
