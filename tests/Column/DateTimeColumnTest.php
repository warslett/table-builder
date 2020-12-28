<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests\Column;

use DateTime;
use WArslett\TableBuilder\Column\DateTimeColumn;
use WArslett\TableBuilder\Exception\NoValueAdapterException;
use WArslett\TableBuilder\Exception\ValueException;
use WArslett\TableBuilder\Tests\TestCase;
use Mockery as m;
use Mockery\Mock;
use WArslett\TableBuilder\ValueAdapter\ValueAdapterInterface;

class DateTimeColumnTest extends TestCase
{

    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellNoValueAdapterThrowsException()
    {
        $column = DateTimeColumn::withName('my_date');

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
        $column = DateTimeColumn::withName('my_date')
            ->valueAdapter($this->mockValueAdapter(new DateTime()));
        $cell = $column->buildTableCell([]);
        $this->assertSame(DateTimeColumn::class, $cell->getRenderingType());
    }

    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellGetsValueForRow()
    {
        $row = ['foo' => 'bar'];
        $valueAdapter = $this->mockValueAdapter(new DateTime());
        $column = DateTimeColumn::withName('my_date')
            ->valueAdapter($valueAdapter);

        $column->buildTableCell($row);

        $valueAdapter->shouldHaveReceived('getValue')->once()->with($row);
    }

    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellValueNullReturnsNull()
    {
        $column = DateTimeColumn::withName('my_date')
            ->valueAdapter($this->mockValueAdapter(null));

        $cell = $column->buildTableCell([]);

        $this->assertNull($cell->getValue());
    }

    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellValueNotDateTimeThrowsException()
    {
        $value = 'foo';
        $column = DateTimeColumn::withName('my_date')
            ->format('d/m/Y H:i')
            ->valueAdapter($this->mockValueAdapter($value));

        $this->expectException(ValueException::class);

        $column->buildTableCell([]);
    }

    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellSetsValueOnCell()
    {
        $value = new DateTime("1990-01-01 00:00:00");
        $column = DateTimeColumn::withName('my_date')
            ->format('d/m/Y H:i')
            ->valueAdapter($this->mockValueAdapter($value));

        $cell = $column->buildTableCell([]);

        $this->assertSame('01/01/1990 00:00', $cell->getValue());
    }

    /**
     * @return void
     */
    public function testBuildTableHeadingSetsNameOnHeading()
    {
        $name = 'my_column';
        $column = DateTimeColumn::withName($name);
        $heading = $column->buildTableHeading();
        $this->assertSame($name, $heading->getName());
    }

    /**
     * @return void
     */
    public function testBuildTableHeadingSetsLabelOnHeading()
    {
        $label = 'My Label';
        $column = DateTimeColumn::withName('my_date')->label($label);
        $heading = $column->buildTableHeading();
        $this->assertSame($label, $heading->getLabel());
    }

    public function testSortableSortToggleSetDoesNothing()
    {
        $column = DateTimeColumn::withName('my_name');
        $column->sortToggle('foo_bar');

        $column->sortable();

        $this->assertSame('foo_bar', $column->getSortToggle());
    }

    public function testSortableSortToggleNotSetSetsSortToggleToName()
    {
        $column = DateTimeColumn::withName('my_name');

        $column->sortable();

        $this->assertSame('my_name', $column->getSortToggle());
    }

    public function testSortableFalseSortToggleSetSetsSortToggleToNull()
    {
        $column = DateTimeColumn::withName('my_name');
        $column->sortToggle('foo_bar');

        $column->sortable(false);

        $this->assertNull($column->getSortToggle());
    }

    /**
     * @param mixed|DateTime $value
     * @return ValueAdapterInterface&Mock
     */
    private function mockValueAdapter($value): ValueAdapterInterface
    {
        $valueAdapter = m::mock(ValueAdapterInterface::class);
        $valueAdapter->shouldReceive('getValue')->andReturn($value);
        return $valueAdapter;
    }
}
