<?php

namespace bdk\Test\HttpMessage;

use bdk\PhpUnitPolyfill\AssertionTrait;
use bdk\PhpUnitPolyfill\ExpectExceptionTrait;
use InvalidArgumentException;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Error\Notice as ErrorNotice;
use PHPUnit\Framework\TestCase as TestCaseBase;
use ReflectionClass;
use RuntimeException;
use TypeError;

/**
 *
 */
class TestCase extends TestCaseBase
{
    use AssertionTrait;
    use ExpectExceptionTrait;
    use DataProviderTrait;
    use FactoryTrait;

    // defining this in DataProviderTrait -> fatal error on php 8.0 (but not 8.1+)
    protected static $hasParamTypes = null;

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

    protected static function hasParamTypes()
    {
        if (PHP_VERSION_ID >= 70000 && isset(self::$hasParamTypes) === false) {
            $refClass = new ReflectionClass('Psr\Http\Message\MessageInterface');
            $refMethod = $refClass->getMethod('withProtocolVersion');
            $refParams = $refMethod->getParameters();
            $refParam = $refParams[0];
            self::$hasParamTypes = $refParam->hasType();
        }
        return self::$hasParamTypes;
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

    /*
        Methods that help for testing.
    */

    /**
     * Moke a uploadedFiles array
     *
     * @param int $item which array to return
     *
     * @return array
     */
    protected static function mockFiles($item = 1)
    {
        if ($item === 1) {
            return array(
                'file1' => self::createUploadedFile(
                    '/tmp/php1234.tmp',
                    100000,
                    UPLOAD_ERR_OK,
                    'file1.jpg',
                    'image/jpeg'
                ),
            );
        }
        if ($item === 2) {
            return array(
                'file2' => self::createUploadedFile(
                    '/tmp/php1235',
                    123456,
                    UPLOAD_ERR_OK,
                    'file2.png',
                    'image/png'
                ),
            );
        }
    }
}
