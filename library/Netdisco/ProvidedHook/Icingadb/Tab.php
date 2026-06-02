<?php

namespace Icinga\Module\Netdisco\ProvidedHook\Icingadb;

use Icinga\Authentication\Auth;
use Icinga\Module\Icingadb\Hook\TabHook;
use Icinga\Module\Icingadb\Model\Host;
use Icinga\Module\Netdisco\Common\Database;
use Icinga\Module\Netdisco\DeviceHelper;
use Icinga\Module\Netdisco\Web\Widget\PortsTable;
use ipl\Html\Html;
use ipl\Orm\Model;
use ipl\Stdlib\Filter;
use Exception;

class Tab extends TabHook
{
    use Database;

    public function getName(): string
    {
        return 'netdisco-ports';
    }

    public function getLabel(): string
    {
        return t('Netdisco Ports');
    }

    /**
     * Only show tab if host has matching Netdisco device
     */
    public function shouldBeShown(Model $object): bool
    {
        if (! $object instanceof Host) {
            return false;
        }

        if (! Auth::getInstance()->hasPermission('netdisco/show')) {
            return false;
        }

        try {
            $device = DeviceHelper::findDeviceByHostName($this->getDb(), $object->name);
            return $device !== null;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getContent(Model $object): array
    {
        if (! $object instanceof Host) {
            return [];
        }

        if (! Auth::getInstance()->hasPermission('netdisco/show')) {
            return [];
        }

        try {
            $db = $this->getDb();
            $device = DeviceHelper::findDeviceByHostName($db, $object->name);
        } catch (Exception $e) {
            return [];
        }

        if ($device === null) {
            return [];
        }

        $ports = \Icinga\Module\Netdisco\Model\DevicePort::on($db)
            ->filter(Filter::equal('ip', $device->ip))
            ->orderBy('port')
            ->execute();

        $hasPorts = false;
        foreach ($ports as $_) {
            $hasPorts = true;
            break;
        }

        if (! $hasPorts) {
            return [
                Html::tag('div', ['class' => 'info-box'], 'No ports discovered for this device.')
            ];
        }

        $portVlans = $this->fetchPortVlans($db, $device->ip);
        $portNodes = $this->fetchPortNodes($db, $device->ip);
        $mrtgConfig = $this->getMrtgConfig();

        return [
            Html::tag('div', ['class' => 'netdisco-ports-section'], [
                Html::tag('h3', null, 'Device Ports'),
                new PortsTable($ports, $portVlans, $portNodes, $mrtgConfig)
            ])
        ];
    }

    protected function fetchPortVlans($db, string $deviceIp): array
    {
        $vlans = \Icinga\Module\Netdisco\Model\DevicePortVlan::on($db)
            ->filter(Filter::equal('ip', $deviceIp))
            ->orderBy('port')
            ->execute();

        $portVlans = [];
        foreach ($vlans as $vlan) {
            $port = $vlan->port;
            if (!isset($portVlans[$port])) {
                $portVlans[$port] = [];
            }
            $portVlans[$port][] = [
                'vlan' => (string) $vlan->vlan,
                'native' => (bool) $vlan->native
            ];
        }

        return $portVlans;
    }

    protected function fetchPortNodes($db, string $deviceIp): array
    {
        $nodes = \Icinga\Module\Netdisco\Model\Node::on($db)
            ->filter(Filter::equal('switch', $deviceIp))
            ->filter(Filter::equal('active', true))
            ->orderBy('time_last', 'DESC')
            ->execute();

        $portNodes = [];
        $nodeMacs = [];

        foreach ($nodes as $node) {
            $port = $node->port;
            if (isset($portNodes[$port])) {
                continue;
            }

            $mac = (string) $node->mac;
            $portNodes[$port] = [
                'mac' => $mac,
                'oui' => $node->oui,
            ];
            $nodeMacs[$mac] = true;
        }

        if (empty($nodeMacs)) {
            return [];
        }

        $ouis = [];
        try {
            $ouiResult = \Icinga\Module\Netdisco\Model\Oui::on($db)
                ->filter(Filter::equal('oui', array_keys($nodeMacs)))
                ->execute();

            foreach ($ouiResult as $oui) {
                $ouis[$oui->oui] = $oui->company;
            }
        } catch (Exception $e) {
        }

        $nodeIps = [];
        try {
            $ipResult = \Icinga\Module\Netdisco\Model\NodeIp::on($db)
                ->filter(Filter::equal('mac', array_keys($nodeMacs)))
                ->filter(Filter::equal('active', true))
                ->orderBy('time_last', 'DESC')
                ->execute();

            foreach ($ipResult as $ip) {
                $mac = (string) $ip->mac;
                if (isset($nodeIps[$mac])) {
                    continue;
                }

                $ipStr = (string) $ip->ip;
                if ($ip->dns) {
                    $ipStr .= ' (' . $ip->dns . ')';
                }
                $nodeIps[$mac] = $ipStr;
            }
        } catch (Exception $e) {
        }

        $result = [];
        foreach ($portNodes as $port => $node) {
            $mac = $node['mac'];
            $result[$port] = [
                $mac,
                $nodeIps[$mac] ?? 'N/A',
                $ouis[$node['oui']] ?? ''
            ];
        }

        return $result;
    }
}
