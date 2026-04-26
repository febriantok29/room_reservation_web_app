<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DivisionIdGenerator
{
    /**
     * Generate unique division ID with format: DIV-NN
     */
    public static function generate(): string
    {
        $last = DB::table('m_divisions')
            ->where('id', 'LIKE', 'DIV-%')
            ->orderByDesc('id')
            ->value('id');

        $lastNum = $last ? (int) substr($last, 4) : 0;
        $newNum  = $lastNum + 1;

        if ($newNum > 99) {
            throw new \RuntimeException('Division ID sequence limit reached (max 99).');
        }

        return 'DIV-' . str_pad($newNum, 2, '0', STR_PAD_LEFT);
    }
}
