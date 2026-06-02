<?php

namespace Icinga\Module\Netdisco\Controllers;

use Icinga\Application\Config;
use Icinga\Module\Netdisco\Forms\Config\DatabaseConfigForm;
use Icinga\Web\Controller\ModuleActionController;
use ipl\Html\HtmlString;

class ConfigController extends ModuleActionController
{
    public function init()
    {
        $this->assertPermission('netdisco/config');
    }

    public function databaseAction()
    {
        $form = (new DatabaseConfigForm())
            ->setIniConfig(Config::module('netdisco'));

        $form->handleRequest();

        $this->view->tabs = $this->Module()->getConfigTabs()->activate('database');
        $this->view->title = $this->translate('Netdisco Configuration');
        $this->view->form = new HtmlString($form->render());
    }
}
