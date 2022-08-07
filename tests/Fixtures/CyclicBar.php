<?php

namespace Hyqo\Container\Test\Fixtures;

class CyclicBar
{
    public function __construct(CyclicFoo $foo)
    {
    }
}
