<?php

namespace Hyqo\Container\Test;

use Hyqo\Container\Container;
use Hyqo\Container\Test\Fixtures\{Call, ClassInterface, CyclicFoo, Foo, Bar, Baz, NoConstructor, PrivateConstructor};
use Hyqo\Container\Exception\ContainerException;

use Hyqo\Container\Exception\CyclicDependencyException;

use Hyqo\Container\Exception\NotFoundException;
use JetBrains\PhpStorm\Pure;
use PHPUnit\Framework\TestCase;

use function Hyqo\Container\get;
use function Hyqo\Container\make;

class  ContainerTest extends TestCase
{
    private Container $container;

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

    public function test_make_a_class_with_not_public_constructor(): void
    {
        $this->expectException(ContainerException::class);
        $this->container->make(PrivateConstructor::class);
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
        $baz = $this->container->make(Baz::class, ['integer' => 1, 'notTyped' => 1]);

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
        $this->expectException(NotFoundException::class);

        $this->container->make('not_exists');
    }

    public function test_call_closure(): void
    {
        $closure = static function () {
            return 1;
        };

        $result = $this->container->call($closure);
        $this->assertEquals(1, $result);

        $closure = Call::staticMethod(...);
        $result = $this->container->call($closure);
        $this->assertEquals(1, $result);
    }

    public function test_call_callable_array(): void
    {
        $result = $this->container->call([Call::class, 'staticMethod']);
        $this->assertEquals(1, $result);

        $result = $this->container->call([Call::class, 'staticMethodWithRequiredParameter'], ['test' => 1]);
        $this->assertEquals(1, $result);

        $result = $this->container->call([Call::class, 'staticMethodWithDependence']);
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
        $this->expectException(ContainerException::class);

        $this->container->make(ClassInterface::class);
    }

    public function test_bind(): void
    {
        $this->container->bind(ClassInterface::class, Bar::class);

        $bar = $this->container->make(ClassInterface::class);

        $this->assertInstanceOf(ClassInterface::class, $bar);
    }

    public function test_set(): void
    {
        $object = (object)[];

        $this->container->set('object', $object);

        $this->assertEquals($object, $this->container->get('object'));
    }

    public function test_has(): void
    {
        $this->container->set('foo', (object)[]);

        $this->assertTrue($this->container->has('foo'));
        $this->assertFalse($this->container->has('bar'));
    }

    public function test_configuration(): void
    {
        $this->container->construct(Baz::class, ['integer' => 999, 'notTyped' => 'string']);

        $baz = $this->container->make(Baz::class, ['integer' => 1]);

        $this->assertEquals(1, $baz->integer);

        $baz = $this->container->get(Baz::class);

        $this->assertEquals(999, $baz->integer);
        $this->assertEquals('string', $baz->notTyped);
    }

    public function test_configuration_make_definition(): void
    {
        $this->container
            ->construct(Foo::class, ['bar' => make(Bar::class, ['optional' => 999])]);

        $foo = $this->container->make(Foo::class);

        $this->assertEquals("999", $foo->print());

        $this->assertFalse($this->container->has(Bar::class));
    }

    public function test_configuration_get_definition(): void
    {
        $this->container
            ->construct(Bar::class, ['optional' => '999'])
            ->construct(Foo::class, ['bar' => get(Bar::class)]);

        $foo = $this->container->make(Foo::class);

        $this->assertEquals("999", $foo->print());

        $this->assertTrue($this->container->has(Bar::class));
    }

    public function test_cyclic(): void
    {
        $this->expectException(CyclicDependencyException::class);

        $this->container->get(CyclicFoo::class);
    }
}
