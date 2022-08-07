<?php

namespace Hyqo\Container\Resolver;

class FunctionResolver extends Resolver
{
    public function resolve(\ReflectionFunctionAbstract $function, array $arguments = []): \Generator
    {
        foreach ($function->getParameters() as $parameter) {
            $parameterName = $parameter->getName();

            if (array_key_exists($parameterName, $arguments)) {
                yield $arguments[$parameter->getName()];
            } elseif (($type = $parameter->getType()) && $this->isValidType($type)) {
                $resolvableClassname = $type->getName();

                yield $this->container->get($resolvableClassname);
            } elseif ($parameter->isDefaultValueAvailable()) {
                yield $parameter->getDefaultValue();
            } else {
                throw new \InvalidArgumentException(
                    sprintf(
                        "$%s in %s must be typed or passed",
                        $parameter->getName(),
                        $function->getName()
                    )
                );
            }
        }
    }
}
