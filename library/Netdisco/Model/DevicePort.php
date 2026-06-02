<?php

namespace Icinga\Module\Netdisco\Model;

use ipl\Orm\Model;
use ipl\Orm\Relations;

class DevicePort extends Model
{
    public function getTableName(): string
    {
        return 'device_port';
    }

    public function getKeyName()
    {
        return ['ip', 'port'];
    }

    public function getColumns(): array
    {
        return [
            'ip',
            'port',
            'creation',
            'descr',
            'up',
            'up_admin',
            'type',
            'duplex',
            'duplex_admin',
            'speed',
            'name',
            'mac',
            'mtu',
            'stp',
            'remote_ip',
            'remote_port',
            'remote_type',
            'remote_id',
            'vlan',
            'pvid',
            'lastchange',
            'manual_topo',
            'is_uplink',
            'slave_of',
            'is_master'
        ];
    }

    public function createRelations(Relations $relations): void
    {
        $relations->belongsTo('device', Device::class)
            ->setCandidateKey('ip')
            ->setForeignKey('ip');

        $relations->hasMany('devicePortVlans', DevicePortVlan::class)
            ->setForeignKey(['ip', 'port']);

        $relations->hasMany('nodes', Node::class)
            ->setForeignKey(['switch', 'port']);
    }
}
