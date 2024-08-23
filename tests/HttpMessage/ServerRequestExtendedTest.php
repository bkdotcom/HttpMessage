<?php

namespace bdk\Test\HttpMessage;

use bdk\HttpMessage\ServerRequestExtended;
use bdk\HttpMessage\Utility\ContentType;
use bdk\Pager\Content;

/**
 * @covers \bdk\HttpMessage\ServerRequestExtended
 */
class ServerRequestExtendedTest extends TestCase
{
    public function testFromServerRequest()
    {
        $attributes = array(
            'authorized' => true,
        );
        $serverParams = [
            'HTTPS' => 'on',
            'HTTP_CONTENT_TYPE' => ContentType::FORM_MULTIPART,
            'SERVER_PROTOCOL' => 'HTTP/2',
        ];
        $body = $this->createStream(__DIR__ . '/Utility/input.txt');
        $cookieParams = ['theme' => 'dark'];
        $requestTarget = '/hidden?secret=potato';
        $files = self::mockFiles();
        $uri = $this->createUri('https://test.com/?queryParam=foo');
        $request = $this->createServerRequest('PUT', $uri, $serverParams)
            ->withBody($body)
            ->withQueryParams(array('greetings' => 'not from url'))
            ->withParsedBody(array('greetings' => 'not from body'))
            ->withUploadedFiles($files)
            ->withCookieParams($cookieParams)
            ->withRequestTarget($requestTarget);

        foreach ($attributes as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        $request = ServerRequestExtended::fromServerRequest($request);

        self::assertInstanceOf('bdk\\HttpMessage\\ServerRequestExtended', $request);
        self::assertInstanceOf('bdk\\HttpMessage\\ServerRequest', $request);
        self::assertInstanceOf('bdk\\HttpMessage\\ServerRequestExtendedInterface', $request);
        self::assertInstanceOf('Psr\\Http\\Message\\ServerRequestInterface', $request);

        self::assertSame('PUT', $request->getMethod());
        self::assertSame('2', $request->getProtocolVersion());
        self::assertSame($uri, $request->getUri());
        self::assertSame(array(
            'Host' => array(
                'test.com',
            ),
            'Content-Type' => array(
                ContentType::FORM_MULTIPART,
            ),
        ), $request->getHeaders());
        self::assertSame($cookieParams, $request->getCookieParams());
        self::assertSame($body, $request->getBody());
        self::assertSame($attributes, $request->getAttributes());
        self::assertSame($requestTarget, $request->getRequestTarget());
        self::assertSame($files, $request->getUploadedFiles());
        self::assertSame(array('greetings' => 'not from url'), $request->getQueryParams());
        self::assertSame(array('greetings' => 'not from body'), $request->getParsedBody());
        self::assertSame(\array_merge(array(
            'REQUEST_METHOD' => 'PUT',
        ), $serverParams), $request->getServerParams());

        // test already ServerRequestExtended
        self::assertSame($request, ServerRequestExtended::fromServerRequest($request));
    }

    public function testGetCookieParam()
    {
        $request = $this->createServerRequestExtended();
        $request = $request->withCookieParams(['user' => 'mrIncredible']);

        self::assertSame('mrIncredible', $request->getCookieParam('user'));

        // test default value
        self::assertSame('light', $request->getCookieParam('theme', 'light'));
    }

    public function testGetMediaType()
    {
        $request = $this->createServerRequestExtended();
        $request = $request
            ->withHeader('Content-Type', 'application/json; charset="utf-8"')
            ->withAddedHeader('Content-Type', 'application/xml');

        self::assertSame('application/json', $request->getMediaType());

        // test no content-type header
        $request = $this->createServerRequestExtended();
        self::assertNull($request->getMediaType());
    }

    public function testGetMediaTypeParams()
    {
        $request = $this->createServerRequestExtended();
        $request = $request
            ->withHeader('Content-Type', 'application/json; charSet="UTF-8"; FOO = "b; a\\"r"')
            ->withAddedHeader('Content-Type', 'application/xml; thing=stuff');

        self::assertSame([
            'charset' => 'utf-8',
            'foo' => 'b; a"r',
        ], $request->getMediaTypeParams());

        // test no params
        $request = $this->createServerRequestExtended();
        $request = $request->withHeader('Content-Type', 'application/json');

        self::assertSame([
            'charset' => null,
        ], $request->getMediaTypeParams());

        // test no Content-Type header
        $request = $this->createServerRequestExtended();
        self::assertSame([
            'charset' => null,
        ], $request->getMediaTypeParams());
    }

    public function testGetParam()
    {
        // from body
        $request = $this->createServerRequestExtended();
        $request = $request->withParsedBody(['foo' => 'bar']);
        $clone = $request->withParsedBody((object) ['foo' => 'bar']);

        self::assertSame('bar', $request->getParam('foo'));
        self::assertSame('bar', $clone->getParam('foo'));

        // from get
        $request = $this->createServerRequestExtended('GET', '/?foo=bar');
        self::assertSame('bar', $request->getParam('foo'));

        // body prioritized over query
        $request = $this->createServerRequestExtended('POST', '/?queryParam=foo&both=query');
        $request = $request
            ->withHeader('Content-Type', ContentType::FORM)
            ->withBody($this->createStream(\http_build_query([
                'bodyParam' => 'bar',
                'both' => 'body',
            ])));
        self::assertSame('body', $request->getParam('both'));

        // default
        self::assertSame('hello', $request->getParam('notSet', 'hello'));
    }

    public function testGetParams()
    {
        // from query
        $request = $this->createServerRequestExtended('GET', '/?foo=bar&bar=baz');
        self::assertSame([
            'bar' => 'baz',
            'foo' => 'bar',
        ], $request->getParams());

        // body prioritized over query
        $request = $this->createServerRequestExtended('POST', '/?queryParam=foo&both=query');
        $request = $request
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody($this->createStream(http_build_query([
                'bodyParam' => 'bar',
                'both' => 'body',
            ])));

        self::assertSame([
            'bodyParam' => 'bar',
            'both' => 'body',
            'queryParam' => 'foo',
        ], $request->getParams());
    }

    public function testGetParsedBodyParam()
    {
        $request = $this->createServerRequestExtended('POST');
        $request = $request->withParsedBody(['foo' => 'bar']);
        $clone = $request->withParsedBody((object) ['foo' => 'bar']);

        self::assertSame('bar', $request->getParsedBodyParam('foo'));
        self::assertSame('bar', $clone->getParsedBodyParam('foo'));

        // default
        self::assertSame('hello', $request->getParsedBodyParam('notSet', 'hello'));
    }

    public function testGetQueryParam()
    {
        $request = $this->createServerRequestExtended('GET', '/?foo=bar');
        self::assertSame('bar', $request->getQueryParam('foo'));

        // default
        self::assertSame('baz', $request->getQueryParam('bar', 'baz'));
    }

    public function testGetServerParam()
    {
        $serverParams = ['HTTP_AUTHORIZATION' => 'test'];
        $request = $this->createServerRequestExtended('GET', 'https://test.com', $serverParams);

        self::assertSame('test', $request->getServerParam('HTTP_AUTHORIZATION'));

        // default
        self::assertSame(false, $request->getServerParam('IS_DEV', false));
    }

    public function testIsSecure()
    {
        $serverParams = array(
            'HTTPS' => 'on',
        );
        $request = $this->createServerRequestExtended('GET', 'https://test.com', $serverParams);

        self::assertTrue($request->isSecure());

        $request = $this->createServerRequestExtended();
        self::assertFalse($request->isSecure());
    }

    public function testIsXhr()
    {
        $request = $this->createServerRequestExtended();

        self::assertFalse($request->isXhr());

        $request = $request->withHeader('X-Requested-With', 'XMLHttpRequest');

        self::assertTrue($request->isXhr());
    }

    public function testWithAttributes()
    {
        $attributes = array(
            'foo' => 'bar',
            'bar' => 'baz',
        );

        $request = $this->createServerRequestExtended();
        $request = $request->withAttributes($attributes);

        self::assertSame($attributes, $request->getAttributes());
    }
}
