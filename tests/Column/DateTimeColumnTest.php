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
        $actionGroupColumn = DateTimeColumn::withName('my_date');

        $this->expectException(NoValueAdapterException::class);

        $actionGroupColumn->buildTableCell([]);
    }

    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellSetsRenderingTypeOnTableCell()
    {
        $actionGroupColumn = DateTimeColumn::withName('my_date')
            ->setValueAdapter($this->mockValueAdapter(new DateTime()));
        $cell = $actionGroupColumn->buildTableCell([]);
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
        $actionGroupColumn = DateTimeColumn::withName('my_date')
            ->setValueAdapter($valueAdapter);

        $actionGroupColumn->buildTableCell($row);

        $valueAdapter->shouldHaveReceived('getValue')->once()->with($row);
    }

    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellValueNullReturnsNull()
    {
        $actionGroupColumn = DateTimeColumn::withName('my_date')
            ->setValueAdapter($this->mockValueAdapter(null));

        $cell = $actionGroupColumn->buildTableCell([]);

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
        $actionGroupColumn = DateTimeColumn::withName('my_date')
            ->setDateTimeFormat('d/m/Y H:i')
            ->setValueAdapter($this->mockValueAdapter($value));

        $this->expectException(ValueException::class);

        $actionGroupColumn->buildTableCell([]);
    }

    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellSetsValueOnCell()
    {
        $value = new DateTime("1990-01-01 00:00:00");
        $actionGroupColumn = DateTimeColumn::withName('my_date')
            ->setDateTimeFormat('d/m/Y H:i')
            ->setValueAdapter($this->mockValueAdapter($value));

        $cell = $actionGroupColumn->buildTableCell([]);

        $this->assertSame('01/01/1990 00:00', $cell->getValue());
    }

    /**
     * @return void
     */
    public function testBuildTableHeadingSetsNameOnHeading()
    {
        $name = 'my_column';
        $actionGroupColumn = DateTimeColumn::withName($name);
        $heading = $actionGroupColumn->buildTableHeading();
        $this->assertSame($name, $heading->getName());
    }

    /**
     * @return void
     */
    public function testBuildTableHeadingSetsLabelOnHeading()
    {
        $label = 'My Label';
        $actionGroupColumn = DateTimeColumn::withName('my_date')->setLabel($label);
        $heading = $actionGroupColumn->buildTableHeading();
        $this->assertSame($label, $heading->getLabel());
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
