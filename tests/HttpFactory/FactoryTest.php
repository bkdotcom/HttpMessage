<?php

namespace bdk\Test\HttpFactory;

use bdk\HttpFactory\Factory;

/**
 * @covers \bdk\HttpFactory\Factory
 */
class FactoryTest extends TestCase
{
    public function testCreateRequest()
    {
        $factory = new Factory();
        $request = $factory->createRequest('GET', 'http://example.com');
        $this->assertInstanceOf('bdk\HttpMessage\Request', $request);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('http://example.com', (string) $request->getUri());

        $uri = $factory->createUri('http://example.com/test');
        $request = $factory->createRequest('POST', $uri);
        $this->assertInstanceOf('bdk\HttpMessage\Request', $request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame($uri, $request->getUri());
    }

    public function testCreateResponse()
    {
        $factory = new Factory();
        $response = $factory->createResponse();
        $this->assertInstanceOf('bdk\HttpMessage\Response', $response);
        $this->assertSame(200, $response->getStatusCode());

        $response = $factory->createResponse(500, 'Dang');
        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Dang', $response->getReasonPhrase());
    }

    public function testCreateServerRequest()
    {
        $factory = new Factory();
        $serverRequest = $factory->createServerRequest('GET', 'http://example.com');
        $this->assertInstanceOf('bdk\HttpMessage\ServerRequestExtended', $serverRequest);

        $uri = $factory->createUri('http://example.com/test');
        $serverParams = array('foo' => 'bar');
        $request = $factory->createServerRequest('POST', $uri, $serverParams);
        $this->assertInstanceOf('bdk\HttpMessage\ServerRequestExtended', $request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame($uri, $request->getUri());
        $this->assertSame($serverParams, \array_intersect_key($request->getServerParams(), array('foo' => 'bar')));
    }

    public function testCreateStream()
    {
        $factory = new Factory();
        $content = 'test 1' . "\n" . 'test 2';
        $stream = $factory->createStream($content);
        $this->assertInstanceOf('bdk\HttpMessage\Stream', $stream);
        $this->assertSame($content, (string) $stream);
    }

    public function testCreateStreamFromFile()
    {
        $factory = new Factory();
        $stream = $factory->createStreamFromFile(__FILE__);
        $this->assertInstanceOf('bdk\HttpMessage\Stream', $stream);
        $this->assertSame(\file_get_contents(__FILE__), (string) $stream);
    }

    public function testCreateStreamFromFileInvalidMode()
    {
        $factory = new Factory();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The mode "bork" is invalid.');
        $factory->createStreamFromFile(__FILE__, 'bork');
    }

    public function testCreateStreamFromFileInvalidFile()
    {
        $factory = new Factory();
        $this->expectException(\RuntimeException::class);
        $file = __DIR__ . '/not-exists';
        $this->expectExceptionMessage(\sprintf('The file %s cannot be opened.', $file));
        $factory->createStreamFromFile($file, 'r');
    }

    public function testCreateStreamFromResource()
    {
        $factory = new Factory();

        $resource = \fopen('php://temp', 'wb+');
        $content = 'test 1' . "\n" . 'test 2';
        \fwrite($resource, $content);

        $stream = $factory->createStreamFromResource($resource);
        $this->assertInstanceOf('bdk\HttpMessage\Stream', $stream);
        $this->assertSame($content, (string) $stream);
    }

    public function testCreateUploadedFile()
    {
        $factory = new Factory();
        $content = 'test 1' . "\n" . 'test 2';
        $uploadedFile = $factory->createUploadedFile(
            $factory->createStream($content),
            null,
            UPLOAD_ERR_OK,
            'test.txt',
            'text/plain'
        );
        $this->assertInstanceOf('bdk\HttpMessage\UploadedFile', $uploadedFile);
        $this->assertSame($content, (string) $uploadedFile->getStream());
        $this->assertSame(\strlen($content), $uploadedFile->getSize());
        $this->assertSame(UPLOAD_ERR_OK, $uploadedFile->getError());
        $this->assertSame('test.txt', $uploadedFile->getClientFilename());
        $this->assertSame('text/plain', $uploadedFile->getClientMediaType());
    }

    public function testCreateUri()
    {
        $factory = new Factory();
        $uri = $factory->createUri('http://example.com');
        $this->assertInstanceOf('bdk\HttpMessage\Uri', $uri);
        $this->assertSame('http://example.com', (string) $uri);
    }
}
