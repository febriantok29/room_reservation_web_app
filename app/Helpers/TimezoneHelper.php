<?php

namespace App\Helpers;

use Carbon\Carbon;

class TimezoneHelper
{
    /**
     * Parse date and time in local timezone and convert to UTC.
     *
     * @param string $date Date in Y-m-d format
     * @param string $time Time in H:i format
     * @param string|null $timezone Local timezone, defaults to config
     * @return Carbon
     */
    public static function parseLocalToUtc(string $date, string $time, ?string $timezone = null): Carbon
    {
        $timezone = $timezone ?? config('app.timezone_user', 'Asia/Jakarta');

        return Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $date . ' ' . $time . ':00',
            $timezone
        )->utc();
    }

    /**
     * Build start and end Carbon instances from date and times, converted to UTC.
     *
     * @param string $date Date in Y-m-d format
     * @param string $startTime Time in H:i format
     * @param string $endTime Time in H:i format
     * @param string|null $timezone Local timezone, defaults to config
     * @return array [Carbon $startTime, Carbon $endTime]
     */
    public static function buildDateTimes(string $date, string $startTime, string $endTime, ?string $timezone = null): array
    {
        return [
            self::parseLocalToUtc($date, $startTime, $timezone),
            self::parseLocalToUtc($date, $endTime, $timezone),
        ];
    }

    /**
     * Get the local timezone for current context.
     * Checks session first, then config, defaults to Asia/Jakarta.
     * For API requests, remains UTC.
     */
    public static function getLocalTimezone(): string
    {
        // For web requests, check if user has custom timezone in session
        if (app()->runningInConsole() === false && session()->has('user_timezone')) {
            return session('user_timezone');
        }

        // Fallback to config
        return config('app.timezone_user', 'Asia/Jakarta');
    }
}
