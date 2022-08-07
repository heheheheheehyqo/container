<?php

namespace Hyqo\Container;

use Hyqo\Container\Exception\ContainerException;
use Hyqo\Container\Exception\CyclicDependencyException;
use Hyqo\Container\Exception\NotFoundException;
use Hyqo\Container\Resolver\FunctionResolver;
use Hyqo\Container\Resolver\MethodResolver;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /** @var array<string,object> */
    protected $storage;

    protected $beingResolved = [];

    /** @var array<class-string,array> */
    protected $configuration = [];

    /** @var array<class-string,class-string> */
    protected $aliases = [];

    /** @var Reflection */
    protected $reflection;

    /** @var MethodResolver */
    protected $methodResolver;

    /** @var FunctionResolver */
    protected $functionResolver;

    /**
     * @var null|static
     * @deprecated
     */
    protected static $instance = null;

    final public function __construct()
    {
        $this->reflection = new Reflection();

        $this->methodResolver = new MethodResolver($this);
        $this->functionResolver = new FunctionResolver($this);

        $this->storage = [
            self::class => $this,
            ContainerInterface::class => $this
        ];
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

    /**
     * @return $this
     */
    public function construct(string $classname, array $arguments): self
    {
        $this->configuration[$classname] = $arguments;

        return $this;
    }

    public function getConfiguration(string $classname): array
    {
        return $this->configuration[$classname] ?? [];
    }

    public function set(string $id, object $instance): object
    {
        return $this->storage[$id] = $instance;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->storage);
    }

    /**
     * @template T
     * @param class-string<T> $id
     * @return T|mixed
     */
    public function get(string $id)
    {
        if ($this->has($id)) {
            return $this->storage[$id];
        }

        return $this->storage[$id] = $this->make($id);
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
            if (array_key_exists($classname, $this->beingResolved)) {
                throw new CyclicDependencyException(
                    sprintf(
                        "Circular dependency detected: %s",
                        implode(' -> ', array_keys($this->beingResolved))
                    )
                );
            }

            $this->beingResolved[$classname] = true;

            $resolvedDependencies = $this->methodResolver->resolve($constructor, $arguments);

            try {
                return $reflection->newInstance(...$resolvedDependencies);
            } catch (\ReflectionException $e) {
                throw new ContainerException($e->getMessage());
            } finally {
                unset($this->beingResolved[$classname]);
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

        $resolvedDependencies = $this->functionResolver->resolve($reflection, $arguments);

        if (is_callable($callable)) {
            return $callable(...$resolvedDependencies);
        }

        throw new ContainerException('The value passed to parameter $callable must be callable');
    }
}
