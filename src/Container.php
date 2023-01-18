<?php

namespace Hyqo\Container;

use Hyqo\Container\Exception\ContainerException;
use Hyqo\Container\Exception\CyclicDependencyException;
use Hyqo\Container\Resolver\FunctionResolver;
use Hyqo\Container\Resolver\MethodResolver;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /** @var array<string,object> */
    protected array $storage;

    protected array $beingResolved = [];

    /** @var array<class-string,array> */
    protected array $configuration = [];

    /** @var array<class-string,class-string> */
    protected array $aliases = [];

    /** @var Reflection */
    protected Reflection $reflection;

    /** @var MethodResolver */
    protected MethodResolver $methodResolver;

    /** @var FunctionResolver */
    protected FunctionResolver $functionResolver;

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
     * @param class-string $interface
     * @param class-string $realisation
     */
    public function bind(string $interface, string $realisation): static
    {
        $this->aliases[$interface] = $realisation;

        return $this;
    }

    public function construct(string $classname, array $arguments): static
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
     * @return T
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

        if (null === $constructor) {
            return new $classname;
        }

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
            return new $classname(...$resolvedDependencies);
        } finally {
            unset($this->beingResolved[$classname]);
        }
    }

    public function call(callable $callable, array $arguments = []): mixed
    {
        $reflection = $this->reflection->getReflectionCallable($callable);

        $resolvedDependencies = $this->functionResolver->resolve($reflection, $arguments);

        return $callable(...$resolvedDependencies);
    }
}
