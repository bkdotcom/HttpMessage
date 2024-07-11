<?php

namespace bdk\Test\HttpMessage;

use bdk\PhpUnitPolyfill\AssertionTrait;
use bdk\PhpUnitPolyfill\ExpectExceptionTrait;
use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase as TestCaseBase;
use ReflectionClass;
use ReflectionProperty;

/**
 *
 */
class TestCase extends TestCaseBase
{
    use AssertionTrait;
    use ExpectExceptionTrait;
    use DataProviderTrait;
    use FactoryTrait;
}
