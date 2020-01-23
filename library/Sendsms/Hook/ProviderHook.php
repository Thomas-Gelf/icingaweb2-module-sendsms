<?php

namespace Icinga\Module\Sendsms\Hook;

use Exception;
use Icinga\Application\Config;
use Icinga\Application\Hook;
use Icinga\Data\ConfigObject;
use Icinga\Module\Sendsms\Destination;
use Icinga\Module\Sendsms\Provider\GtxMessaging;
use Icinga\Module\Sendsms\Sender;
use InvalidArgumentException;

abstract class ProviderHook
{
    /** @var ConfigObject */
    private $config;

    /** @var string */
    private $accountName;

    public function setProviderConfig($accountName, ConfigObject $config)
    {
        $this->accountName = $accountName;
        $this->config = $config;
    }

    public function getAccountName()
    {
        return $this->accountName;
    }

    public function getSetting($name, $default = null)
    {
        return $this->config->get($name, $default);
    }

    public function getRequiredSetting($name)
    {
        $value = $this->config->get($name);
        if ($value === null) {
            throw new InvalidArgumentException(sprintf(
                'SMS provider configuration requires the "%s" setting',
                $name
            ));
        } else {
            return $value;
        }
    }

    /**
     * @param $accountName
     * @param Config $accountsConfig
     * @return ProviderHook
     */
    public static function instance($accountName, Config $accountsConfig)
    {
        if (! $accountsConfig->hasSection($accountName)) {
            throw new InvalidArgumentException(sprintf(
                'There is no section for "%s" in "%s"',
                $accountName,
                $accountsConfig->getConfigFile()
            ));
        }

        $config = $accountsConfig->getSection($accountName);
        $implementation = $config->get('provider');
        $implementation = GtxMessaging::class;

        try {
            /** @var ProviderHook $instance */
            $instance = Hook::createInstance('sendsms/Provider', $implementation);
        } catch (Exception $e) {
            throw new InvalidArgumentException(sprintf(
                'Could not load the "%s" SMS provider implementation: %s',
                $implementation,
                $e->getMessage()
            ));
        }

        $instance->setProviderConfig($accountName, $config);

        return $instance;
    }

    abstract public function send(Sender $sender, Destination $destination, $text);
}
