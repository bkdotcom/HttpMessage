<?php

namespace bdk\Test;

// backward compatibility
$classMap = array(
    // PHP 5.3 doesn't like leading backslash
    'PHPUnit_Framework_AssertionFailedError' => 'PHPUnit\Framework\AssertionFailedError',
    'PHPUnit_Framework_Constraint_IsType' => 'PHPUnit\Framework\Constraint\IsType',
    'PHPUnit_Framework_Exception' => 'PHPUnit\Framework\Exception',
    'PHPUnit_Framework_TestCase' => 'PHPUnit\Framework\TestCase',
    'PHPUnit_Framework_TestSuite' => 'PHPUnit\Framework\TestSuite',
);
foreach ($classMap as $old => $new) {
    if (\class_exists($new) === false) {
        \class_alias($old, $new);
    }
}

require __DIR__ . '/../vendor/autoload.php';

\define('TEST_DIR', __DIR__);

\ini_set('xdebug.var_display_max_depth', 3);
\ini_set('xdebug.var_display_max_data', '-1');

$modifyTests = new \bdk\DevUtil\ModifyTests();
$modifyTests->modify(__DIR__);

\register_shutdown_function(static function () {
    /*
    $files = \glob(TEST_DIR . '/../tmp/log/*.json');
    foreach ($files as $filePath) {
        \unlink($filePath);
    }
    */
    $files = array(
        __DIR__ . '/../tmp/logo_clone.png',
    );
    foreach ($files as $file) {
        if (\is_file($file)) {
            \unlink($file);
        }
    }
});
