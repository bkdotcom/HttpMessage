<?php

namespace bdk\Test\HttpMessage;

use bdk\HttpMessage\Message;
use bdk\HttpMessage\Request;
use bdk\HttpMessage\ServerRequest;
use bdk\HttpMessage\Utility\ContentType;
use bdk\HttpMessage\Utility\ParseStr;
use ReflectionObject;

/**
 * @covers \bdk\HttpMessage\AssertionTrait
 * @covers \bdk\HttpMessage\ServerRequest
 * @covers \bdk\HttpMessage\Utility\ParseStr
 */
class ServerRequestTest extends TestCase
{
    public function testConstruct()
    {
        $serverRequest = $this->createServerRequest();
        $this->assertTrue($serverRequest instanceof Message);
        $this->assertTrue($serverRequest instanceof Request);
        $this->assertTrue($serverRequest instanceof ServerRequest);
    }

    public function testAuthHeaders()
    {
        $serverRequest = $this->createServerRequest('GET', 'http://www.test.com/', array(
            'REDIRECT_HTTP_AUTHORIZATION' => 'Basic ' . \base64_encode('username:password'),
        ));
        $this->assertSame(array(
            'Host' => array('www.test.com'),
            'Authorization' => array('Basic ' . \base64_encode('username:password')),
        ), $serverRequest->getHeaders());

        $digestVal = 'Digest username="Mufasa", realm="testrealm@host.com", nonce="dcd98b7102dd2f0e8b11d0f600bfb0c093", uri="/dir/index.html", qop=auth, nc=00000001, cnonce="0a4f113b", response="6629fae49393a05397450978507c4ef1", opaque="5ccc069c403ebaf9f0171e9517f40e41';
        $serverRequest = $this->createServerRequest('GET', 'http://www.test.com/', array(
            'PHP_AUTH_DIGEST' => $digestVal,
        ));
        $this->assertSame(array(
            'Host' => array('www.test.com'),
            'Authorization' => array($digestVal),
        ), $serverRequest->getHeaders());
    }

    public function testConstructWithUri()
    {
        $serverRequest = $this->createServerRequest(
            'GET',
            '/some/page?foo=bar&dingle.berry=brown&a%20b=c&d+e=f&g h=i',
            array(
                'SERVER_PROTOCOL' => 'HTTP/1.0',
                'CONTENT_TYPE' => 'text/html',
            )
        );
        $this->assertSame(array(
            'foo' => 'bar',
            'dingle.berry' => 'brown',
            'a b' => 'c',
            'd e' => 'f',
            'g h' => 'i',
        ), $serverRequest->getQueryParams());
        $this->assertSame('1.0', $serverRequest->getProtocolVersion());
        $this->assertSame('text/html', $serverRequest->getHeaderLine('Content-Type'));

        ParseStr::setOpts('convDot', true);
        ParseStr::setOpts('convSpace', false);
        $serverRequest = $this->createServerRequest(
            'GET',
            '/some/page?foo=bar&dingle.berry=brown&a%20b=c&d+e=f&g h=i'
        );
        $this->assertSame(array(
            'foo' => 'bar',
            'dingle_berry' => 'brown',
            'a b' => 'c',
            'd e' => 'f',
            'g h' => 'i',
        ), $serverRequest->getQueryParams());

        ParseStr::setOpts('convDot', false);
        ParseStr::setOpts('convSpace', true);
        $serverRequest = $this->createServerRequest(
            'GET',
            '/some/page?foo=bar&dingle.berry=brown&a%20b=c&d+e=f&g h=i'
        );
        $this->assertSame(array(
            'foo' => 'bar',
            'dingle.berry' => 'brown',
            'a_b' => 'c',
            'd_e' => 'f',
            'g_h' => 'i',
        ), $serverRequest->getQueryParams());

        ParseStr::setOpts('convSpace', false);
        ParseStr::setOpts('convDot', false);

        /*
            Test new values replace
        */
        $serverRequest = $serverRequest->withQueryParams(array(
            'new' => 'new',
        ));
        $this->assertSame(array(
            'new' => 'new',
        ), $serverRequest->getQueryParams());
    }

    public function testProperties()
    {
        $serverRequest = $this->createServerRequest();

        $properties = array(
            'attributes' => array(),
            'cookie' => array(),
            'parsedBody' => null,
            'queryParams' => null,
            'server' => array(
                'REQUEST_METHOD' => 'GET',
            ),
            'files' => array(),
        );

        $reflection = new ReflectionObject($serverRequest);

        foreach ($properties as $k => $vExpect) {
            $prop = $reflection->getProperty($k);
            $prop->setAccessible(true);
            $this->assertSame($vExpect, $prop->getValue($serverRequest), $k);
            unset($prop);
        }
    }

