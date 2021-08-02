# Container [![Packagist Version](https://img.shields.io/packagist/v/hyqo/container)](https://packagist.org/packages/hyqo/container)

Dependency injection container

<img alt="example" src="https://raw.githubusercontent.com/hyqo/assets/master/container/example.png" width="583">

## Install

```sh
composer require hyqo/container
```

## Basic usage

For example, we have class `Bar`, which depends on class `Foo`:

```php
class Foo {
}

class Bar
{
    private Foo $foo;

    public function __construct(Foo $foo)
    {
        $this->foo = $foo;
    }
}
```

Create container and object of class `Bar`:

```php
use \Hyqo\Container\Container;

$container = new Container();

$bar = $container->make(Bar::class);
```

```text
class Bar (1) {
  private Foo $foo =>
  class Foo (0) {
  }
}
```

## Untyped arguments

If class constructor has untyped arguments or type is a built-in type, you must pass an associative array of values to `make` method.

```php
class Foo {
}

class Bar
{
    private $foo;

    private $arg1;

    private $arg2;

    public function __construct(Foo $foo, int $arg1, $arg2)
    {
        $this->foo = $foo;
        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
    }
}
```

Create the object:
```php
use \Hyqo\Container\Container;

$container = new Container();

$bar = $container->make(Bar::class, ['arg1'=>1, 'arg2'=> 'value']);
```

## Interface mapping

If class constructor required interface implementation, you must bind interface to implementation that be used.

```php
interface FooInterface {
}

class Foo implements FooInterface{
}

class Bar
{
    private $foo;

    public function __construct(FooInterface $foo)
    {
        $this->foo = $foo;
    }
}
```

Bind interface and create the object:

```php
use \Hyqo\Container\Container;

$container = new Container();
$container->bind(FooInterface::class, Foo::class);

$bar = $container->make(Bar::class);
```
