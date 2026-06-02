<?php

namespace Icinga\Module\Netdisco\Model;

use ipl\Orm\Model;
use ipl\Orm\Relations;

class NodeIp extends Model
{
    public function getTableName(): string
    {
        return 'node_ip';
    }

    public function getKeyName()
    {
        return ['mac', 'ip'];
    }

    public function getColumns(): array
    {
        return [
            'mac',
            'ip',
            'active',
            'time_first',
            'time_last',
            'dns'
        ];
    }

    public function createRelations(Relations $relations): void
    {
        $relations->belongsTo('node', Node::class)
            ->setCandidateKey('mac')
            ->setForeignKey('mac');
    }
}
