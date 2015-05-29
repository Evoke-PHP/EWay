<?php
/**
 * AccessCodeResult
 *
 * @package   EWay\Request
 */

namespace EWay\Request;

use stdClass;
use RuntimeException;

/**
 * AccessCodeResult
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2015 Paul Young
 * @package EWay\Request
 */
class AccessCodeResult extends Request
{
    /**
     * Create an Access Code result request.
     *
     * @param string $apiKey
     * @param string $password
     * @param string $accessCode
     * @param bool   $isSandbox
     * @param int    $timeout
     */
    public function __construct($apiKey, $password, $accessCode, $isSandbox = false, $timeout = 60)
    {
        parent::__construct($apiKey, $password, $isSandbox, $timeout);

        $this->body->AccessCode = $accessCode;
    }

    /******************/
    /* Public Methods */
    /******************/

    /**
     * Send the request.
     *
     * @return stdClass
     * @throws RuntimeException
     */
    public function send()
    {
        $context  = stream_context_create(
            [
                'http' =>
                    [
                        'method'  => 'GET',
                        'header'  =>
                            "Content-Type: application/json\r\n" .
                            "Authorization: Basic " . $this->authorization . "\r\n",
                        'content' => json_encode($this->body),
                        'timeout' => $this->timeout
                    ]
            ]
        );

        $response = file_get_contents($this->urlBase . 'AccessCode/' . $this->body->AccessCode, false, $context);

        if ($response === false) {
            throw new RuntimeException('Unable to get the access code result');
        }

        $decodedResponse = json_decode($response);

        if (!$decodedResponse instanceof stdClass) {
            throw new RuntimeException('Unable to decode the access code result response');
        }

        return $decodedResponse;
    }
}
// EOF
