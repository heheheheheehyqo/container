<?php

namespace Hyqo\Container\Resolver;

use Hyqo\Container\Container;

abstract class Resolver implements ResolverInterface
{
    public function __construct(protected Container $container)
    {
    }

    protected function isValidType($type): bool
    {
        return $type instanceof \ReflectionNamedType && !$type->isBuiltin();
    }
}
