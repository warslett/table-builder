<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests\Column;

use WArslett\TableBuilder\Action;
use WArslett\TableBuilder\ActionBuilderInterface;
use WArslett\TableBuilder\ActionGroup;
use WArslett\TableBuilder\Column\ActionGroupColumn;
use WArslett\TableBuilder\Exception\NoValueAdapterException;
use WArslett\TableBuilder\Exception\ValueException;
use WArslett\TableBuilder\Tests\TestCase;
use Mockery as m;
use Mockery\Mock;

class ActionGroupColumnTest extends TestCase
{
    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellSetsRenderingTypeOnTableCell()
    {
        $column = ActionGroupColumn::withName('my_action_group');
        $cell = $column->buildTableCell([]);
        $this->assertSame(ActionGroupColumn::class, $cell->getRenderingType());
    }

    /**
     * @return void
     */
    public function testBuildTableHeadingSetsLabelOnHeading()
    {
        $label = 'My Label';
        $column = ActionGroupColumn::withName('my_action_group')->label($label);
        $heading = $column->buildTableHeading();
        $this->assertSame($label, $heading->getLabel());
    }

    /**
     * @return void
     */
    public function testBuildTableCellSetsNameOnHeading()
    {
        $name = 'my_column';
        $column = ActionGroupColumn::withName($name);
        $heading = $column->buildTableHeading();
        $this->assertSame($name, $heading->getName());
    }

    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellSetsValueToActionGroup()
    {
        $column = ActionGroupColumn::withName('my_action_group');
        $cell = $column->buildTableCell([]);
        $value = $cell->getValue();
        $this->assertInstanceOf(ActionGroup::class, $value);
    }

    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellOneActionBuilderBuildsOneAction()
    {
        $actionName = 'foo';
        $action = new Action('Foo');
        $column = ActionGroupColumn::withName('my_action_group')
            ->add($this->mockActionBuilder($actionName, true, $action))
        ;
        $cell = $column->buildTableCell([]);

        /** @var ActionGroup $value */
        $value = $cell->getValue();
        $actions = $value->getActions();

        $this->assertSame(1, count($actions));
        $this->assertArrayHasKey($actionName, $actions);
        $this->assertSame($action, $actions[$actionName]);
    }

    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellActionBuilderConditionDisallowedExcludesAction()
    {
        $actionName = 'foo';
        $action = new Action('Foo');
        $column = ActionGroupColumn::withName('my_action_group')
            ->add($this->mockActionBuilder($actionName, false, $action))
        ;
        $cell = $column->buildTableCell([]);

        /** @var ActionGroup $value */
        $value = $cell->getValue();
        $actions = $value->getActions();

        $this->assertEmpty($actions);
    }

    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellTwoActionBuilderBuildsTwoAction()
    {
        $column = ActionGroupColumn::withName('my_action_group')
            ->add($this->mockActionBuilder('foo', true, new Action('Foo')))
            ->add($this->mockActionBuilder('bar', true, new Action('Bar')))
        ;
        $cell = $column->buildTableCell([]);

        /** @var ActionGroup $value */
        $value = $cell->getValue();
        $actions = $value->getActions();

        $this->assertSame(2, count($actions));
    }

    public function testSortableSortToggleSetDoesNothing()
    {
        $column = ActionGroupColumn::withName('my_name');
        $column->sortToggle('foo_bar');

        $column->sortable();

        $this->assertSame('foo_bar', $column->getSortToggle());
    }

    public function testSortableSortToggleNotSetSetsSortToggleToName()
    {
        $column = ActionGroupColumn::withName('my_name');

        $column->sortable();

        $this->assertSame('my_name', $column->getSortToggle());
    }

    public function testSortableFalseSortToggleSetSetsSortToggleToNull()
    {
        $column = ActionGroupColumn::withName('my_name');
        $column->sortToggle('foo_bar');

        $column->sortable(false);

        $this->assertNull($column->getSortToggle());
    }

    /**
     * @param string $actionName
     * @param bool $isAllowed
     * @param Action $action
     * @return ActionBuilderInterface&Mock
     */
    private function mockActionBuilder(string $actionName, bool $isAllowed, Action $action): ActionBuilderInterface
    {
        $actionBuilder = m::mock(ActionBuilderInterface::class);
        $actionBuilder->shouldReceive('getName')->andReturn($actionName);
        $actionBuilder->shouldReceive('isAllowedFor')->andReturn($isAllowed);
        $actionBuilder->shouldReceive('buildAction')->andReturn($action);
        return $actionBuilder;
    }
}
