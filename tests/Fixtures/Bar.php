<?php

namespace Hyqo\Container\Test\Fixtures;

class Bar implements ClassInterface
{
    protected int $integer;

    public function __construct(int $optional = 1)
    {
        $this->integer = $optional;
    }

    public function print(): string
    {
        return (string)$this->integer;
    }
}
