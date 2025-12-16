<?php

namespace Firesphere\PartialUserforms\Tests;

use ReflectionClass;
use ReflectionException;

/**
 * A simple helper class for tests
 * @package Firesphere\PartialUserforms\Tests
 */
class TestHelper
{
    /**
     * Call protected/private method of a class
     *
     * @param Object $object
     * @param string $methodName
     * @param array $parameters
     * @return mixed
     * @throws ReflectionException
     *
     * @link https://jtreminio.com/blog/unit-testing-tutorial-part-iii-testing-protected-private-methods-coverage-reports-and-crap/
     */
    public static function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass($object::class);
        $method = $reflection->getMethod($methodName);

        return $method->invokeArgs($object, $parameters);
    }
}
