<?php

namespace Icinga\Module\Sendsms;

class Json
{
    /**
     * @param $mixed
     * @param int|null $flags
     * @return string
     * @throws JsonException
     */
    public static function encode($mixed, $flags = null)
    {
        $result = \json_encode($mixed, $flags);

        if ($result === false && json_last_error() !== JSON_ERROR_NONE) {
            throw JsonException::forLastJsonError();
        }

        return $result;
    }

    /**
     * @param $string
     * @return mixed
     * @throws JsonException
     */
    public static function decode($string)
    {
        $result = \json_decode($string);

        if ($result === null && json_last_error() !== JSON_ERROR_NONE) {
            throw JsonException::forLastJsonError();
        }

        return $result;
    }
}
