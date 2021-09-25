<?php


namespace Hyqo\Container;


class Reflection
{
    private static array $reflectionClass = [];

    public function getReflectionClass(string $classname): \ReflectionClass
    {
        try {
            return self::$reflectionClass[$classname] ??= new \ReflectionClass($classname);
        } catch (\ReflectionException $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }
    }

    /**
     * @throws \ReflectionException
     */
    public function getReflectionCallable(callable $callable): \ReflectionFunctionAbstract
    {
        switch (true) {
            case (is_array($callable) && count($callable) === 2):
                [$class, $method] = $callable;

                return new \ReflectionMethod($class, $method);

            case (is_string($callable) && (substr_count($callable, '::') === 1)):
                [$class, $method] = explode('::', $callable);

                return new \ReflectionMethod($class, $method);

            case (is_string($callable)):
            case ($callable instanceof \Closure):
            default:
                return new \ReflectionFunction($callable);
        }
    }
}