    public function testGetMethods()
    {
        // Test 1
        $serverRequest = $this->createServerRequest();
        $this->assertSame('GET', $serverRequest->getMethod());
        $this->assertSame([
            'REQUEST_METHOD' => 'GET',
        ], $serverRequest->getServerParams());
        $this->assertSame([], $serverRequest->getCookieParams());
        $this->assertSame(null, $serverRequest->getParsedBody());
        $this->assertSame([], $serverRequest->getQueryParams());
        $this->assertSame([], $serverRequest->getUploadedFiles());
        $this->assertSame([], $serverRequest->getAttributes());

        // Test 2
        $serverRequest = $this->createServerRequest('POST', '', array('what' => 'server'))
            ->withCookieParams(array('what' => 'cookie'))
            ->withParsedBody(array('what' => 'post'))
            ->withQueryParams(array('what' => 'get'))
            ->withAttribute('what', 'attribute')
            ->withUploadedFiles(self::mockFiles(1));

        $this->assertSame('POST', $serverRequest->getMethod());
        $this->assertEquals(array(
            'what' => 'server',
            'REQUEST_METHOD' => 'POST',
        ), $serverRequest->getServerParams());
        $this->assertEquals(array('what' => 'cookie'), $serverRequest->getCookieParams());
        $this->assertEquals(array('what' => 'post'), $serverRequest->getParsedBody());
        $this->assertEquals(array('what' => 'get'), $serverRequest->getQueryParams());
        $this->assertEquals(array('what' => 'attribute'), $serverRequest->getAttributes());
        $this->assertEquals(
            array(
                'file1' => $this->createUploadedFile(
                    '/tmp/php1234.tmp',
                    100000,
                    UPLOAD_ERR_OK,
                    'file1.jpg',
                    'image/jpeg'
                ),
            ),
            $serverRequest->getUploadedFiles()
        );
    }

    public function testWithBodyClearsParsedBody()
    {
        $serverRequest = $this->createServerRequest('POST', '', array(
            'CONTENT_TYPE' => ContentType::JSON,
        ))->withBody(
            $this->createStream(\json_encode(array('foo' => 'bar')))
        );

        $this->assertSame(array('foo' => 'bar'), $serverRequest->getParsedBody());

        $serverRequest = $serverRequest->withBody(
            $this->createStream(\json_encode(array('ding' => 'dong')))
        );

        $this->assertSame(array('ding' => 'dong'), $serverRequest->getParsedBody());
    }

    public function testWithBodyDoesNotClearParsedBody()
    {
        $serverRequest = $this->createServerRequest('POST', '', array(
            'CONTENT_TYPE' => ContentType::JSON,
        ))->withBody(
            $this->createStream(\json_encode(array('foo' => 'bar')))
        )->withParsedBody(array(
            'snap' => 'crackle',
        ));

        // we explicitly set the parsed body - ignore the body
        $this->assertSame(array('snap' => 'crackle'), $serverRequest->getParsedBody());

        // updating the body does nothing.... use the explicit parsed body
        $serverRequest = $serverRequest->withBody(
            $this->createStream(\json_encode(array('ding' => 'dong')))
        );
        $this->assertSame(array('snap' => 'crackle'), $serverRequest->getParsedBody());

        // withParsedBody(null) -> we will attempt to parsed the body
        $serverRequest = $serverRequest->withParsedBody(null);
        $this->assertSame(array('ding' => 'dong'), $serverRequest->getParsedBody());
    }


    /**
     * @param string       $contentType Request content type
     * @param string       $body        Request body
     * @param array|object $expect      Expected parsed body
     *
     * @dataProvider parsedBodyProvider
     */
    public function testParsedBody($contentType, $body, $expect)
    {
        $serverRequest = $this->createServerRequest('POST', '', array(
            'CONTENT_TYPE' => $contentType,
        ));
        $serverRequest = $serverRequest->withBody($this->createStream($body));
        $parsedBody = $serverRequest->getParsedBody();
        if (\is_object($expect) && \is_object($parsedBody)) {
            $expect = (array) $expect;
            $parsedBody = (array) $parsedBody;
        }
        $this->assertSame($expect, $parsedBody);
    }

