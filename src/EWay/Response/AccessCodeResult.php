<?php
/**
 * AccessCodeResult
 *
 * @package   EWay\Response
 */

namespace EWay\Response;

/**
 * AccessCodeResult
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2015 Paul Young
 * @package EWay\Response
 */
class AccessCodeResult extends Response
{
    /**
     * Bank success codes (anything else is failure).
     * @var string[]
     */
    protected static $bankSuccessCodes = [
        '00', // Success
        '08', // Honour with identification (Transaction approved by Signature)
        // The following possible success codes are left out due to ambiguity between the banks.
        // '10', '11', '16', '76', '77'
    ];

    /******************/
    /* Public Methods */
    /******************/

    /**
     * Return the bank authorisation code.
     * @return string
     */
    public function getBankAuthCode()
    {
        return $this->get('AuthorisationCode');
    }

    /**
     * Return the two digit bank response code.
     * @return string
     */
    public function getBankResponseCode()
    {
        return $this->get('ResponseCode');
    }


    /**
     * Get the bank response message as decoded by the error status codes.
     * @return string[]
     */
    public function getBankResponseMessage()
    {
        return $this->get('ResponseMessage');
    }

    /**
     * Get an echo of the merchant's invoice number (a 64 character string).
     * @return string
     */
    public function getInvoiceNumber()
    {
        return $this->get('InvoiceNumber');
    }

    /**
     * Get an echo of the merchant's invoice reference (a 64 character string).
     * @return string
     */
    public function getInvoiceReference()
    {
        return $this->get('InvoiceReference');
    }

    /**
     * Get the amount that was authorised for this transaction in the lowest common denominator (cents).
     * @return int
    */
    public function getTotalAmountAuth()
    {
        return $this->get('TotalAmount');
    }

    /**
     * Get the unique identifier that represents the transaction in eWAY’s system.
     * @return int
     */
    public function getTransactionID()
    {
        return $this->get('TransactionID');
    }

    /**
     * Get whether the transaction was successful or not.
     * @return bool
     */
    public function getTransactionStatus()
    {
        return $this->get('TransactionStatus');
    }

    /**
     * Whether the bank has returned a success code for the transaction.
     *
     * @return bool
     */
    public function hasBankSuccess()
    {
        return isset($this->response->ResponseCode) && in_array($this->response->ResponseCode, self::$bankSuccessCodes);
    }
}
// EOF
