<?php

namespace bdk\Test\HttpMessage;

use bdk\HttpMessage\Message;
use bdk\HttpMessage\Response;

/**
 * @covers \bdk\HttpMessage\AssertionTrait
 * @covers \bdk\HttpMessage\Response
 */
class ResponseTest extends TestCase
{
    public function testConstruct()
    {
        $response = new Response();
        $this->assertTrue($response instanceof Response);
        $this->assertTrue($response instanceof Message);
    }

    public function testConstructException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Status code must to be an integer, string provided.');
        $this->createResponse('bogusCode');
    }

    /**
     * @param mixed  $code         status code to test
     * @param mixed  $phrase       phrase to test
     * @param string $phraseExpect expected phrase
     *
     * @dataProvider statusPhrasesValid
     */
    public function testStatusPhrases($code, $phrase, $phraseExpect)
    {
        $response = $this->createResponse($code, $phrase);
        $response->withStatus($code, $phrase);
        $this->assertSame((int) $code, $response->getStatusCode());
        $this->assertSame($phraseExpect, $response->getReasonPhrase());
    }

    /*
        Exceptions
    */

    /**
     * @param mixed $statusCode status code to test
     *
     * @dataProvider statusCodesInvalid
     */
    public function testStatusCodeRejected($statusCode)
    {
        self::assertExceptionOrTypeError(function () use ($statusCode) {
            $response = $this->createResponse();
            $response->withStatus($statusCode, 'Custom reason phrase');
        });
    }

    /**
     * @param mixed $reasonPhrase reason phrase to test
     *
     * @dataProvider statusPhrasesInvalid
     */
    public function testStatusPhraseRejected($phrase)
    {
        self::assertExceptionOrTypeError(function () use ($phrase) {
            $response = $this->createResponse();
            $response->withStatus(200, $phrase);
        });
    }
}
