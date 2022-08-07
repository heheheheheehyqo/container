<?php

namespace Hyqo\Container\Resolver;

use Hyqo\Container\Container;
use Hyqo\Container\Exception\CyclicDependencyException;

abstract class Resolver implements ResolverInterface
{
    /** @var Container */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    protected function isValidType($type): bool
    {
        return $type instanceof \ReflectionNamedType && !$type->isBuiltin();
    }
}
