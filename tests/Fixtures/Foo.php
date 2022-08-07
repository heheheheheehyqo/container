<?php

namespace Hyqo\Container\Test\Fixtures;

class Foo
{
    private $bar;

    public function __construct(Bar $bar)
    {
        $this->bar = $bar;
    }

    public function print(): string
    {
        return $this->bar->print();
    }
}
