<?php

namespace Hyqo\Container;

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Pure;

class Container
{
    private array $services = [];

    private array $aliases = [];

    private Reflection $reflection;

    private static ?self $instance = null;

    #[Pure]
    public function __construct()
    {
        $this->reflection = new Reflection();
    }

    #[Deprecated]
    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public function bind(string $class, string $realisation): void
    {
        $this->aliases[$class] = $realisation;
    }

    public function set(string $classname, object $instance): object
    {
        return $this->services[$classname] = $instance;
    }

    /**
     * @template T
     * @param class-string<T> $classname
     * @return T
     */
    public function get(string $classname): object
    {
        return $this->services[$classname] ??= $this->make($classname);
    }

    /**
     * @template T
     * @param class-string<T> $classname
     * @return T
     */
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

    public function call(callable $callable, array $arguments = []): mixed
    {
        $reflection = $this->reflection->getReflectionCallable($callable);

        $resolvedDependencies = $this->resolveDependencies($reflection, $arguments);

        return $callable(...$resolvedDependencies);
    }

    private function resolveDependencies(\ReflectionFunctionAbstract $function, array $arguments = []): \Generator
    {
        foreach ($function->getParameters() as $parameter) {
            if (isset($arguments[$parameter->getName()])) {
                yield $arguments[$parameter->getName()];
            } elseif (($type = $parameter->getType()) && !$type->isBuiltin()) {
                yield $this->get($type->getName());
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
