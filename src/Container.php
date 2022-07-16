<?php

namespace Hyqo\Container;

use Hyqo\Container\Exception\ContainerException;
use Hyqo\Container\Exception\NotFoundException;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /** @var array<string,object> */
    protected $services = [];

    /** @var array<class-string,class-string> */
    protected $aliases = [];

    /** @var Reflection */
    protected $reflection;

    /** @var null|static */
    protected static $instance = null;

    final public function __construct()
    {
        $this->reflection = new Reflection();
    }

    /**
     * @return static
     * @deprecated
     */
    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * @param class-string $interface
     * @param class-string $realisation
     * @return $this
     */
    public function bind(string $interface, string $realisation): self
    {
        $this->aliases[$interface] = $realisation;

        return $this;
    }

    public function set(string $classname, object $instance): object
    {
        return $this->services[$classname] = $instance;
    }

    /**
     * @template T
     * @param class-string<T> $id
     * @return T|mixed
     */
    public function get(string $id)
    {
        if (array_key_exists($id, $this->services)) {
            return $this->services[$id];
        }

        return $this->services[$id] = $this->make($id);
    }

    /**
     * @template T
     * @param class-string<T> $classname
     * @return T|mixed
     * @throws ContainerException
     */
    public function make(string $classname, array $arguments = []): object
    {
        $classname = $this->aliases[$classname] ?? $classname;

        $reflection = $this->reflection->getReflectionClass($classname);

        if (!$reflection->isInstantiable()) {
            throw new ContainerException("Can not instantiate $classname");
        }

        $constructor = $reflection->getConstructor();

        if ($constructor) {
            $resolvedDependencies = $this->resolveDependencies($constructor, $arguments);

            try {
                return $reflection->newInstance(...$resolvedDependencies);
            } catch (\ReflectionException $e) {
                throw new ContainerException($e->getMessage());
            }
        }

        try {
            return $reflection->newInstanceWithoutConstructor();
        } catch (\ReflectionException $e) {
            throw new ContainerException($e->getMessage());
        }
    }

    /**
     * @param callable|array{object,string} $callable
     *
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function call($callable, array $arguments = [])
    {
        try {
            $reflection = $this->reflection->getReflectionCallable($callable);
        } catch (\ReflectionException $e) {
            throw new NotFoundException($e->getMessage());
        }

        $resolvedDependencies = $this->resolveDependencies($reflection, $arguments);

        if (is_callable($callable)) {
            return $callable(...$resolvedDependencies);
        }

        throw new ContainerException('The value passed to parameter $callable must be callable');
    }

    private function resolveDependencies(\ReflectionFunctionAbstract $function, array $arguments = []): \Generator
    {
        foreach ($function->getParameters() as $parameter) {
            if (isset($arguments[$parameter->getName()])) {
                yield $arguments[$parameter->getName()];
            } elseif (($type = $parameter->getType()) && $type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                $classname = $type->getName();

                yield $this->get($classname);
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
