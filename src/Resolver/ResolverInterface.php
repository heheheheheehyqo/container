<?php

namespace Hyqo\Container\Resolver;

use Hyqo\Container\Container;

interface ResolverInterface
{
    public function __construct(Container $container);
}
