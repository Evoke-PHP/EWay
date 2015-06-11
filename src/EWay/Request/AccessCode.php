<?php
/**
 * AccessCode
 *
 * @package   EWay\Request
 */

namespace EWay\Request;

use DomainException;
use RuntimeException;
use stdClass;

/**
 * EWay Access Code Request
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2015 Paul Young
 */
class AccessCode extends Request
{
    /**
     * Methods that can be used for payments.
     *
     * @var array
     */
    public static $methods = [
        'ProcessPayment',
        'CreateTokenCustomer',
        'UpdateTokenCustomer',
        'TokenPayment',
        'Authorise'
    ];

    /**
     * Transaction types.
     *
     * @var array
     */
    public static $transactionTypes = ['Purchase', 'MOTO', 'Recurring'];

    /******************/
    /* Public Methods */
    /******************/

    /**
     * Send the request for an access code and return the response.
     *
     * @return stdClass
     * @throws RuntimeException
     */
    public function send()
    {
        $context = stream_context_create(
            [
                'http' =>
                    [
                        'method'  => 'POST',
                        'header'  =>
                            "Content-Type: application/json\r\n" .
                            "Authorization: Basic " . $this->authorization . "\r\n",
                        'content' => json_encode($this->body),
                        'timeout' => $this->timeout
                    ]
            ]
        );

        $response = file_get_contents($this->urlBase . 'AccessCodes', false, $context);

        if ($response === false) {
            throw new RuntimeException('Unable to get access code');
        }

        $decodedResponse = json_decode($response);

        if (!$decodedResponse instanceof stdClass) {
            throw new RuntimeException('Unable to decode access code response');
        }

        return $decodedResponse;
    }

    /**
     * Set the customer.
     *
     * @param array $customer
     */
    public function setCustomer(Array $customer)
    {
        $this->body->Customer = (object)$customer;
    }

    /**
     * Set the customer IP address.
     *
     * @param string $ipAddress
     */
    public function setCustomerIP($ipAddress)
    {
        $this->body->CustomerIP = $ipAddress;
    }

    /**
     * Set the Checkout Payment to use the paypal checkout.
     *
     * @param string $checkoutURL The URL to return to after the payment is verified.
     */
    public function setCheckoutPayment($checkoutURL)
    {
        $this->body->CheckoutPayment = true;
        $this->body->CheckoutUrl     = $checkoutURL;
    }

    /**
     * Set the device ID.
     *
     * @param string $deviceID
     */
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
            $itemsObjects[] = (object)$item;
        }

        $this->body->Items = $itemsObjects;
    }

    /**
     * Set the payment method.
     *
     * @param $method
     */
    public function setMethod($method)
    {
        if (!in_array($method, self::$methods)) {
            throw new DomainException('Unknown Method: ' . $method);
        }

        $this->body->Method = $method;
    }

    /**
     * Set any options.
     *
     * @param array $options
     */
    public function setOptions(Array $options)
    {
        $optionsObjects = [];

        foreach ($options as $option) {
            $optionsObjects[] = (object)$option;
        }

        $this->body->Options = $optionsObjects;
    }

    /**
     * Set the partner ID.
     *
     * @param $partnerID
     */
    public function setPartnerID($partnerID)
    {
        $this->body->PartnerID = $partnerID;
    }

    /**
     * Set the payment information.
     *
     * @param mixed[] $paymentInformation
     *     Payment information of the form:
     *     <code>
     *     // 'TotalAmount' is the only required field.
     *     [
     *     'TotalAmount'        => 1234,       // integer for number of cents.
     *     'InvoiceNumber'      => 'I1234',    // string.
     *     'InvoiceDescription' => 'Inv Desc', // string
     *     'InvoiceReference'   => 'IR123',    // string
     *     'CurrencyCode'       => 'AUD'       // ISO 4217 3 char currency code.
     *     ]
     *     </code>
     */
    public function setPayment(Array $paymentInformation)
    {
        $this->body->Payment = (object)$paymentInformation;
    }

    /**
     * Set the url to redirect to after the payment has been processed.
     *
     * @param $url
     */
    public function setRedirectURL($url)
    {
        $this->body->RedirectUrl = $url;
    }

    /**
     * Set the shipping address.
     *
     * @param array $shippingAddress
     */
    public function setShippingAddress(Array $shippingAddress)
    {
        $this->body->ShippingAddress = (object)$shippingAddress;
    }

    /**
     * Set the transaction type.
     *
     * @param $transactionType
     */
    public function setTransactionType($transactionType)
    {
        if (!in_array($transactionType, self::$transactionTypes)) {
            throw new DomainException('Unknown Transaction Type: ' . $transactionType);
        }

        $this->body->TransactionType = $transactionType;
    }
}
// EOF
