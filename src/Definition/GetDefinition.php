<?php

namespace Hyqo\Container\Definition;

use Hyqo\Container\Container;

class GetDefinition implements DefinitionInterface
{
    private $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function resolve(Container $container)
    {
        return $container->get($this->id);
    }
}
