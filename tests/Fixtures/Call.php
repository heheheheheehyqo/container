<?php

namespace Hyqo\Container\Test\Fixtures;

class Call
{
    public static function staticMethod(int $test = 1): int
    {
        return $test;
    }

    public static function staticMethodWithRequiredParameter(int $test): int
    {
        return $test;
    }

    public static function staticMethodWithDependence(Bar $bar): int
    {
        return $bar->print();
    }

    public function objectMethod(): int
    {
        return 1;
    }
}
