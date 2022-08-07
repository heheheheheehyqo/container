<?php

namespace Hyqo\Container\Test\Fixtures;

class Baz
{
    public $integer;
    public $notTyped;

    public function __construct(Foo $foo, Bar $bar, int $integer, $notTyped)
    {
        $this->integer = $integer;
        $this->notTyped = $notTyped;
    }
}
