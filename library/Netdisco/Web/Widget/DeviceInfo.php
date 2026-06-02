<?php

namespace Icinga\Module\Netdisco\Web\Widget;

use Icinga\Module\Netdisco\DeviceHelper;
use Icinga\Module\Netdisco\Model\Device;
use ipl\Html\BaseHtmlElement;
use ipl\Html\Html;

class DeviceInfo extends BaseHtmlElement
{
    protected $tag = 'div';
    protected $defaultAttributes = ['class' => 'netdisco-device-info'];

    /** @var Device */
    protected $device;

    public function __construct(Device $device)
    {
        $this->device = $device;
    }

    protected function assemble(): void
    {
        $device = $this->device;

        // Compact header with key info
        $header = Html::tag('div', ['class' => 'page-row'], [
            Html::tag('h3', null, [
                Html::tag('i', ['class' => 'icon-network']),
                ' Netdisco'
            ])
        ]);

        // Single compact table with critical info only
        $info = Html::tag('table', ['class' => 'name-value-table compact-table']);

        // Row 1: IP, Vendor/Model, Location
        $info->addHtml(Html::tag('tr', null, [
            Html::tag('td', ['class' => 'device-ip'], (string) $device->ip),
            Html::tag('td', ['class' => 'device-model'], 
                ($device->vendor ?: '?') . ' ' . ($device->model ?: 'Unknown')),
            Html::tag('td', ['class' => 'device-location'], 
                $device->location ?: 'No location')
        ]));

        // Row 2: Uptime, Ports, Layers, Last Discover
        $info->addHtml(Html::tag('tr', null, [
            Html::tag('td', null, [
                Html::tag('i', ['class' => 'icon-clock']),
                ' ' . DeviceHelper::formatUptime($device->uptime)
            ]),
            Html::tag('td', null, [
                Html::tag('i', ['class' => 'icon-ethernet']),
                ' ' . ($device->ports ?? '?') . ' ports'
            ]),
            Html::tag('td', null, [
                Html::tag('i', ['class' => 'icon-sitemap']),
                ' ' . DeviceHelper::decodeLayers($device->layers)
            ]),
            Html::tag('td', null, [
                Html::tag('i', ['class' => 'icon-search']),
                ' Discovered ' . DeviceHelper::formatLastScan($device->last_discover)
            ])
        ]));

        // Row 3: OS, Serial (if available)
        $osLine = [];
        if ($device->os) {
            $osLine[] = Html::tag('td', null, $device->os . ($device->os_ver ? ' ' . $device->os_ver : ''));
        }
        if ($device->serial) {
            $osLine[] = Html::tag('td', null, 'SN: ' . $device->serial);
        }
        if (!empty($osLine)) {
            $info->addHtml(Html::tag('tr', null, $osLine));
        }

        // IP aliases (compact comma-separated)
        $aliases = Html::tag('div', ['class' => 'device-aliases']);
        if ($device->deviceIps !== null) {
            $aliasList = [];
            foreach ($device->deviceIps as $ip) {
                if ((string)$ip->alias !== (string)$device->ip) {
                    $aliasStr = (string) $ip->alias;
                    if ($ip->dns) {
                        $aliasStr .= ' (' . $ip->dns . ')';
                    }
                    $aliasList[] = $aliasStr;
                }
            }
            if (!empty($aliasList)) {
                $aliases->addHtml(
                    Html::tag('small', ['class' => 'text-muted'], 
                        'IPs: ' . implode(', ', $aliasList))
                );
            }
        }

        $this->addHtml(
            $header,
            Html::tag('div', ['class' => 'page-row'], $info),
            $aliases
        );
    }
}
