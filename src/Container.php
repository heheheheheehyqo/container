<?php


namespace Hyqo\Container;


class Container
{
    /** @var array */
    private $services = [];

    /** @var array */
    private $aliases = [];

    /** @var Reflection */
    private $reflection;

    public function __construct()
    {
        $this->reflection = new Reflection();
    }

    public function bind(string $class, string $realisation)
    {
        $this->aliases[$class] = $realisation;
    }

    public function set(string $classname, object $instance): object
    {
        return $this->services[$classname] = $instance;
    }

    public function get(string $classname): object
    {
        if (isset($this->services[$classname])) {
            return $this->services[$classname];
        }

        return $this->services[$classname] = $this->make($classname);
    }

    public function make(string $classname, array $arguments = []): object
    {
        $reflection = $this->reflection->getReflectionClass($this->aliases[$classname] ?? $classname);

        if (!$reflection->isInstantiable()) {
            throw new \InvalidArgumentException("Can not instantiate $classname");
        }

        $constructor = $reflection->getConstructor();

        if ($constructor) {
            $resolvedDependencies = $this->resolveDependencies($constructor, $arguments);

            return $reflection->newInstance(...$resolvedDependencies);
        }

        return $reflection->newInstance();
    }

    /**
     * @param callable $callable
     * @param array $arguments
     *
     * @return false|mixed
     */
    public function call(callable $callable, array $arguments = [])
    {
        $reflection = $this->reflection->getReflectionCallable($callable);

        $resolvedDependencies = $this->resolveDependencies($reflection, $arguments);

        return call_user_func($callable, ...$resolvedDependencies);
    }

    private function resolveDependencies(\ReflectionFunctionAbstract $function, array $arguments = []): \Generator
    {
        foreach ($function->getParameters() as $parameter) {
            if (isset($arguments[$parameter->getName()])) {
                yield $arguments[$parameter->getName()];
            } elseif ($parameter->getClass()) {
                yield $this->get($parameter->getClass()->getName());
            } elseif ($parameter->isDefaultValueAvailable()) {
                yield $parameter->getDefaultValue();
            } elseif ($parameter->getDeclaringClass()) {
                throw new \InvalidArgumentException(
                    sprintf(
                        "$%s in %s::%s must be typed or passed",
                        $parameter->getName(),
                        $parameter->getDeclaringClass()->getName(),
                        $function->getName()
                    )
                );
            } else {
                throw new \InvalidArgumentException(
                    sprintf(
                        "$%s in %s must be typed or passed",
                        $parameter->getName(),
                        $function->getName()
                    )
                );
            }
        }
    }
}
