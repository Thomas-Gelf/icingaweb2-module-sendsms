<?php

namespace Icinga\Module\Sendsms;

class Destination
{
    const FORMAT_INTERNATIONAL = 'international';

    protected $rawDestination;

    public function __construct($rawDestination)
    {
        $this->rawDestination = $rawDestination;
    }

    public function getFormatted($format = self::FORMAT_INTERNATIONAL)
    {
        return $this->getRaw();
    }

    public function getRaw()
    {
        return $this->rawDestination;
    }
}
