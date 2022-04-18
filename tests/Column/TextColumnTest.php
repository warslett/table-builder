<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests\Column;

use WArslett\TableBuilder\Column\TextColumn;
use WArslett\TableBuilder\Exception\NoValueAdapterException;
use WArslett\TableBuilder\Exception\ValueException;
use WArslett\TableBuilder\Tests\TestCase;
use Mockery as m;
use Mockery\Mock;
use WArslett\TableBuilder\ValueAdapter\ValueAdapterInterface;

class TextColumnTest extends TestCase
{
    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellNoValueAdapterThrowsException()
    {
        $column = TextColumn::withName('my_action_group');

        $this->expectException(NoValueAdapterException::class);

        $column->buildTableCell([]);
    }

    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellSetsRenderingTypeOnTableCell()
    {
        $column = TextColumn::withName('my_action_group')
            ->valueAdapter($this->mockValueAdapter('foo'));
        $cell = $column->buildTableCell([]);
        $this->assertSame(TextColumn::class, $cell->getRenderingType());
    }

    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellGetsValueForRow()
    {
        $row = ['foo' => 'bar'];
        $valueAdapter = $this->mockValueAdapter('foo');
        $column = TextColumn::withName('my_action_group')
            ->valueAdapter($valueAdapter);

        $column->buildTableCell($row);

        $valueAdapter->shouldHaveReceived('getValue')->once()->with($row);
    }

    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellSetsValueOnCell()
    {
        $value = 'foo';
        $column = TextColumn::withName('my_action_group')
            ->valueAdapter($this->mockValueAdapter($value));

        $cell = $column->buildTableCell([]);

        $this->assertSame($value, $cell->getValue());
    }

    public function testSortableSortToggleSetDoesNothing()
    {
        $column = TextColumn::withName('my_name');
        $column->sortToggle('foo_bar');

        $column->sortable();

        $this->assertSame('foo_bar', $column->getSortToggle());
    }

    public function testSortableSortToggleNotSetSetsSortToggleToName()
    {
        $column = TextColumn::withName('my_name');

        $column->sortable();

        $this->assertSame('my_name', $column->getSortToggle());
    }

    public function testSortableFalseSortToggleSetSetsSortToggleToNull()
    {
        $column = TextColumn::withName('my_name');
        $column->sortToggle('foo_bar');

        $column->sortable(false);

        $this->assertNull($column->getSortToggle());
    }

    /**
     * @return void
     */
    public function testBuildTableHeadingSetsNameOnHeading()
    {
        $name = 'my_column';
        $column = TextColumn::withName($name);
        $heading = $column->buildTableHeading();
        $this->assertSame($name, $heading->getName());
    }

    /**
     * @return void
     */
    public function testBuildTableHeadingSetsLabelOnHeading()
    {
        $label = 'My Label';
        $column = TextColumn::withName('my_action_group')->label($label);
        $heading = $column->buildTableHeading();
        $this->assertSame($label, $heading->getLabel());
    }

    /**
     * @param string $value
     * @return ValueAdapterInterface&Mock
     */
    private function mockValueAdapter(string $value): ValueAdapterInterface
    {
        $valueAdapter = m::mock(ValueAdapterInterface::class);
        $valueAdapter->shouldReceive('getValue')->andReturn($value);
        return $valueAdapter;
    }
}
