<?php

namespace Icinga\Module\Sendsms;

class Sender
{
    /** @var string */
    protected $sender;

    /**
     * @param string $sender
     */
    public function __construct($sender)
    {
        $this->sender = $sender;
    }

    public function __toString()
    {
        return $this->sender;
    }
}
