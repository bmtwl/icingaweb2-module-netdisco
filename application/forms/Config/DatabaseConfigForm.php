<?php

namespace Icinga\Module\Netdisco\Forms\Config;

use Icinga\Data\ResourceFactory;
use Icinga\Forms\ConfigForm;

class DatabaseConfigForm extends ConfigForm
{
    public function init()
    {
        $this->setName('form_config_database');
        $this->setSubmitLabel($this->translate('Save Changes'));
    }

    public function createElements(array $formData)
    {
        $dbResources = ResourceFactory::getResourceConfigs('db')->keys();

        $this->addElement('select', 'database_resource', [
            'label'        => $this->translate('Database Resource'),
            'description'  => $this->translate('PostgreSQL database resource for Netdisco'),
            'multiOptions' => array_merge(
                ['' => $this->translate('Please choose')],
                array_combine($dbResources, $dbResources)
            ),
            'required'     => true
        ]);

        $this->addElement('text', 'general_match_field', [
            'label'       => $this->translate('Match Field'),
            'description' => $this->translate('Device field to match against Icinga host name (name, dns, ip)'),
            'value'       => 'name',
            'required'    => true
        ]);

        $this->addElement('text', 'mrtg_base_url', [
            'label'       => $this->translate('MRTG Base URL'),
            'description' => $this->translate('Base URL for MRTG HTML pages, e.g. /mrtg. Used to link sparklines to full graphs. Leave empty to disable linking.'),
            'value'       => '',
            'required'    => false
        ]);

        $this->addElement('text', 'mrtg_log_path', [
            'label'       => $this->translate('MRTG Log Path'),
            'description' => $this->translate('Filesystem path to MRTG .log files, e.g. /var/www/mrtg. Sparklines are rendered directly from these files.'),
            'value'       => '',
            'required'    => false
        ]);

        $this->addElement('text', 'mrtg_port_field', [
            'label'       => $this->translate('MRTG Port Field'),
            'description' => $this->translate('Port field used as MRTG index (port, name, descr)'),
            'value'       => 'port',
            'required'    => false
        ]);
    }
}
