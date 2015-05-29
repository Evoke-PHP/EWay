<?php
/**
 * AccessCodeTest
 *
 * @package   EWay_Test\Request
 */

namespace EWay_Test\Request;

use DomainException;
use EWay\Request\AccessCode;
use PHPUnit_Framework_TestCase;
use RuntimeException;
use stdClass;

/**
 * AccessCodeTest
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2015 Paul Young
 * @package   EWay_Test\Request
 * @covers    EWay\Request\Request
 * @covers    EWay\Request\AccessCode
 * @requires  extension runkit
 */
class AccessCodeTest extends PHPUnit_Framework_TestCase
{
    /**
     * The filename used in the last mocked file_get_contents call.
     *
     * @var string
     */
    public static $fgcFilename;

    /**
     * The return value for the mocked file_get_contents call.
     *
     * @var mixed
     */
    public static $fgcReturn = '{}';

    /**
     * The options used in the last mocked stream_create_context call.
     *
     * @var mixed[]
     */
    public static $sccOptions;

    /**
     * The return value for the mocked stream_create_context call.
     *
     * @var mixed
     */
    public static $sccReturn;

    /**
     * Errors recorded from a test.
     *
     * @var array
     */
    protected $errors = [];

    /***********/
    /* Fixture */
    /***********/

    public function mockFGC()
    {
        runkit_function_rename('file_get_contents', 'TEST_SAVED_file_get_contents');
        runkit_function_add(
            'file_get_contents',
            '$filename, $use_include_path, $context',
            'EWay_Test\Request\AccessCodeTest::$fgcFilename = $filename;' .
            'return EWay_Test\Request\AccessCodeTest::$fgcReturn;');
    }

    public function mockSCC()
    {
        runkit_function_rename('stream_context_create', 'TEST_SAVED_stream_context_create');
        runkit_function_add(
            'stream_context_create',
            'Array $options = [], Array $params = []',
            'EWay_Test\Request\AccessCodeTest::$sccOptions = $options;' .
            'return EWay_Test\Request\AccessCodeTest::$sccReturn;'
        );
    }

    public function recordErrors($errno, $errstr)
    {
        $this->errors[] = [$errno, $errstr];
    }

    public function tearDown()
    {
        if (function_exists('TEST_SAVED_file_get_contents')) {
            runkit_function_remove('file_get_contents');
            runkit_function_rename('TEST_SAVED_file_get_contents', 'file_get_contents');
        }

        if (function_exists('TEST_SAVED_stream_context_create')) {
            runkit_function_remove('stream_context_create');
            runkit_function_rename('TEST_SAVED_stream_context_create', 'stream_context_create');
        }

        self::$fgcReturn = '{}';
    }

    /*********/
    /* Tests */
    /*********/

    public function testBasic()
    {
        self::$sccReturn = stream_context_create();
        $this->mockFGC();
        $this->mockSCC();
        $obj = new AccessCode('apiKey', 'pass', false, 60);
        $obj->setPayment(['TotalAmount' => 1234]);
        $obj->setRedirectURL('http://eway.com.au');
        $obj->setMethod('ProcessPayment');
        $obj->setTransactionType('Purchase');

        $rawResponse = $obj->send();

        $expectedBody                       = new stdClass;
        $expectedBody->Payment              = new stdClass;
        $expectedBody->Payment->TotalAmount = 1234;
        $expectedBody->RedirectUrl          = 'http://eway.com.au';
        $expectedBody->Method               = 'ProcessPayment';
        $expectedBody->TransactionType      = 'Purchase';

        $expectedOptions =
            [
                'http' =>
                    [
                        'method'  => 'POST',
                        'header'  =>
                            "Content-Type: application/json\r\n" .
                            "Authorization: Basic " . base64_encode('apiKey:pass') . "\r\n",
                        'content' => json_encode($expectedBody),
                        'timeout' => 60
                    ]
            ];

        $this->assertSame($expectedOptions, self::$sccOptions);
        $this->assertEquals(new stdClass, $rawResponse);
    }

