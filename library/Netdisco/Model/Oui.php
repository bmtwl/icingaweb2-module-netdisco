<?php

namespace Icinga\Module\Netdisco\Model;

use ipl\Orm\Model;

class Oui extends Model
{
    public function getTableName(): string
    {
        return 'oui';
    }

    public function getKeyName()
    {
        return 'oui';
    }

    public function getColumns(): array
    {
        return [
            'oui',
            'company',
            'abbrev'
        ];
    }
}
