<?php

namespace Hyqo\Container\Test\Fixtures;

class Call
{
    public static function staticMethod()
    {
        return 1;
    }

    public static function staticMethodWithRequiredParameter(int $test): int
    {
        return $test;
    }

    public function objectMethod()
    {
        return 1;
    }
}
