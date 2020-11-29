<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests\Column;

use WArslett\TableBuilder\Column\BooleanColumn;
use WArslett\TableBuilder\Exception\NoValueAdapterException;
use WArslett\TableBuilder\Exception\ValueException;
use WArslett\TableBuilder\Tests\TestCase;
use Mockery as m;
use Mockery\Mock;
use WArslett\TableBuilder\ValueAdapter\ValueAdapterInterface;

class BooleanColumnTest extends TestCase
{

    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellNoValueAdapterThrowsException()
    {
        $actionGroupColumn = BooleanColumn::withName('my_action_group');

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
        $actionGroupColumn = BooleanColumn::withName('my_action_group')
            ->setValueAdapter($this->mockValueAdapter(false));
        $cell = $actionGroupColumn->buildTableCell([]);
        $this->assertSame(BooleanColumn::class, $cell->getRenderingType());
    }

    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellNonBooleanValueThrowsException()
    {
        $row = ['foo' => 'bar'];
        $valueAdapter = $this->mockValueAdapter('foo');
        $actionGroupColumn = BooleanColumn::withName('my_action_group')
            ->setValueAdapter($valueAdapter);

        $this->expectException(ValueException::class);

        $actionGroupColumn->buildTableCell($row);
    }

    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellGetsValueForRow()
    {
        $row = ['foo' => 'bar'];
        $valueAdapter = $this->mockValueAdapter(false);
        $actionGroupColumn = BooleanColumn::withName('my_action_group')
            ->setValueAdapter($valueAdapter);

        $actionGroupColumn->buildTableCell($row);

        $valueAdapter->shouldHaveReceived('getValue')->once()->with($row);
    }

    /**
     * @return void
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function testBuildTableCellSetsValueOnCell()
    {
        $value = false;
        $actionGroupColumn = BooleanColumn::withName('my_action_group')
            ->setValueAdapter($this->mockValueAdapter($value));

        $cell = $actionGroupColumn->buildTableCell([]);

        $this->assertSame($value, $cell->getValue());
    }

    /**
     * @return void
     */
    public function testBuildTableHeadingSetsNameOnHeading()
    {
        $name = 'my_column';
        $actionGroupColumn = BooleanColumn::withName($name);
        $heading = $actionGroupColumn->buildTableHeading();
        $this->assertSame($name, $heading->getName());
    }

    /**
     * @return void
     */
    public function testBuildTableHeadingSetsLabelOnHeading()
    {
        $label = 'My Label';
        $actionGroupColumn = BooleanColumn::withName('my_action_group')->setLabel($label);
        $heading = $actionGroupColumn->buildTableHeading();
        $this->assertSame($label, $heading->getLabel());
    }

    /**
     * @param mixed $value
     * @return ValueAdapterInterface&Mock
     */
    private function mockValueAdapter($value): ValueAdapterInterface
    {
        $valueAdapter = m::mock(ValueAdapterInterface::class);
        $valueAdapter->shouldReceive('getValue')->andReturn($value);
        return $valueAdapter;
    }
}
