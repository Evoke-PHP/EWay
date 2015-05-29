<?php
/**
 * AccessCodeResultTest
 *
 * @package   EWay_Test\Request
 */

namespace EWay_Test\Request;

use EWay\Request\AccessCodeResult;
use PHPUnit_Framework_TestCase;
use RuntimeException;
use stdClass;

/**
 * AccessCodeResultTest
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2015 Paul Young
 * @package   EWay_Test\Request
 * @covers    EWay\Request\AccessCodeResult
 * @requires  extension runkit
 */
class AccessCodeResultTest extends PHPUnit_Framework_TestCase
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
            'EWay_Test\Request\AccessCodeResultTest::$fgcFilename = $filename;' .
            'return EWay_Test\Request\AccessCodeResultTest::$fgcReturn;');
    }

    public function mockSCC()
    {
        runkit_function_rename('stream_context_create', 'TEST_SAVED_stream_context_create');
        runkit_function_add(
            'stream_context_create',
            'Array $options = [], Array $params = []',
            'EWay_Test\Request\AccessCodeResultTest::$sccOptions = $options;' .
            'return EWay_Test\Request\AccessCodeResultTest::$sccReturn;'
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
        $accessCode = 'FPOI123hidfsoafpiij3i029';
        $obj = new AccessCodeResult('apiKey', 'pass', $accessCode, false, 60);
        $rawResponse = $obj->send();

        $expectedBody = new stdClass;
        $expectedBody->AccessCode = $accessCode;

        $expectedOptions =
            [
                'http' =>
                    [
                        'method'  => 'GET',
                        'header'  =>
                            "Content-Type: application/json\r\n" .
                            "Authorization: Basic " . base64_encode('apiKey:pass') . "\r\n",
                        'content' => json_encode($expectedBody),
                        'timeout' => 60
                    ]
            ];

        $this->assertSame($expectedOptions, self::$sccOptions);
        $this->assertSame('https://api.ewaypayments.com/AccessCode/' . $accessCode, self::$fgcFilename);
        $this->assertEquals(new stdClass, $rawResponse);
    }

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Unable to get the access code result
     */
    public function testFailureAPIKeyPassword()
    {
        $obj = new AccessCodeResult('sandbox', 'sandboxpw', 'blahACblah', true);

        try {
            $this->errors = [];
            set_error_handler([$this, 'recordErrors']);
            $obj->send();
        } catch (RuntimeException $e) {
            $this->assertSame(
                [
                    [
                        E_WARNING,
                        'file_get_contents(https://api.sandbox.ewaypayments.com/AccessCode/blahACblah): ' .
                        'failed to open stream: HTTP request failed! HTTP/1.1 401 Unauthorized' . "\r\n"
                    ]
                ], $this->errors);
            restore_error_handler();
            throw $e;
        }
    }

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Unable to decode the access code result response
     */
    public function testFailureGarbageResponse()
    {
        $obj             = new AccessCodeResult('DC', 'DC', 'BLAH');
        self::$sccReturn = stream_context_create();
        self::$fgcReturn = 'GARBAGE';
        $this->mockSCC();
        $this->mockFGC();
        $obj->send();
    }

    public function testURLLive()
    {
        $this->mockFGC();
        $obj = new AccessCodeResult('liveAPI', 'livePW', 'ACblah');
        $obj->send();
        $this->assertSame('https://api.ewaypayments.com/AccessCode/ACblah', self::$fgcFilename);
    }

    public function testURLSandbox()
    {
        $this->mockFGC();
        $obj = new AccessCodeResult('api', 'pw', 'ACblahdeblahblahyah', true);
        $obj->send();
        $this->assertSame('https://api.sandbox.ewaypayments.com/AccessCode/ACblahdeblahblahyah', self::$fgcFilename);
    }
}
