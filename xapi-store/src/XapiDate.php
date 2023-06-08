<?php

namespace Trax\XapiStore;

class XapiDate
{
    /**
     * Get the present time.
     *
     * @return string
     */
    public static function now(): string
    {
        try {
            list($seconds, $micro) = explode('.', microtime(true));
            // We need 4 digits precision.
            $micro = substr($micro.'000', 0, 4);
        } catch (\Exception $e) {
            // $micro may be missing.
            $seconds = microtime(true);
            $micro = '0000';
        }
        return date('Y-m-d\TH:i:s.', $seconds) . $micro . 'Z';
    }

    /**
     * Normalize a given ISO date to enable later comparison between dates.
     *
     * @param  string  $isoDate
     * @return string
     */
    public static function normalize(string $isoDate): string
    {
        // Convert to Unix timestamp.
        // Microseconds are lost.
        // Timezone is taken into account.
        $timestamp = strtotime($isoDate);

        // We need to extract microseconds from the original ISO date and normalize it.

        // The delimiter may be 'T' or ' ', which are both accepted by the validation rules.
        $delimiter = \Str::contains($isoDate, 'T') ? 'T' : ' ';
        $dateTime = explode($delimiter, $isoDate);

        // We may have only the date, not the time.
        $time = isset($dateTime[1]) ? $dateTime[1] : '00:00:00';

        // Remove the timezone at the end.
        if (strpos($time, '+') !== false) {
            list($time, $forget) = explode('+', $time);
        } elseif (strpos($time, '-') !== false) {
            list($time, $forget) = explode('-', $time);
        } elseif (strpos($time, 'Z') !== false) {
            list($time) = explode('Z', $time);
        }
        
        // Extract and normalize microseconds (4 digits precision).
        try {
            list($seconds, $micro) = explode('.', $time);
            $micro = substr($micro.'000', 0, 4);
        } catch (\Exception $e) {
            $micro = '0000';
        }

        return date('Y-m-d\TH:i:s.', $timestamp) . $micro . 'Z';
    }
}
