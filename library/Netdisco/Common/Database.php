<?php

namespace Icinga\Module\Netdisco\Common;

use Icinga\Application\Config;
use Icinga\Data\ResourceFactory;
use Icinga\Exception\ConfigurationError;
use ipl\Sql\Config as SqlConfig;
use ipl\Sql\Connection;

trait Database
{
    /** @var Connection|null */
    protected $db;

    /**
     * Get Netdisco database connection
     *
     * @return Connection
     * @throws ConfigurationError
     */
    public function getDb(): Connection
    {
        if ($this->db === null) {
            $config = Config::module('netdisco');
            $resourceName = $config->get('database', 'resource');

            if (empty($resourceName)) {
                throw new ConfigurationError('Netdisco database resource not configured');
            }

            $this->db = new Connection(new SqlConfig(
                ResourceFactory::getResourceConfig($resourceName)
            ));
        }

        return $this->db;
    }

    /**
     * Get match field from configuration
     *
     * @return string
     */
    public function getMatchField(): string
    {
        $config = Config::module('netdisco');
        return strtolower($config->get('general', 'match_field', 'name'));
    }

    /**
     * Get MRTG configuration
     *
     * @return array
     */
    public function getMrtgConfig(): array
    {
        $config = Config::module('netdisco');
        return [
            'base_url'   => $config->get('mrtg', 'base_url', ''),
            'log_path'   => $config->get('mrtg', 'log_path', ''),
            'port_field' => $config->get('mrtg', 'port_field', 'port')
        ];
    }
}
