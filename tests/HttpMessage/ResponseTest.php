<?php

namespace bdk\Test\HttpMessage;

use bdk\HttpMessage\Message;
use bdk\HttpMessage\Response;
use Exception;
use PHPUnit\Framework\AssertionFailedError;
use TypeError;

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

    /**
     * @param mixed  $code         status code to test
     * @param mixed  $phrase       phrase to test
     * @param string $phraseExpect expected phrase
     *
     * @dataProvider statusPhrases
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
     * @dataProvider invalidStatusCodes
     */
    public function testRejectInvalidStatusCode($statusCode)
    {
        try {
            $response = $this->createResponse();
            $response->withStatus($statusCode, 'Custom reason phrase');
        } catch (Exception $e) {
            self::assertSame(\get_class($e), 'InvalidArgumentException');
            return;
        } catch (TypeError $e) {
            self::assertSame(\get_class($e), 'TypeError');
            return;
        }
        throw new AssertionFailedError('Exception not thrown');
    }

    /**
     * @param mixed $reasonPhrase reason phrase to test
     *
     * @dataProvider invalidReasonPhrases
     */
    public function testRejectInvalidReasonPhrase($reasonPhrase)
    {
        try {
            $response = $this->createResponse();
            $response->withStatus(200, $reasonPhrase);
        } catch (Exception $e) {
            self::assertSame(\get_class($e), 'InvalidArgumentException');
            return;
        } catch (TypeError $e) {
            self::assertSame(\get_class($e), 'TypeError');
            return;
        }
        throw new AssertionFailedError('Exception not thrown');
    }
}
