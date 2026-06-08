<?php

namespace Icinga\Module\Netdisco;

use ipl\Sql\Connection;
use Icinga\Module\Netdisco\Model\Device;

class DeviceHelper
{
    /**
     * Netdisco layers bitmask to human-readable OSI layers
     */
    const OSI_LAYERS = [
        1 => 'Physical',      // bit 0 (lowest)
        2 => 'Data Link',
        3 => 'Network',
        4 => 'Transport',
        5 => 'Session',
        6 => 'Presentation',
        7 => 'Application'     // bit 6 (highest used)
    ];

    /**
     * Find device by Icinga host name (case-insensitive via PostgreSQL regex)
     */
    public static function findDeviceByHostName(Connection $db, string $hostName): ?Device
    {
        $query = Device::on($db);
        $query->getSelectBase()->where(
            'name ~* ?',
            '^' . preg_quote($hostName, '~') . '$'
        );

        return $query->first();
    }

    /**
     * Decode Netdisco layers bitmask to human readable layers
     */
    public static function decodeLayers(?string $layers): string
    {
        if (empty($layers) || !is_numeric($layers)) {
            return 'N/A';
        }

        $bits = str_pad(decbin((int) $layers), 7, '0', STR_PAD_LEFT);
        $active = [];

        // Netdisco layers: bit 0=Layer1, bit 1=Layer2, bit 2=Layer3, etc.
        // Binary string is left-padded, so index 0 = L1, index 6 = L7
        for ($i = 0; $i < 7; $i++) {
            $layerNum = $i + 1;
            if (isset($bits[6 - $i]) && $bits[6 - $i] === '1') {
                $active[] = "L{$layerNum}";
            }
        }

        if (empty($active)) {
            return 'N/A';
        }

        // Special common combinations
        $combo = implode('', array_map(function($l) { return substr($l, 1); }, $active));
        if ($combo === '23') return 'L2/L3 Switch';
        if ($combo === '2') return 'L2 Switch';
        if ($combo === '3') return 'L3 Router';
        if ($combo === '27') return 'Host/Mgmt';

        return implode('/', $active);
    }

    /**
     * Format uptime from device ticks
     */
    public static function formatUptime(?int $uptime): string
    {
        if ($uptime === null || $uptime < 0) {
            return 'N/A';
        }

        $uptime = $uptime / 100;
        $days = floor($uptime / 86400);
        $hours = floor(($uptime % 86400) / 3600);
        $mins = floor(($uptime % 3600) / 60);

        if ($days > 365) {
            return floor($days / 365) . 'y ' . ($days % 365) . 'd';
        }
        if ($days > 0) {
            return "{$days}d {$hours}h";
        }
        if ($hours > 0) {
            return "{$hours}h {$mins}m";
        }
        return "{$mins}m";
    }

    /**
     * Format speed value
     */
    public static function formatSpeed(?string $speed): string
    {
        if (empty($speed)) {
            return 'N/A';
        }

        $value = (int) $speed;

        if ($value >= 1000000000) {
            return ($value / 1000000000) . ' Gbps';
        } elseif ($value >= 1000000) {
            return ($value / 1000000) . ' Mbps';
        } elseif ($value >= 1000) {
            return ($value / 1000) . ' Kbps';
        }

        return $value . ' bps';
    }

    /**
     * Format port status
     */
    public static function formatPortStatus(?string $up, ?string $upAdmin): array
    {
        $status = 'Unknown';
        $cssClass = 'state-pending';

        if ($up === 'up') {
            $status = 'Up';
            $cssClass = 'state-up';
        } elseif ($up === 'down') {
            if ($upAdmin === 'up') {
                $status = 'Down';
                $cssClass = 'state-down';
            } else {
                $status = 'Admin Down';
                $cssClass = 'state-warning';
            }
        }

        return [$status, $cssClass];
    }

    /**
     * Format last scan time as relative
     */
    public static function formatLastScan(?string $timestamp): string
    {
        if (empty($timestamp)) {
            return 'Never';
        }

        try {
            $then = new \DateTime($timestamp);
            $now = new \DateTime();
            $diff = $now->diff($then);

            if ($diff->d > 0) return $diff->d . 'd ago';
            if ($diff->h > 0) return $diff->h . 'h ago';
            if ($diff->i > 0) return $diff->i . 'm ago';
            return 'Just now';
        } catch (\Exception $e) {
            return $timestamp;
        }
    }
}
