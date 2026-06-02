<?php

namespace Icinga\Module\Netdisco\Model;

use ipl\Orm\Model;
use ipl\Orm\Relations;

class DevicePortVlan extends Model
{
    public function getTableName(): string
    {
        return 'device_port_vlan';
    }

    public function getKeyName()
    {
        return ['ip', 'port', 'vlan'];
    }

    public function getColumns(): array
    {
        return [
            'ip',
            'port',
            'vlan',
            'native',
            'creation',
            'last_discover',
            'vlantype'
        ];
    }

    public function createRelations(Relations $relations): void
    {
        $relations->belongsTo('devicePort', DevicePort::class)
            ->setCandidateKey(['ip', 'port'])
            ->setForeignKey(['ip', 'port']);
    }
}
