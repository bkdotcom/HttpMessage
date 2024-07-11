<?php

namespace bdk\Test\HttpMessage;

use bdk\PhpUnitPolyfill\AssertionTrait;
use bdk\PhpUnitPolyfill\ExpectExceptionTrait;
use PHPUnit\Framework\TestCase as TestCaseBase;

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
