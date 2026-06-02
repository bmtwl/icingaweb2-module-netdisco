<?php

namespace Icinga\Module\Netdisco\Mrtg;

/**
 * Read MRTG .log files and extract the last 24 hours of 5-minute data.
 *
 * MRTG log format (text, newest first after header):
 *   Line 1: <timestamp> <current_in> <current_out>
 *   Line 2: <timestamp> <avg_in> <avg_out> <max_in> <max_out>  (latest 5-min)
 *   Line 3+: historical 5-minute buckets
 */
class LogReader
{
    /**
     * Read the last 24 hours from an MRTG log file.
     *
     * @param string $path Absolute path to the .log file
     * @return array List of ['time' => int, 'in' => float, 'out' => float], oldest first
     */
    public function read24h(string $path): array
    {
        if (! is_readable($path)) {
            return [];
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false || count($lines) < 3) {
            return [];
        }

        // Discard first line (raw current values)
        array_shift($lines);

        $cutoff = time() - 86400;
        $data = [];

        foreach ($lines as $line) {
            $parts = preg_split('/\s+/', trim($line));
            if (count($parts) < 3) {
                continue;
            }

            $ts = (int) $parts[0];

            // Entries are ordered newest -> oldest. Stop once we pass 24h.
            if ($ts < $cutoff) {
                break;
            }

            $data[] = [
                'time' => $ts,
                'in'   => is_numeric($parts[1]) ? (float) $parts[1] : 0.0,
                'out'  => is_numeric($parts[2]) ? (float) $parts[2] : 0.0,
            ];

            if (count($data) >= 300) {
                break;
            }
        }

        // Reverse so the SVG renders left-to-right chronologically
        return array_reverse($data);
    }
}
