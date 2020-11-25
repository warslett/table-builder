<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests;

use WArslett\TableBuilder\ActionBuilder;
use WArslett\TableBuilder\Tests\TestCase;
use Mockery as m;
use Mockery\Mock;
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
        $builder->setLabel($label);

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
        $builder->setRoute($route);

        $action = $builder->buildAction([]);
        $actual = $action->getRoute();

        $this->assertSame($route, $actual);
    }

    public function testBuildActionWithRouteWithRouteParameterGetsValueForRouteParameter()
    {
        $route = 'my_route';
        $row = new \stdClass();
        $builder = ActionBuilder::withName('foo');
        $adapter = $this->mockValueAdapter(123);
        $builder->setRoute($route, [$adapter]);

        $builder->buildAction($row);

        $adapter->shouldHaveReceived('getValue')->once()->with($row);
    }

    public function testBuildActionWithRouteWithRouteParameterMapsValueToParameters()
    {
        $route = 'my_route';
        $row = new \stdClass();
        $builder = ActionBuilder::withName('foo');
        $value = 123;
        $adapter = $this->mockValueAdapter($value);
        $builder->setRoute($route, ['id' => $adapter]);

        $action = $builder->buildAction($row);

        $this->assertSame(['id' => $value], $action->getRouteParams());
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
