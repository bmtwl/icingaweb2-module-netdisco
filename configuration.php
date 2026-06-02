<?php

use Icinga\Application\Icinga;

$this->providePermission('netdisco/show', $this->translate('Show Netdisco data in IcingaDB'));
$this->providePermission('netdisco/config', $this->translate('Configure Netdisco module'));

$this->provideConfigTab('database', [
    'title' => $this->translate('Configure Netdisco'),
    'label' => $this->translate('Configuration'),
    'url'   => 'config/database'
]);