    public function testCheckoutPayment()
    {
        self::$sccReturn = stream_context_create();
        $this->mockFGC();
        $this->mockSCC();
        $obj = new AccessCode('apiKey', 'pass', false, 60);
        $obj->setPayment(['TotalAmount' => 1234]);
        $obj->setRedirectURL('http://eway.com.au');
        $obj->setMethod('ProcessPayment');
        $obj->setTransactionType('Purchase');
        $obj->setCheckoutPayment('http://eway.com.au/paypal/landing/page/test');

        $rawResponse = $obj->send();

        $expectedBody                       = new stdClass;
        $expectedBody->Payment              = new stdClass;
        $expectedBody->Payment->TotalAmount = 1234;
        $expectedBody->RedirectUrl          = 'http://eway.com.au';
        $expectedBody->Method               = 'ProcessPayment';
        $expectedBody->TransactionType      = 'Purchase';
        $expectedBody->CheckoutPayment      = true;
        $expectedBody->CheckoutUrl          = 'http://eway.com.au/paypal/landing/page/test';

        $expectedOptions =
            [
                'http' =>
                    [
                        'method'  => 'POST',
                        'header'  =>
                            "Content-Type: application/json\r\n" .
                            "Authorization: Basic " . base64_encode('apiKey:pass') . "\r\n",
                        'content' => json_encode($expectedBody),
                        'timeout' => 60
                    ]
            ];

        $this->assertSame($expectedOptions, self::$sccOptions);
        $this->assertEquals(new stdClass, $rawResponse);
    }

    public function testComplete()
    {
        self::$sccReturn = stream_context_create();
        $this->mockFGC();
        $this->mockSCC();
        $obj = new AccessCode(
            '60CF3Ce97nRS1Z1Wp5m9kMmzHHEh8Rkuj31QCtVxjPWGYA9FymyqsK0Enm1P6mHJf0THbR',
            'API-P4ss');
        $obj->setCustomer(
            [
                "Reference"      => "A12345",
                "Title"          => "Mr.",
                "FirstName"      => "John",
                "LastName"       => "Smith",
                "CompanyName"    => "Demo Shop 123",
                "JobDescription" => "Developer",
                "Street1"        => "Level 5",
                "Street2"        => "369 Queen Street",
                "City"           => "Sydney",
                "State"          => "NSW",
                "PostalCode"     => "2000",
                "Country"        => "au",
                "Phone"          => "09 889 0986",
                "Mobile"         => "09 889 6542",
                "Email"          => "demo@example.org",
                "Url"            => "http://www.ewaypayments.com"
            ]
        );
        $obj->setShippingAddress(
            [
                "ShippingMethod" => "NextDay",
                "FirstName"      => "John",
                "LastName"       => "Smith",
                "Street1"        => "Level 5",
                "Street2"        => "369 Queen Street",
                "City"           => "Sydney",
                "State"          => "NSW",
                "Country"        => "au",
                "PostalCode"     => "2000",
                "Phone"          => "09 889 0986"
            ]
        );
        $obj->setItems(
            [
                [
                    "SKU"         => "12345678901234567890",
                    "Description" => "Item Description 1",
                    "Quantity"    => 1,
                    "UnitCost"    => 400,
                    "Tax"         => 100,
                    "Total"       => 500
                ],
                [
                    "SKU"         => "123456789012",
                    "Description" => "Item Description 2",
                    "Quantity"    => 1,
                    "UnitCost"    => 400,
                    "Tax"         => 100,
                    "Total"       => 500
                ]
            ]
        );
        $obj->setOptions([["Value" => "Option1"], ["Value" => "Option2"]]);
        $obj->setPayment(
            [
                "TotalAmount"        => 1000,
                "InvoiceNumber"      => "Inv 21540",
                "InvoiceDescription" => "Individual Invoice Description",
                "InvoiceReference"   => "513456",
                "CurrencyCode"       => "AUD"
            ]
        );
        $obj->setRedirectURL("http://www.eway.com.au");
        $obj->setMethod("ProcessPayment");
        $obj->setDeviceID("D1234");
        $obj->setCustomerIP("127.0.0.1");
        $obj->setPartnerID("ID");
        $obj->setTransactionType("Purchase");
        $rawResponse = $obj->send();

        $expectedBody = <<<'EOB'
        {
        "Customer": {
           "Reference": "A12345",
           "Title": "Mr.",
           "FirstName": "John",
           "LastName": "Smith",
           "CompanyName": "Demo Shop 123",
           "JobDescription": "Developer",
           "Street1": "Level 5",
           "Street2": "369 Queen Street",
           "City": "Sydney",
           "State": "NSW",
           "PostalCode": "2000",
           "Country": "au",
           "Phone": "09 889 0986",
           "Mobile": "09 889 6542",
           "Email": "demo@example.org",
           "Url": "http://www.ewaypayments.com"
        },
        "ShippingAddress": {
           "ShippingMethod": "NextDay",
           "FirstName": "John",
           "LastName": "Smith",
           "Street1": "Level 5",
           "Street2": "369 Queen Street",
           "City": "Sydney",
           "State": "NSW",
           "Country": "au",
           "PostalCode": "2000",
           "Phone": "09 889 0986"
        },
        "Items": [
         {
           "SKU": "12345678901234567890",
           "Description": "Item Description 1",
           "Quantity": 1,
           "UnitCost": 400,
           "Tax": 100,
           "Total": 500
         },
         {
           "SKU": "123456789012",
           "Description": "Item Description 2",
           "Quantity": 1,
           "UnitCost": 400,
           "Tax": 100,
           "Total": 500
         }
        ],
        "Options": [
         {
           "Value": "Option1"
         },
         {
           "Value": "Option2"
         }
        ],
        "Payment": {
           "TotalAmount": 1000,
           "InvoiceNumber": "Inv 21540",
           "InvoiceDescription": "Individual Invoice Description",
           "InvoiceReference": "513456",
           "CurrencyCode": "AUD"
        },
        "RedirectUrl": "http://www.eway.com.au",
        "Method": "ProcessPayment",
        "DeviceID": "D1234",
        "CustomerIP": "127.0.0.1",
        "PartnerID": "ID",
        "TransactionType": "Purchase"
        }
EOB;

        $expectedOptionsMinusContent =
            [
                'http' =>
                    [
                        'method'  => 'POST',
                        'header'  =>
                            "Content-Type: application/json\r\n" .
                            "Authorization: Basic " . base64_encode(
                                '60CF3Ce97nRS1Z1Wp5m9kMmzHHEh8Rkuj31QCtVxjPWGYA9FymyqsK0Enm1P6mHJf0THbR:API-P4ss'
                            ) . "\r\n",
                        'timeout' => 60
                    ]
            ];

        $this->assertJsonStringEqualsJsonString($expectedBody, self::$sccOptions['http']['content']);

        $sccOptionsMinusContent = self::$sccOptions;
        unset($sccOptionsMinusContent['http']['content']);
        $this->assertEquals($expectedOptionsMinusContent, $sccOptionsMinusContent);
        $this->assertEquals(new stdClass, $rawResponse);

    }

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Unable to get access code
     */
    public function testFailureAPIKeyPassword()
    {
        $obj = new AccessCode('sandbox', 'sandboxpw', true);

        try {
            $this->errors = [];
            set_error_handler([$this, 'recordErrors']);
            $obj->send();
        } catch (RuntimeException $e) {
            $this->assertSame(
                [
                    [
                        E_WARNING,
                        'file_get_contents(https://api.sandbox.ewaypayments.com/AccessCodes): ' .
                        'failed to open stream: HTTP request failed! HTTP/1.1 401 Unauthorized' . "\r\n"
                    ]
                ], $this->errors);
            restore_error_handler();
            throw $e;
        }
    }

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Unable to decode access code response
     */
    public function testFailureGarbageResponse()
    {
        $obj             = new AccessCode('DC', 'DC');
        self::$sccReturn = stream_context_create();
        self::$fgcReturn = 'GARBAGE';
        $this->mockSCC();
        $this->mockFGC();
        $obj->send();
    }

