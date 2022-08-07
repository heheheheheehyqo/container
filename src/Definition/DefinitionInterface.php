<?php

namespace Hyqo\Container\Definition;

use Hyqo\Container\Container;

interface DefinitionInterface
{
    public function resolve(Container $container);
}
