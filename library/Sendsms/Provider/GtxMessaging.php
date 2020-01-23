<?php

namespace Icinga\Module\Sendsms\Provider;

use Icinga\Module\Sendsms\Destination;
use Icinga\Module\Sendsms\Hook\ProviderHook;
use Icinga\Module\Sendsms\RestApiClient;
use Icinga\Module\Sendsms\Sender;
use InvalidArgumentException;

class GtxMessaging extends ProviderHook
{
    protected $baseUrl = 'smsc/sendsms'; // /:auth-key[/:format]

    /** @var RestApiClient */
    protected $client;

    public function send(Sender $sender, Destination $destination, $text)
    {
        /**
         * Personal API Key, provided in GTX Dashboard
         *
         * It's a UUID and looks like this: aaaaaaaa-bbbb-cccc-dddd-1234567890ab
         *
         * @var string
         */
        $authKey = $this->getRequiredSetting('auth_key');
        $baseUrl = $this->getSetting('base_url', $this->baseUrl);

        /** @var string $format SMSC response. json, xml, plain(default) */
        $format = 'json';

        $url = \implode('/', [
            $baseUrl,
            \urlencode($authKey),
            \urlencode($format),
        ]);

        echo "$url\n";

        $headers = [];
        switch ($format) {
            case 'plain';
                $headers['Accept'] = 'application/x-www-form-urlencoded';
                $headers['Content-Type'] = 'application/x-www-form-urlencoded';
                throw new InvalidArgumentException(
                    "Format 'plain' hasn't been implemented yet"
                );
                break;
            case 'json';
                // $headers['Accept'] = 'application/json'; -> RestApiClient does this
                $headers['Content-Type'] = 'application/json';
                break;
            default:
                throw new InvalidArgumentException(sprintf(
                    "Format must be 'plain' or 'json', got '%s'",
                    $format
                ));
        }

        $params = [
            // The TPOA / originator of the message. Allowed is alphanumeric up
            // to 11 chars, shortcode, local longcode or international number
            // (E.164, E.212 or E.214)
            'from'    => (string) $sender,
            // The recipient of the message, international format, with leading '+'
            // (E.164, E.212 or E.214)
            'to'      => $destination->getFormatted(Destination::FORMAT_INTERNATIONAL),
            'charset' => 'utf-8',
            'text'    => $text,
        ];

        $urlParams = [];

        foreach ($params as $name => $value) {
            $urlParams[] = \urlencode($name) . '=' . \urlencode($value);
        }
        $url .= '?' . \implode('&', $urlParams);

        // Optional parameters:
        //
        // dlr-mask (Bitmask/Number): Request for delivery reports with the state of
        //      the sent message. The value is a bit mask composed of:
        //          1: Delivered to phone
        //          2: Non-Delivered to Phone
        //         34: Expired
        //         66: Unknown
        //      Example: 3 = DELIVRD, UNDELIV and EXPIRED (EXPIRED -> error in docs?)
        //
        // dlr-url (String): If dlr-mask is given, this mandatory URL will be
        //     fetched by HTTP-GET method. e.g. https://mydomain.com:12345/dlr?
        //
        // udh (String): User Data Header (UDH) part of the message, HEX encoded.
        //     e.g. 0605040B8423F0
        //
        // dcs (Number): Data Coding Scheme (DCS) in HEX. 0 = text msg, 8 = binary
        // mclass (Number): Message Class; accepted values: 0 to 3. 0 = for submit display
        // mwi (Number): Message Waiting Indicator (MWI) sets the MWI bits in DCS
        // coding (Number): Sets the coding scheme bits in DCS field. Accepts 1 to 3
        //     1 = 7-Bit Message, 2 = 8-Bit Message, 3 = UCS-2
        // charset (String): Encoding of the message parameter: 'UTF-16'
        // validity (Number): Validity Period (VP) in minutes: 240
        // validity-time (ISO-8601): 2019-05-01T12:00:00Z
        // deferred (Number): Deferred Delivery Time (DDT) in minutes: 5
        // deferred-time (ISO-8601): Deferred Delivery Date Time: 2019-05-01T12:00:00Z

        // response fields:
        // <message-count>
        //<message-status>
        //<message-id>

        // TODO: Result object?
        return $this->client()->get($url, null, $headers);
    }

    protected function client()
    {
        if ($this->client === null) {
            $this->client = new RestApiClient('rest.gtx-messaging.net');
        }

        return $this->client;
    }
}
