<?php

namespace bdk\Test\HttpMessage\Utility;

use bdk\HttpMessage\Stream;
use bdk\HttpMessage\Utility\Stream as StreamUtility;
use PHPUnit\Framework\TestCase;

/**
 * @covers bdk\HttpMessage\Utility\Response
 *
 * @phpcs:disable SlevomatCodingStandard.Arrays.AlphabeticallySortedByKeys.IncorrectKeyOrder
 */
class UtilityTest extends TestCase
{
    public function testGetStreamContents()
    {
        $stream = new Stream('this is a test');
        $stream->seek(8);
        self::assertSame('this is a test', StreamUtility::getContents($stream));
        self::assertSame('a test', $stream->getContents());
    }
}
