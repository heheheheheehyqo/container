<?php

namespace Hyqo\Container\Definition;

use Hyqo\Container\Container;

readonly class GetDefinition implements DefinitionInterface
{
    public function __construct(public string $id)
    {
    }

    public function resolve(Container $container)
    {
        return $container->get($this->id);
    }
}
