<?php

namespace App\Http\Controllers\Concerns;

trait NormalizesFilterValues
{
    /**
     * Normalize scalar, CSV, or array query input into a flat string array.
     *
     * @param mixed $value
     * @return array<int, string>
     */
    private function normalizeFilterValues(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        $rawValues = is_array($value) ? $value : [$value];
        $normalized = [];

        foreach ($rawValues as $raw) {
            $chunks = explode(',', (string) $raw);
            foreach ($chunks as $chunk) {
                $trimmed = trim($chunk);
                if ($trimmed !== '') {
                    $normalized[] = $trimmed;
                }
            }
        }

        return array_values(array_unique($normalized));
    }
}
