<?php

namespace bdk\Test\HttpMessage;

use bdk\PhpUnitPolyfill\AssertionTrait;
use bdk\PhpUnitPolyfill\ExpectExceptionTrait;
use PHPUnit\Framework\TestCase as TestCaseBase;
use ReflectionClass;

/**
 *
 */
class TestCase extends TestCaseBase
{
    use AssertionTrait;
    use ExpectExceptionTrait;
    use DataProviderTrait;
    use FactoryTrait;

    // defining this in DataProviderTrait -> fatal error on php 8.0
    protected static $hasParamTypes = null;

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
}
