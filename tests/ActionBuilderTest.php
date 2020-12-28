<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests;

use stdClass;
use WArslett\TableBuilder\ActionBuilder;
use Mockery as m;
use Mockery\Mock;
use WArslett\TableBuilder\Exception\ValueAdapterException;
use WArslett\TableBuilder\ValueAdapter\ValueAdapterInterface;

class ActionBuilderTest extends TestCase
{

    public function testBuildActionNoLabelSetsNameAsLabel()
    {
        $name = 'foo';
        $builder = new ActionBuilder($name);

        $action = $builder->buildAction([]);
        $label = $action->getLabel();

        $this->assertSame($name, $label);
    }

    public function testBuildActionWithLabelSetsLabel()
    {
        $label = 'bar';
        $builder = ActionBuilder::withName('foo');
        $builder->label($label);

        $action = $builder->buildAction([]);
        $actual = $action->getLabel();

        $this->assertSame($label, $actual);
    }

    public function testBuildActionNoRouteRouteIsNull()
    {
        $builder = ActionBuilder::withName('foo');

        $action = $builder->buildAction([]);
        $route = $action->getRoute();

        $this->assertNull($route);
    }

    public function testBuildActionWithRouteSetsRoute()
    {
        $route = 'my_route';
        $builder = ActionBuilder::withName('foo');
        $builder->route($route);

        $action = $builder->buildAction([]);
        $actual = $action->getRoute();

        $this->assertSame($route, $actual);
    }

    public function testBuildActionWithRouteWithRouteParameterGetsValueForRouteParameter()
    {
        $route = 'my_route';
        $row = new stdClass();
        $builder = ActionBuilder::withName('foo');
        $adapter = $this->mockValueAdapter(123);
        $builder->route($route, [$adapter]);

        $builder->buildAction($row);

        $adapter->shouldHaveReceived('getValue')->once()->with($row);
    }

    public function testBuildActionWithRouteWithRouteParameterMapsValueAdapterToParameters()
    {
        $route = 'my_route';
        $row = new stdClass();
        $builder = ActionBuilder::withName('foo');
        $value = 123;
        $adapter = $this->mockValueAdapter($value);
        $builder->route($route, ['id' => $adapter]);

        $action = $builder->buildAction($row);

        $this->assertSame(['id' => $value], $action->getRouteParams());
    }

    public function testBuildActionWithRouteWithRouteParameterCallbackToParameters()
    {
        $route = 'my_route';
        $row = new stdClass();
        $builder = ActionBuilder::withName('foo');
        $value = 123;
        $builder->route($route, ['id' => fn() => $value]);

        $action = $builder->buildAction($row);

        $this->assertSame(['id' => $value], $action->getRouteParams());
    }

    public function testBuildActionWithRouteWithRouteParameterPropertyPathToParameters()
    {
        $route = 'my_route';
        $row = new stdClass();
        $value = 123;
        $row->key = $value;
        $builder = ActionBuilder::withName('foo');
        $builder->route($route, ['id' => 'key']);

        $action = $builder->buildAction($row);

        $this->assertSame(['id' => $value], $action->getRouteParams());
    }

    public function testRouteWithInvalidParameterThrowsException()
    {
        $route = 'my_route';
        $builder = ActionBuilder::withName('foo');

        $this->expectException(ValueAdapterException::class);

        $builder->route($route, ['id' => new stdClass()]);
    }

    public function testBuildActionWithAttribute()
    {
        $extraClasses = ['btn-danger'];
        $builder = ActionBuilder::withName('delete')
            ->attribute('extra_classes', $extraClasses);

        $action = $builder->buildAction([]);

        $attribute = $action->getAttribute('extra_classes');
        $this->assertSame($extraClasses, $attribute);
    }

    public function testBuildActionWithFalseConditionIsDisallowed()
    {
        $builder = ActionBuilder::withName('delete')
            ->condition(fn($row) => false);

        $isAllowed = $builder->isAllowedFor([]);

        $this->assertFalse($isAllowed);
    }

    public function testBuildActionWithTrueConditionIsAllowed()
    {
        $builder = ActionBuilder::withName('delete')
            ->condition(fn($row) => true);

        $isAllowed = $builder->isAllowedFor([]);

        $this->assertTrue($isAllowed);
    }

    public function testBuildActionNoConditionIsAllowed()
    {
        $builder = ActionBuilder::withName('delete');

        $isAllowed = $builder->isAllowedFor([]);

        $this->assertTrue($isAllowed);
    }

    /**
     * @param $value
     * @return ValueAdapterInterface&Mock
     */
    private function mockValueAdapter($value)
    {
        $valueAdapter = m::mock(ValueAdapterInterface::class);
        $valueAdapter->shouldReceive('getValue')->andReturn($value);
        return $valueAdapter;
    }
}
