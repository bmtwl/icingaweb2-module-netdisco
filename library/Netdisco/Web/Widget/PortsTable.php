<?php

namespace Icinga\Module\Netdisco\Web\Widget;

use Icinga\Module\Netdisco\Model\DevicePort;
use ipl\Html\BaseHtmlElement;
use ipl\Html\Html;

class PortsTable extends BaseHtmlElement
{
    protected $tag = 'table';
    protected $defaultAttributes = ['class' => 'common-table table-row-selectable'];

    /** @var iterable|DevicePort[] */
    protected $ports;

    /** @var array [port => [['vlan' => '10', 'native' => true], ...]] */
    protected $portVlans;

    /** @var array [port => [mac, ip, company]] */
    protected $portNodes;

    /** @var array */
    protected $mrtgConfig;

    public function __construct(iterable $ports, array $portVlans = [], array $portNodes = [], array $mrtgConfig = [])
    {
        $this->ports = $ports;
        $this->portVlans = $portVlans;
        $this->portNodes = $portNodes;
        $this->mrtgConfig = $mrtgConfig;
    }

    protected function assemble(): void
    {
        $thead = Html::tag('thead');
        $headerCells = [
            Html::tag('th', null, 'Port'),
            Html::tag('th', null, 'Name'),
            Html::tag('th', null, 'Status'),
            Html::tag('th', null, 'Speed'),
            Html::tag('th', null, 'VLANs'),
        ];

        $mrtgEnabled = ! empty($this->mrtgConfig['base_url']) || ! empty($this->mrtgConfig['log_path']);
        if ($mrtgEnabled) {
            $headerCells[] = Html::tag('th', ['class' => 'mrtg-header', 'title' => 'MRTG Graphs'], 'MRTG');
        }

        $headerCells[] = Html::tag('th', null, 'Last Node');
        $headerCells[] = Html::tag('th', null, 'Node IP');

        $thead->addHtml(Html::tag('tr', null, $headerCells));
        $this->addHtml($thead);

        $tbody = Html::tag('tbody');

        foreach ($this->ports as $port) {
            $vlans = $this->portVlans[$port->port] ?? [];
            $nodeInfo = $this->portNodes[$port->port] ?? ['N/A', 'N/A', ''];
            $tbody->addHtml(new PortRow($port, $vlans, $nodeInfo, $this->mrtgConfig));
        }

        $this->addHtml($tbody);
    }
}
