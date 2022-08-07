<?php

namespace Hyqo\Container\Definition;

use Hyqo\Container\Container;

class MakeDefinition implements DefinitionInterface
{
    private $id;
    private $arguments;

    public function __construct(string $id, array $arguments = [])
    {
        $this->id = $id;
        $this->arguments = $arguments;
    }

    public function resolve(Container $container)
    {
        return $container->make($this->id, $this->arguments);
    }
}
