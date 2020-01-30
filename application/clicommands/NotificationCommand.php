<?php

namespace Icinga\Module\Sendsms\Clicommands;

use Icinga\Cli\Command;
use Icinga\Module\Sendsms\Destination;
use Icinga\Module\Sendsms\Hook\ProviderHook;
use Icinga\Module\Sendsms\Sender;

class NotificationCommand extends Command
{
    public function sendAction()
    {
        $provider = $this->requireProvider();
        $sender = new Sender($this->params->get('sender', 'Icinga'));
        $destination = new Destination($this->params->getRequired('to'));
        $type = $this->params->getRequired('type'); // PROBLEM, RECOVERY...
        $host = $this->params->getRequired('host');
        $service = $this->params->get('service');
        $state = $this->params->getRequired('state');
        $output = $this->params->get('output');
        if ($service === null) {
            $msg = \sprintf(
                '%s %s is %s',
                $type,
                $host,
                $state
            );
        } else {
            $msg = \sprintf(
                '%s %s on %s is %s',
                $type,
                $service,
                $host,
                $state
            );
        }

        if (\strlen($output) > 0) {
            $msg .= ": $output";
        }

        $result = $provider->send($sender, $destination, $msg);
        var_dump($result);
    }

    /**
     * @return ProviderHook
     */
    protected function requireProvider()
    {
        $accountName = $this->params->getRequired('account');

        return ProviderHook::instance($accountName, $this->Config('accounts'));
    }
}