    public function testWithMethods()
    {
        $new = $this->createServerRequest()
            ->withCookieParams(['foo3' => 'bar3'])
            ->withParsedBody(['foo4' => 'bar4', 'foo5' => 'bar5'])
            ->withQueryParams(['foo6' => 'bar6', 'foo7' => 'bar7'])
            ->withAttribute('foo8', 'bar9')
            ->withUploadedFiles(self::mockFiles(2));

        $this->assertSame('GET', $new->getMethod());
        $this->assertEquals([
            'REQUEST_METHOD' => 'GET',
        ], $new->getServerParams());
        $this->assertEquals(['foo3' => 'bar3'], $new->getCookieParams());
        $this->assertEquals(['foo4' => 'bar4', 'foo5' => 'bar5'], $new->getParsedBody());
        $this->assertEquals(['foo6' => 'bar6', 'foo7' => 'bar7'], $new->getQueryParams());
        $this->assertEquals('bar9', $new->getAttribute('foo8'));

        $this->assertEquals(
            array(
                'file2' => $this->createUploadedFile(
                    '/tmp/php1235',
                    123456,
                    UPLOAD_ERR_OK,
                    'file2.png',
                    'image/png'
                ),
            ),
            $new->getUploadedFiles()
        );

        $new2 = $new->withoutAttribute('foo8')
            ->withoutAttribute(false);

        $this->assertEquals(null, $new2->getAttribute('foo8'));
        $this->assertSame($new2, $new2->withoutAttribute('noSuch'));
        $this->assertSame($new2, $new2->withoutAttribute(false));
    }

    /**
     * @param $value
     *
     * @dataProvider queryParamsValid
     */
    public function testWithQueryParamsAcceptsValidValues($value)
    {
        $params = $value;
        if (\is_string($params)) {
            \parse_str($value, $params);
        }
        $request = $this->createServerRequest()
            ->withQueryParams($params);
        $this->assertSame($params, $request->getQueryParams());
    }

    /**
     * @param $value
     *
     * @dataProvider queryParamsInvalid
     */
    public function testWithQueryParamsRejectsInvalidValues($value, $message = null)
    {
        $exceptionClass = \is_array($value)
            ? 'InvalidArgumentException'
            : (PHP_VERSION_ID >= 70000
                ? 'TypeError'
                : 'RuntimeException');
        $this->expectException($exceptionClass);
        if ($message) {
            $this->expectExceptionMessage($message);
        }
        $this->createServerRequest()
            ->withQueryParams($value);
    }

    /**
     * @param $value
     *
     * @dataProvider cookieParamsValid
     */
    public function testWithCookieParamsAcceptsValidValues($value)
    {
        $request = $this->createServerRequest()
            ->withCookieParams($value);
        $this->assertSame($value, $request->getCookieParams());
    }

    /**
     * @param $value
     *
     * @dataProvider cookieParamsInvalid
     */
    public function testWithCookieParamsRejectsInvalidValues($value)
    {
        $exceptionClass = \is_array($value)
            ? 'InvalidArgumentException'
            : (PHP_VERSION_ID >= 70000
                ? 'TypeError'
                : 'RuntimeException');
        $this->expectException($exceptionClass);
        $this->createServerRequest()
            ->withCookieParams($value);
    }

    /**
     * @param $name
     * @param $value
     *
     * @dataProvider attributeNamesAndValuesValid
     */
    public function testWithAttributeAcceptsValidNamesAndValues($name, $value)
    {
        $request = $this->createServerRequest()
            ->withAttribute($name, $value);
        $this->assertSame($value, $request->getAttribute($name));
    }

    /**
     * @param $name
     * @param $value
     *
     * @dataProvider attributeNamesAndValuesInvalid
     */
    public function testWithAttributeRejectsInvalidValues($name, $value)
    {
        self::assertExceptionOrTypeError(function () use ($name, $value) {
            $this->createServerRequest()
                ->withAttribute($name, $value);
        });
    }

    /*
        Exceptions
    */

    public function testExceptionUploadedFilesArray()
    {
        // $value = (object) [];
        $value = 'not array';
        $exceptionClass = \is_array($value)
            ? 'InvalidArgumentException'
            : (PHP_VERSION_ID >= 70000
                ? 'TypeError'
                : 'RuntimeException');
        $this->expectException($exceptionClass);
        $this->createServerRequest()
            ->withUploadedFiles($value);
    }

    public function testExceptionUploadedFiles()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid file in uploaded files structure. Expected UploadedFileInterface, string provided');
        $this->createServerRequest()
            ->withUploadedFiles([
                [
                    ['files' => ''],
                ],
            ]);
    }

    public function testExceptionParsedBody()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('ParsedBody must be array, object, or null. string provided.');

        $serverRequest = $this->createServerRequest()
            ->withParsedBody('I am a string');
    }

    /**
     * @dataProvider providerParseStrOpts
     */
    public function testExceptionParseStrOpts($val, $message)
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage($message);
        ParseStr::setOpts($val);
    }

    public function providerParseStrOpts()
    {
        return array(
            'boolean' => array(false, 'parseStrOpts expects string or array. boolean provided.'),
            'object' => array(new \stdClass(), 'parseStrOpts expects string or array. stdClass provided.'),
        );
    }
}
