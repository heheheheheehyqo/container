<?php

namespace Hyqo\Container\Test\Fixtures;

class CyclicFoo
{
    public function __construct(CyclicBar $bar)
    {
    }
}
