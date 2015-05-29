<?php
/**
 * Request
 *
 * @package   EWay\Request
 */

namespace EWay\Request;

use stdClass;
use RuntimeException;

/**
 * Request
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2015 Paul Young
 * @package EWay\Request
 */
abstract class Request
{
    /**
     * The authorization user(apiKey) and password to gain access.
     * @var string
     */
    protected $authorization;

    /**
     * The body of the request that we are sending.
     * @var stdClass
     */
    protected $body;

    /**
     * The timeout for the request.
     * @var int
     */
    protected $timeout;

    /**
     * Base for the eway URLs.
     * @var string
     */
    protected $urlBase;

    /**
     * Create an EWay request.
     *
     * @param string $apiKey
     * @param string $password
     * @param bool   $isSandbox
     * @param int    $timeout
     */
    public function __construct($apiKey, $password, $isSandbox = false, $timeout = 60)
    {
        $this->authorization = base64_encode($apiKey . ':' . $password);
        $this->body          = new stdClass;
        $this->timeout       = $timeout;
        $this->urlBase       = $isSandbox ?
            'https://api.sandbox.ewaypayments.com/' :
            'https://api.ewaypayments.com/';
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
    abstract public function send();
}
// EOF
