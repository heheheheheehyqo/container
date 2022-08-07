<?php

namespace Hyqo\Container;

use Hyqo\Container\Definition\GetDefinition;
use Hyqo\Container\Definition\MakeDefinition;
use Hyqo\Container\Definition\DefinitionInterface;

function get(string $id): DefinitionInterface
{
    return new GetDefinition($id);
}

function make(string $id, array $arguments = []): DefinitionInterface
{
    return new MakeDefinition($id, $arguments);
}
