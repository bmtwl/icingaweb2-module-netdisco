<?php

namespace Icinga\Module\Netdisco\Model;

use ipl\Orm\Model;
use ipl\Orm\Relations;

class Node extends Model
{
    public function getTableName(): string
    {
        return 'node';
    }

    public function getKeyName()
    {
        return ['mac', 'switch', 'port', 'vlan'];
    }

    public function getColumns(): array
    {
        return [
            'mac',
            'switch',
            'port',
            'vlan',
            'active',
            'oui',
            'time_first',
            'time_recent',
            'time_last'
        ];
    }

    public function createRelations(Relations $relations): void
    {
        $relations->belongsTo('devicePort', DevicePort::class)
            ->setCandidateKey(['switch', 'port'])
            ->setForeignKey(['ip', 'port']);
    }
}
