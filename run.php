<?php

/** @var $this \Icinga\Application\Modules\Module */

$this->provideHook('icingadb/IcingadbSupport', \Icinga\Module\Netdisco\ProvidedHook\Icingadb\IcingadbSupport::class);
$this->provideHook('icingadb/HostDetailExtension', \Icinga\Module\Netdisco\ProvidedHook\Icingadb\HostDetailExtension::class);
$this->provideHook('icingadb/Tab', \Icinga\Module\Netdisco\ProvidedHook\Icingadb\Tab::class);
