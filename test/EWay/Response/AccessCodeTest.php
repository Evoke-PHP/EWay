<?php
/**
 * AccessCodeTest
 *
 * @package   EWay_Test\Response
 */

namespace EWay_Test\Response;

use EWay\Response\AccessCode;
use PHPUnit_Framework_TestCase;
use RuntimeException;
use stdClass;

/**
 * AccessCodeTest
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2015 Paul Young
 * @covers    EWay\Response\AccessCode
 * @covers    EWay\Response\Response
 * @package   EWay_Test\Response
 */
class AccessCodeTest extends PHPUnit_Framework_TestCase
{
    /******************/
    /* Data Providers */
    /******************/

    public function providerGetErrors()
    {
        return
            [
                'All_Good'           =>
                    [
                        'Expected' =>
                            [
                                'A2000' => 'Transaction Approved',
                                'A2008' => 'Honour With Identification',
                                'A2010' => 'Approved For Partial Amount',
                                'A2011' => 'Approved VIP',
                                'A2016' => 'Approved Update Track 3'
                            ],
                        'Response' => (object)['Errors' => 'A2000,A2008,A2010,A2011,A2016']
                    ],
                'None'               =>
                    [
                        'Expected' => [],
                        'Response' => (object)[]
                    ],
                'Multiple'           =>
                    [
                        'Expected' =>
                            [
                                'V6000' => 'Validation error',
                                'V6001' => 'Invalid CustomerIP',
                                'V6002' => 'Invalid DeviceID'
                            ],
                        'Response' => (object)['Errors' => 'V6000,V6001,V6002']
                    ],
                'Single'             =>
                    [
                        'Expected' => ['V6000' => 'Validation error'],
                        'Response' => (object)['Errors' => 'V6000']
                    ],
                'Single_Good'        =>
                    [
                        'Expected' => ['A2000' => 'Transaction Approved'],
                        'Response' => (object)['Errors' => 'A2000']
                    ],
                'Some_Good_Some_Bad' =>
                    [
                        'Expected' =>
                            [
                                'A2000' => 'Transaction Approved',
                                'F7008' => 'Risk Score Fraud'
                            ],
                        'Response' => (object)['Errors' => 'A2000,F7008']
                    ],
                'Unknown_Error_Code' =>
                    [
                        'Expected' => ['U9999' => 'Unknown Error'],
                        'Response' => (object)['Errors' => 'U9999']
                    ]
            ];
    }

    public function providerHasErrors()
    {
        return
            [
                'All_Good'           =>
                    [
                        'Expected' => false,
                        'Response' => (object)['Errors' => 'A2000,A2008,A2010,A2011,A2016']
                    ],
                'None'               =>
                    [
                        'Expected' => false,
                        'Response' => (object)[]
                    ],
                'Multiple'           =>
                    [
                        'Expected' => true,
                        'Response' => (object)['Errors' => 'V6000,V6001,V6002']
                    ],
                'Single'             =>
                    [
                        'Expected' => true,
                        'Response' => (object)['Errors' => 'V6000']
                    ],
                'Single_Good'        =>
                    [
                        'Expected' => false,
                        'Response' => (object)['Errors' => 'A2000']
                    ],
                'Some_Good_Some_Bad' =>
                    [
                        'Expected' => true,
                        'Response' => (object)['Errors' => 'A2000,F7008']
                    ],
                'Unknown_Error_Code' =>
                    [
                        'Expected' => true,
                        'Response' => (object)['Errors' => 'U9999']
                    ]
            ];
    }

    /**************/
    /* Test Cases */
    /**************/

    public function testGetAccessCode()
    {
        $obj = new AccessCode((object)['AccessCode' => 'BLAH']);
        $this->assertEquals('BLAH', $obj->getAccessCode());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Response does not contain AccessCode
     */
    public function testGetAccessCodeBad()
    {
        $obj = new AccessCode(new stdClass);
        $obj->getAccessCode();
    }

    /**
     * @dataProvider providerGetErrors
     * @param bool     $expected
     * @param stdClass $response
     */
    public function testGetErrors($expected, stdClass $response)
    {
        $obj = new AccessCode($response);
        $this->assertEquals($expected, $obj->getErrors());
    }

    public function testGetFormActionURL()
    {
        $obj = new AccessCode((object)['FormActionURL' => 'FAU']);
        $this->assertEquals('FAU', $obj->getFormActionURL());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Response does not contain FormActionURL
     */
    public function testGetFormActionURLBad()
    {
        $obj = new AccessCode(new stdClass);
        $obj->getFormActionURL();
    }

    /**
     * @dataProvider providerHasErrors
     * @param        bool     $expected
     * @param        stdClass $response
     */
    public function testHasErrors($expected, stdClass $response)
    {
        $obj = new AccessCode($response);
        $this->assertEquals($expected, $obj->hasErrors());
    }
}
//EOF
