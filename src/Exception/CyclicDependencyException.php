<?php

namespace Hyqo\Container\Exception;

use Psr\Container\ContainerExceptionInterface;

class CyclicDependencyException extends \RuntimeException implements ContainerExceptionInterface
{

}
