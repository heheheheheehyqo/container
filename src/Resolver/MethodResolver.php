<?php

namespace Hyqo\Container\Resolver;

use Hyqo\Container\Definition\DefinitionInterface;
use Hyqo\Container\Exception\ContainerException;

class MethodResolver extends Resolver
{
    public function resolve(\ReflectionMethod $reflectionMethod, array $arguments = []): \Generator
    {
        $classname = $reflectionMethod->getDeclaringClass()->getName();

        $configuration = $this->container->getConfiguration($classname);

        foreach ($reflectionMethod->getParameters() as $parameter) {
            $parameterName = $parameter->getName();

            if (array_key_exists($parameterName, $arguments)) {
                yield $arguments[$parameterName];
            } elseif (array_key_exists($parameterName, $configuration)) {
                if ($configuration[$parameterName] instanceof DefinitionInterface) {
                    yield $configuration[$parameterName]->resolve($this->container);
                } else {
                    yield $configuration[$parameterName];
                }
            } elseif (($type = $parameter->getType()) && $this->isValidType($type)) {
                $resolvableClassname = $type->getName();

                yield $this->container->get($resolvableClassname);
            } elseif ($parameter->isDefaultValueAvailable()) {
                yield $parameter->getDefaultValue();
            } else {
                throw new ContainerException(
                    sprintf(
                        "$%s in %s::%s must be typed or passed",
                        $parameter->getName(),
                        $classname,
                        $reflectionMethod->getName()
                    )
                );
            }
        }
    }
}
