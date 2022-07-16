<?php


namespace Hyqo\Container;


class Reflection
{
    /**
     * @var array<string, \ReflectionClass<object>>
     */
    private static $reflectionClass = [];

    /**
     * @template T of object
     * @param class-string<T> $classname
     * @return \ReflectionClass<T>
     */
    public function getReflectionClass(string $classname): \ReflectionClass
    {
        try {
            if (!array_key_exists($classname, self::$reflectionClass)) {
                self::$reflectionClass[$classname] = new \ReflectionClass($classname);
            }

            return self::$reflectionClass[$classname];
        } catch (\ReflectionException $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }
    }

    /**
     * @param callable|array{object,string} $callable
     * @throws \ReflectionException
     */
    public function getReflectionCallable($callable): \ReflectionFunctionAbstract
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
                return new \ReflectionFunction($callable);
            default:
                throw new \RuntimeException();
        }
    }
}
