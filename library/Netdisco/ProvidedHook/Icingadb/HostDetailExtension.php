<?php

namespace Icinga\Module\Netdisco\ProvidedHook\Icingadb;

use Icinga\Authentication\Auth;
use Icinga\Module\Icingadb\Hook\HostDetailExtensionHook;
use Icinga\Module\Icingadb\Model\Host;
use Icinga\Module\Netdisco\Common\Database;
use Icinga\Module\Netdisco\DeviceHelper;
use Icinga\Module\Netdisco\Web\Widget\DeviceInfo;
use ipl\Html\ValidHtml;
use ipl\Html\Html;
use Exception;

class HostDetailExtension extends HostDetailExtensionHook
{
    use Database;

    public function getHtmlForObject(Host $host): ValidHtml
    {
        if (! Auth::getInstance()->hasPermission('netdisco/show')) {
            return Html::tag('div');
        }

        try {
            $device = DeviceHelper::findDeviceByHostName($this->getDb(), $host->name);
        } catch (Exception $e) {
            return Html::tag('div');
        }

        if ($device === null) {
            return Html::tag('div');
        }

        return Html::tag('div', ['class' => 'netdisco-section'], [
            new DeviceInfo($device)
        ]);
    }
}
