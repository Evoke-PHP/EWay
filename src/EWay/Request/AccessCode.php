<?php
/**
 * AccessCode
 *
 * @package   EWay\Request
 */

namespace EWay\Request;

use DomainException,
    stdClass;

/**
 * AccessCode
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2015 Paul Young
 * @package EWay\Request
 */
class AccessCode
{
    public static $methods = [
        'ProcessPayment', 'CreateTokenCustomer', 'UpdateTokenCustomer', 'TokenPayment', 'Authorise'
    ];

    public static $transactionTypes = ['Purchase', 'MOTO', 'Recurring'];

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
     * Base for the eway URLs.
     * @var string
     */
    protected $urlBase;

    /**
     * @param string $apiKey
     * @param string $password
     * @param bool   $isSandbox
     */
    public function __construct($apiKey, $password, $isSandbox = false)
    {
        $this->authorization = base64_encode($apiKey . ':' . $password);
        $this->body = new stdClass;
        $this->urlBase = $isSandbox ?
            'https://api.sandbox.ewaypayments.com/' :
            'https://api.ewaypayments.com/';
    }

    /******************/
    /* Public Methods */
    /******************/

    /**
     * Send the request for an access code and return the response.
     *
     * @return \EWay\Response\AccessCode
     */
    public function send()
    {
        $context  = stream_context_create(
            [
                'http' =>
                    [
                        'method'  => 'POST',
                        'header'  =>
                            "Content-Type: application/json\r\n" .
                            "Authorization: Basic " . $this->authorization . "\r\n",
                        'content' => json_encode($this->body),
                        'timeout' => 60
                    ]
            ]
        );

        return new \EWay\Response\AccessCode(
            json_decode(file_get_contents($this->urlBase . 'AccessCodes', false, $context))
        );
    }

    public function setCustomer(Array $customer)
    {
        $this->body->Customer = (object) $customer;
    }

    public function setCustomerIP($ipAddress)
    {
        $this->body->CustomerIP = $ipAddress;
    }

    public function setCheckoutPayment($checkoutURL)
    {
        $this->body->CheckoutPayment = true;
        $this->body->CheckoutUrl = $checkoutURL;
    }

    public function setDeviceID($deviceID)
    {
        $this->body->DeviceID = $deviceID;
    }

    /**
     * The Items section is optional. If provided, it should contain a list of line items purchased by the customer, up
     * to a maximum of 99 items. It is used by Beagle Fraud Alerts (Enterprise) to calculate a risk score for this
     * transaction, and by the Responsive Shared page and PayPal to display the order to the customer.
     *
     * Items have the following properties:
     *
     * - SKU         (O) string The stock keeping unit or name used to identify this line item.
     * - Description (O) string A brief description of the product.
     * - Quantity    (O) int(6) The purchased quantity.
     * - UnitCost    (0) int(8) The pre-tax cost per unit of the product in the lowest denomination e.g. 500 for $5.00.
     * - Tax         (0) int(8) The tax amount that applies to this line item in the lowest denomination.
     * - Total       (0) int(8) The total amount charged for this line item in the lowest denomination.
     *
     * @param $items mixed[] Items for the transaction.
     */
    public function setItems(Array $items)
    {
        $itemsObjects = [];

        foreach ($items as $item) {
            $itemsObjects[] = (object) $item;
        }

        $this->body->Items = $itemsObjects;
    }

    public function setMethod($method)
    {
        if (!in_array($method, self::$methods)) {
            throw new DomainException('Unknown Method: ' . $method);
        }

        $this->body->Method = $method;
    }

    public function setOptions(Array $options)
    {
        $optionsObjects = [];

        foreach ($options as $option) {
            $optionsObjects[] = (object) $option;
        }

        $this->body->Options = $optionsObjects;
    }

    public function setPartnerID($partnerID)
    {
        $this->body->PartnerID = $partnerID;
    }

    /**
     * @param mixed[] $paymentInformation
     * Payment information of the form:
     * <code>
     * // 'TotalAmount' is the only required field.
     * [
     *     'TotalAmount'        => 1234,       // integer for number of cents.
     *     'InvoiceNumber'      => 'I1234',    // string.
     *     'InvoiceDescription' => 'Inv Desc', // string
     *     'InvoiceReference'   => 'IR123',    // string
     *     'CurrencyCode'       => 'AUD'       // ISO 4217 3 char currency code.
     * ]
     * </code>
     */
    public function setPayment(Array $paymentInformation)
    {
        $this->body->Payment = (object) $paymentInformation;
    }

    public function setRedirectURL($url)
    {
        $this->body->RedirectUrl = $url;
    }

    public function setShippingAddress(Array $shippingAddress)
    {
        $this->body->ShippingAddress = (object) $shippingAddress;
    }

    public function setTransactionType($transactionType)
    {
        if (!in_array($transactionType, self::$transactionTypes)) {
            throw new DomainException('Unknown Transaction Type: ' . $transactionType);
        }

        $this->body->TransactionType = $transactionType;
    }
}
// EOF