    public function testResponseHasData()
    {
        $obj             = new AccessCode('DC', 'DC');
        self::$fgcReturn = '{"AccessCode": "BLAH"}';
        self::$sccReturn = stream_context_create();
        $this->mockSCC();
        $this->mockFGC();
        $rawResponse = $obj->send();

        $this->assertTrue(
            $rawResponse instanceof stdClass && $rawResponse->AccessCode === 'BLAH',
            'Response can be retrieved with data.'
        );
    }

    public function testSetMethods()
    {
        $obj = new AccessCode('DontCare', 'DontCare');
        $obj->setMethod('ProcessPayment');
        $obj->setMethod('CreateTokenCustomer');
        $obj->setMethod('UpdateTokenCustomer');
        $obj->setMethod('TokenPayment');
        $obj->setMethod('Authorise');

        try {
            $obj->setMethod('UNACCEPTABLE');
        } catch (DomainException $e) {
            $this->assertEquals('Unknown Method: UNACCEPTABLE', $e->getMessage());
        }
    }

    public function testSetTransactionTypes()
    {
        $obj = new AccessCode('DontCare', 'DontCare');
        $obj->setTransactionType('Purchase');
        $obj->setTransactionType('MOTO');
        $obj->setTransactionType('Recurring');

        try {
            $obj->setTransactionType('UNACCEPTABLE');
        } catch (DomainException $e) {
            $this->assertEquals('Unknown Transaction Type: UNACCEPTABLE', $e->getMessage());
        }
    }

    public function testURLLive()
    {
        $this->mockFGC();
        $obj = new AccessCode('liveAPI', 'livePW');
        $obj->send();
        $this->assertSame('https://api.ewaypayments.com/AccessCodes', self::$fgcFilename);
    }

    public function testURLSandbox()
    {
        $this->mockFGC();
        $obj = new AccessCode('api', 'pw', true);
        $obj->send();
        $this->assertSame('https://api.sandbox.ewaypayments.com/AccessCodes', self::$fgcFilename);
    }
}
// EOF
