<?php

namespace Icinga\Module\Netdisco\Web\Widget;

use Icinga\Module\Netdisco\DeviceHelper;
use Icinga\Module\Netdisco\Mrtg\LogReader;
use Icinga\Module\Netdisco\Mrtg\SparklineRenderer;
use Icinga\Module\Netdisco\Model\DevicePort;
use ipl\Html\BaseHtmlElement;
use ipl\Html\Html;
use ipl\Html\HtmlString;

class PortRow extends BaseHtmlElement
{
    protected $tag = 'tr';
    protected $defaultAttributes = ['class' => 'port-row'];

    /** @var DevicePort */
    protected $port;

    /** @var array [['vlan' => '10', 'native' => true], ...] */
    protected $vlans;

    /** @var array [mac, ip, company] */
    protected $nodeInfo;

    /** @var array */
    protected $mrtgConfig;

    public function __construct(DevicePort $port, array $vlans = [], array $nodeInfo = ['N/A', 'N/A', ''], array $mrtgConfig = [])
    {
        $this->port = $port;
        $this->vlans = $vlans;
        $this->nodeInfo = $nodeInfo;
        $this->mrtgConfig = $mrtgConfig;
    }

    protected function assemble(): void
    {
        $port = $this->port;
        [$statusText, $statusClass] = DeviceHelper::formatPortStatus($port->up, $port->up_admin);
        $vlanHtml = $this->formatVlans();
        [$nodeMac, $nodeIp, $nodeCompany] = $this->nodeInfo;

        $speed = $port->speed ?: 'N/A';

        $cells = [
            Html::tag('td', null, $port->port),
            Html::tag('td', null, $port->name ?: $port->descr ?: 'N/A'),
            Html::tag('td', null, Html::tag('span', ['class' => "state-ball {$statusClass}"], $statusText)),
            Html::tag('td', null, $speed),
            Html::tag('td', null, new HtmlString($vlanHtml)),
        ];

        $mrtgEnabled = ! empty($this->mrtgConfig['base_url']) || ! empty($this->mrtgConfig['log_path']);
        if ($mrtgEnabled) {
            $cells[] = Html::tag('td', ['class' => 'mrtg-cell'], $this->createMrtgCell());
        }

        $cells[] = Html::tag('td', null, $nodeMac . ($nodeCompany ? ' (' . $nodeCompany . ')' : ''));
        $cells[] = Html::tag('td', null, $nodeIp);

        foreach ($cells as $cell) {
            $this->addHtml($cell);
        }
    }

    /**
     * Build sparkline from local MRTG log, or fall back to icon links.
     *
     * @return array
     */
    protected function createMrtgCell(): array
    {
        $port = $this->port;
        $logPath = $this->mrtgConfig['log_path'] ?? '';
        $baseUrl = $this->mrtgConfig['base_url'] ?? '';
        $field   = $this->mrtgConfig['port_field'] ?? 'port';
        $portId  = $port->$field ?? $port->port;

        // Convert 1/1/1 style port IDs to 1_1_1 for MRTG file/URL naming
        $portIdSafe = strtolower(str_replace('/', '_', (string) $portId));
        $switchPort = (string) $port->ip . '_' . $portIdSafe;

        // 1. Attempt sparkline from local log file
        if (! empty($logPath)) {
            $safeIp     = strtolower(str_replace(['..', '/', '\\'], '', (string)$port->ip));
            $safePortId = strtolower(str_replace(['..', '\\'], '',$portIdSafe));
            $fullPath   = rtrim($logPath, '/') . '/' . $safeIp . '_' . $safePortId . '.log';

            $data = (new LogReader())->read24h($fullPath);
            if (! empty($data)) {
                $svg = SparklineRenderer::render($data, 90, 24);
                $svgElement = new HtmlString($svg);

                if (! empty($baseUrl)) {
                    return [Html::tag('a', [
                        'href'   => rtrim($baseUrl, '/') . '/' . urlencode($switchPort) . '.html',
                        'target' => '_blank',
                        'class'  => 'mrtg-sparkline-wrap',
                        'title'  => 'MRTG (24h)'
                    ], $svgElement)];
                }

                return [Html::tag('span', ['class' => 'mrtg-sparkline-wrap'], $svgElement)];
            }
        }

        // 2. Fall back to icon links when no log path or file missing
        if (! empty($baseUrl)) {
            return [
                Html::tag('a', [
                    'href'   => rtrim($baseUrl, '/') . '/' . urlencode($switchPort) . '.html',
                    'target' => '_blank',
                    'class'  => 'mrtg-link',
                    'title'  => 'MRTG Traffic'
                ], Html::tag('i', ['class' => 'icon-chart-bar'])),
                Html::tag('a', [
                    'href'   => rtrim($baseUrl, '/') . '/' . urlencode($switchPort) . '_errors.html',
                    'target' => '_blank',
                    'class'  => 'mrtg-link mrtg-errors',
                    'title'  => 'MRTG Errors'
                ], Html::tag('i', ['class' => 'icon-exclamation-sign']))
            ];
        }

        return [Html::tag('span', null, '-')];
    }

    protected function formatVlans(): string
    {
        if (empty($this->vlans)) {
            return 'N/A';
        }

        $parts = [];
        foreach ($this->vlans as $vlan) {
            $num = htmlspecialchars($vlan['vlan']);
            if ($vlan['native']) {
                $parts[] = '<strong>' . $num . '</strong>';
            } else {
                $parts[] = $num;
            }
        }

        return implode(', ', $parts);
    }
}
