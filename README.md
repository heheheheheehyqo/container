# Container

![Packagist Version](https://img.shields.io/packagist/v/hyqo/container?style=flat-square)
![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/hyqo/container?style=flat-square)
![GitHub Workflow Status](https://img.shields.io/github/workflow/status/hyqo/container/run-tests?style=flat-square)

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

If class constructor has untyped arguments or type is a built-in type, you must pass an associative array of values
to `make` method.

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

Create object:

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

## Constructor configuration

```php
class Bar
{
    public $string;

    public function __construct(string $string)
    {
        $this->string = $string;
    }
}

class Foo {
    private $bar;

    public function __construct(Bar $bar)
    {
        $this->bar = $bar;
    }
    
    public function print() {
        echo $this->bar->string;
    }
}
```

You can specify object of `Bar`

```php
use \Hyqo\Container\Container;

$container = new Container();
$container->construct(Foo::class, ['bar'=> new Bar('Hello, world!')]);

$container->make(Foo::class)->print(); //Hello, world!
```

Or you can specify arguments of `Bar` to create an object on demand

```php
use \Hyqo\Container\Container;

$container = new Container();
$container->construct(Bar::class, ['string'=>'Hello, world!']);

$container->make(Foo::class)->print(); //Hello, world!
```

Another way

```php
use \Hyqo\Container\Container;

use function \Hyqo\Container\make;

$container = new Container();
$container->construct(Foo::class, ['bar'=> make(Bar::class, ['string'=>'Hello, world!')])]);

$container->make(Foo::class)->print(); //Hello, world!
```
