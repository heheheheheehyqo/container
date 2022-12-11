<?php

namespace Hyqo\Container\Definition;

use Hyqo\Container\Container;

readonly class MakeDefinition implements DefinitionInterface
{
    public function __construct(
        private string $id,
        private array $arguments = []
    ) {
    }

    public function resolve(Container $container)
    {
        return $container->make($this->id, $this->arguments);
    }
}
