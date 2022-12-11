<?php


namespace Hyqo\Container;


use Hyqo\Container\Exception\NotFoundException;

class Reflection
{
    /**
     * @var array<string, \ReflectionClass<object>>
     */
    private static array $reflectionClass = [];

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
            throw new NotFoundException($e->getMessage());
        }
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function getReflectionCallable(callable $callable): \ReflectionFunctionAbstract
    {
        switch (true) {
            case (is_array($callable)):
                [$class, $method] = $callable;
                return new \ReflectionMethod($class, $method);

            case (is_string($callable) && (str_contains($callable, '::'))):
                [$class, $method] = explode('::', $callable);
                return new \ReflectionMethod($class, $method);

            default:
                return new \ReflectionFunction($callable);
        }
    }
}
