<?php

namespace bdk\Test\HttpFactory;

use bdk\PhpUnitPolyfill\AssertionTrait;
use bdk\PhpUnitPolyfill\ExpectExceptionTrait;
use InvalidArgumentException;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Error\Notice as ErrorNotice;
use PHPUnit\Framework\TestCase as TestCaseBase;
use RuntimeException;
use TypeError;

/**
 *
 */
class TestCase extends TestCaseBase
{
    use AssertionTrait;
    use ExpectExceptionTrait;

    protected static $errorHandler;

    public static function setUpBeforeClass(): void
    {
        self::$errorHandler = \set_error_handler(static function ($type, $msg) {
            if ($type & E_USER_DEPRECATED) {
                return true;
            }
            throw new RuntimeException($msg);
        });
    }

    public static function tearDownAfter(): void
    {
        \set_error_handler(self::$errorHandler);
    }

    protected static function assertExceptionOrTypeError($callable)
    {
        try {
            $callable();
        } catch (ErrorNotice $e) {
            self::assertSame('A non well formed numeric value encountered', $e->getMessage());
            return;
        } catch (RuntimeException $e) {
            self::assertSame('A non well formed numeric value encountered', $e->getMessage());
            return;
        } catch (InvalidArgumentException $e) {
            self::assertTrue(true);
            return;
        } catch (TypeError $e) {
            self::assertSame(\get_class($e), 'TypeError');
            return;
        }
        throw new AssertionFailedError('Exception not thrown');
    }
}
