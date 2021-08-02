<?php

namespace Hyqo\Container\Test;

function getClosure(): \Closure
{
    return static function (int $foo): int {
        return $foo;
    };
}

;
