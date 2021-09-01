<?php

namespace Hyqo\Container\Test;

use Hyqo\Container\Container;
use Hyqo\Container\Test\Fixtures\{Call, ClassInterface, Foo, Bar, Baz, NoConstructor};

class  ContainerTest extends \PHPUnit\Framework\TestCase
{
    /** @var Container */
    private $container;

    protected function setUp(): void
    {
        $this->container = new Container();
    }

    public function test_can_create_container(): void
    {
        $this->assertInstanceOf(Container::class, $this->container);
    }

    public function test_make_a_service(): void
    {
        $bar = $this->container->make(Bar::class);

        $this->assertInstanceOf(Bar::class, $bar);
    }

    public function test_make_a_class_without_constructor(): void
    {
        $noConstructor = $this->container->make(NoConstructor::class);

        $this->assertInstanceOf(NoConstructor::class, $noConstructor);
    }

    public function test_get_with_inject(): void
    {
        $foo = $this->container->get(Foo::class);

        $this->assertInstanceOf(Foo::class, $foo);
    }

    public function test_make_with_parameters(): void
    {
        $baz = $this->container->make(Baz::class, ['test' => 1, 'a' => 1]);

        $this->assertInstanceOf(Baz::class, $baz);
    }

    public function test_make_class_without_passing_a_required_parameter(): void
    {
        $this->expectExceptionMessage('must be typed');

        $this->container->make(Baz::class, ['test' => 1]);
    }

    public function test_call_closure_without_passing_a_required_parameter(): void
    {
        $this->expectExceptionMessage('must be typed');

        $this->container->call(getClosure());
    }

    public function test_class_not_exists_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->container->make('not_exists');
    }

    public function test_call_closure(): void
    {
        $closure = function () {
            return 1;
        };

        $result = $this->container->call($closure);

        $this->assertEquals(1, $result);
    }

    public function test_call_callable_array(): void
    {
        $result = $this->container->call([Call::class, 'staticMethod']);

        $this->assertEquals(1, $result);
    }

    public function test_call_callable_object(): void
    {
        $object = new Call();

        $result = $this->container->call([$object, 'objectMethod']);

        $this->assertEquals(1, $result);
    }

    public function test_call_callable_string(): void
    {
        $result = $this->container->call(sprintf('%s::%s', Call::class, 'staticMethod'));

        $this->assertEquals(1, $result);
    }

    public function test_make_interface_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->container->make(ClassInterface::class);
    }

    public function test_bind(): void
    {
        $this->container->bind(ClassInterface::class, Bar::class);

        $bar = $this->container->make(ClassInterface::class);

        $this->assertInstanceOf(Bar::class, $bar);
    }

    public function test_set(): void
    {
        $object = (object)[];

        $this->container->set('object', $object);

        $this->assertEquals($object, $this->container->get('object'));
    }
}
