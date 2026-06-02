<?php

namespace Icinga\Module\Netdisco\Model;

use ipl\Orm\Model;
use ipl\Orm\Relations;

class Device extends Model
{
    public function getTableName(): string
    {
        return 'device';
    }

    public function getKeyName()
    {
        return 'ip';
    }

    public function getColumns(): array
    {
        return [
            'ip',
            'dns',
            'name',
            'location',
            'description',
            'vendor',
            'model',
            'serial',
            'os',
            'os_ver',
            'layers',
            'last_discover',
            'last_macsuck',
            'last_arpnip',
            'uptime',
            'slots',
            'ports',
            'ps1_type',
            'ps2_type',
            'fan',
            'creation'
        ];
    }

    public function createRelations(Relations $relations): void
    {
        $relations->hasMany('devicePorts', DevicePort::class)
            ->setForeignKey('ip');

        $relations->hasMany('deviceIps', DeviceIp::class)
            ->setForeignKey('ip');
    }
}
