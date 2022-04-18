<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests;

use WArslett\TableBuilder\Column\TextColumn;
use WArslett\TableBuilder\Exception\DataAdapterException;
use WArslett\TableBuilder\Exception\SortToggleException;
use WArslett\TableBuilder\TableCell;
use WArslett\TableBuilder\ValueAdapter\PropertyAccessAdapter;
use WArslett\TableBuilder\DataAdapter\ArrayDataAdapter;
use WArslett\TableBuilder\Exception\NoDataAdapterException;
use WArslett\TableBuilder\RequestAdapter\ArrayRequestAdapter;
use WArslett\TableBuilder\TableBuilderFactory;

class TableEventsIntegrationTest extends TestCase
{
    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testCellWithAttributeCallbackChangesCallback(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->add(TextColumn::withName('foo')
                ->valueAdapter(PropertyAccessAdapter::withPropertyPath('[foo]'))
                ->afterBuildCell(function (TableCell $cell) {
                    if ($cell->getValue() > 5) {
                        $cell->setAttribute('extra_classes', ['text-red']);
                    }
                }))
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                ['foo' => 1],
                ['foo' => 6],
            ]))
            ->handleRequest(ArrayRequestAdapter::withArray([]))
        ;

        $rows = $table->getRows();
        $this->assertSame(null, $rows[0]['foo']->getAttribute('extra_classes'));
        $this->assertSame(['text-red'], $rows[1]['foo']->getAttribute('extra_classes'));
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testCellWithValueCallbackChangesValue(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->add(TextColumn::withName('foo')
                ->valueAdapter(PropertyAccessAdapter::withPropertyPath('[foo]'))
                ->afterBuildCell(function (TableCell $cell) {
                    $cell->setValue(ucwords($cell->getValue()));
                }))
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                ['foo' => 'bar']
            ]))
            ->handleRequest(ArrayRequestAdapter::withArray([]))
        ;

        $rows = $table->getRows();
        $this->assertSame('Bar', $rows[0]['foo']->getValue());
    }
}
