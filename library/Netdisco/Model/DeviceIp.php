<?php

namespace Icinga\Module\Netdisco\Model;

use ipl\Orm\Model;
use ipl\Orm\Relations;

class DeviceIp extends Model
{
    public function getTableName(): string
    {
        return 'device_ip';
    }

    public function getKeyName()
    {
        return ['ip', 'alias'];
    }

    public function getColumns(): array
    {
        return [
            'ip',
            'alias',
            'subnet',
            'port',
            'dns',
            'creation'
        ];
    }

    public function createRelations(Relations $relations): void
    {
        $relations->belongsTo('device', Device::class)
            ->setCandidateKey('ip')
            ->setForeignKey('ip');
    }
}
