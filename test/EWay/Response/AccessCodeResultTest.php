<?php
/**
 * AccessCodeResultTest
 *
 * @package   EWay_Test\Response
 */

namespace EWay_Test\Response;

use EWay\Response\AccessCodeResult;
use PHPUnit_Framework_AssertionFailedError;
use PHPUnit_Framework_TestCase;
use RuntimeException;
use stdClass;

/**
 * AccessCodeResultTest
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2015 Paul Young
 * @covers    EWay\Response\AccessCodeResult
 * @covers    EWay\Response\Response
 * @package   EWay_Test\Response
 */
class AccessCodeResultTest extends PHPUnit_Framework_TestCase
{
    public function providerGetMethodDoesNotContainOnEmpty()
    {
        return
            [
                'Bank Authorisation Code' => ['BankAuthCode', 'AuthorisationCode'],
                'Bank Response Code'      => ['BankResponseCode', 'ResponseCode'],
                'Bank Response Message'   => ['BankResponseMessage', 'ResponseMessage'],
                'Merchant Invoice Number' => ['InvoiceNumber', 'InvoiceNumber'],
                'Merchant Invoice Ref'    => ['InvoiceReference', 'InvoiceReference'],
                'Total Amount Authorised' => ['TotalAmountAuth', 'TotalAmount'],
                'EWay Transaction ID'     => ['TransactionID', 'TransactionID'],
                'Transaction Status'      => ['TransactionStatus', 'TransactionStatus']
            ];
    }

    public function providerGetMethodReturnsField()
    {
        return
            [
                'Bank Authorisation Code' =>
                    [
                        'Response' => (object)['AuthorisationCode' => '00'],
                        'Method'   => 'BankAuthCode',
                        'Expected' => '00'
                    ],
                'Bank Response Code'      =>
                    [
                        'Response' => (object)['ResponseCode' => '77'],
                        'Method'   => 'BankResponseCode',
                        'Expected' => '77'
                    ],
                'Bank Response Message'   =>
                    [
                        'Response' => (object)['ResponseMessage' => 'blah'],
                        'Method'   => 'BankResponseMessage',
                        'Expected' => 'blah'
                    ],
                'Merchant Invoice Number' =>
                    [
                        'Response' => (object)['InvoiceNumber' => 'INV01234'],
                        'Method'   => 'InvoiceNumber',
                        'Expected' => 'INV01234'
                    ],
                'Merchant Invoice Ref'    =>
                    [
                        'Response' => (object)['InvoiceReference' => 'blah'],
                        'Method'   => 'InvoiceReference',
                        'Expected' => 'blah'
                    ],
                'Total Amount Authorised' =>
                    [
                        'Response' => (object)['TotalAmount' => '3400'],
                        'Method'   => 'TotalAmountAuth',
                        'Expected' => '3400'
                    ],
                'EWay Transaction ID'     =>
                    [
                        'Response' => (object)['TransactionID' => 'ID98765'],
                        'Method'   => 'TransactionID',
                        'Expected' => 'ID98765'
                    ],
                'Transaction Status'      =>
                    [
                        'Response' => (object)['TransactionStatus' => 'Status OK'],
                        'Method'   => 'TransactionStatus',
                        'Expected' => 'Status OK'
                    ]
            ];
    }

    public function providerHasBankSuccess()
    {
        return
            [
                'Approved_00'          =>
                    [
                        'Response' => (object)['ResponseCode' => '00'],
                        'Expected' => true
                    ],
                'Approved_08'          =>
                    [
                        'Response' => (object)['ResponseCode' => '08'],
                        'Expected' => true
                    ],
                'Failure_01'           =>
                    [
                        'Response' => (object)['ResponseCode' => '01'],
                        'Expected' => false
                    ],
                'Failure_Non_Existent' =>
                    [
                        'Response' => new stdClass,
                        'Expected' => false
                    ]
            ];
    }

    /*********/
    /* Tests */
    /*********/

    /**
     * @dataProvider providerGetMethodDoesNotContainOnEmpty
     */
    public function testGetMethodDoesNotContainOnEmpty($method, $field)
    {
        try {
            $obj       = new AccessCodeResult(new stdClass);
            $getMethod = 'get' . $method;
            $obj->$getMethod();

            $this->fail('Get method should raise exception on the get of empty response.');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            // Catch the fail and rethrow so that it is not caught as a RuntimeException below.
            throw $e;
        } catch (RuntimeException $e) {
            $this->assertSame('Response does not contain ' . $field, $e->getMessage());
        }
    }

    /**
     * @dataProvider providerGetMethodReturnsField
     */
    public function testGetMethodReturnsField(stdClass $response, $method, $expected)
    {
        $obj       = new AccessCodeResult($response);
        $getMethod = 'get' . $method;
        $this->assertSame($expected, $obj->$getMethod());
    }

    /**
     * @dataProvider providerHasBankSuccess
     */
    public function testHasBankSuccess(stdClass $response, $expected)
    {
        $obj = new AccessCodeResult($response);
        $this->assertSame($expected, $obj->hasBankSuccess());
    }
}
// EOF