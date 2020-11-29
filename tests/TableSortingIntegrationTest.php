<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests;

use WArslett\TableBuilder\Action;
use WArslett\TableBuilder\ActionBuilder;
use WArslett\TableBuilder\Column\ActionGroupColumn;
use WArslett\TableBuilder\Column\TextColumn;
use WArslett\TableBuilder\Exception\DataAdapterException;
use WArslett\TableBuilder\Exception\SortToggleException;
use WArslett\TableBuilder\RequestAdapter\RequestAdapterInterface;
use WArslett\TableBuilder\ValueAdapter\PropertyAccessAdapter;
use WArslett\TableBuilder\DataAdapter\ArrayDataAdapter;
use WArslett\TableBuilder\Exception\NoValueAdapterException;
use WArslett\TableBuilder\Exception\NoDataAdapterException;
use WArslett\TableBuilder\RequestAdapter\ArrayRequestAdapter;
use WArslett\TableBuilder\TableBuilderFactory;

class TableSortingIntegrationTest extends TestCase
{

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testSortToggleSortsData(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->addColumn(TextColumn::withName('foo_column')
                ->setValueAdapter(PropertyAccessAdapter::withPropertyPath('[foo]'))
                ->setSortToggle('foo_toggle'))
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                ['foo' => 3],
                ['foo' => 4],
                ['foo' => 2],
                ['foo' => 1],
            ])->mapSortToggle('foo_toggle', fn($a, $b) => $a['foo'] < $b['foo'] ? -1 : 1))
            ->handleRequest(ArrayRequestAdapter::withArray(['user_table' => ['sort_column' => 'foo_column']]))
        ;

        $this->assertSame('foo_column', $table->getSortColumnName());

        $rows = $table->getRows();
        $this->assertSame(1, $rows[0]['foo_column']->getValue());
        $this->assertSame(2, $rows[1]['foo_column']->getValue());
        $this->assertSame(3, $rows[2]['foo_column']->getValue());
        $this->assertSame(4, $rows[3]['foo_column']->getValue());
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testSortToggleSortsDataDescending(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->addColumn(TextColumn::withName('foo_column')
                ->setValueAdapter(PropertyAccessAdapter::withPropertyPath('[foo]'))
                ->setSortToggle('foo_toggle'))
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                ['foo' => 3],
                ['foo' => 4],
                ['foo' => 2],
                ['foo' => 1],
            ])->mapSortToggle('foo_toggle', function (array $a, array $b) {
                return $a['foo'] < $b['foo'] ? -1 : 1;
            }))
            ->handleRequest(ArrayRequestAdapter::withArray(['user_table' => [
                'sort_column' => 'foo_column',
                'sort_dir' => RequestAdapterInterface::SORT_DESCENDING
            ]]))
        ;

        $this->assertTrue($table->isSortedDescending());

        $rows = $table->getRows();
        $this->assertSame(4, $rows[0]['foo_column']->getValue());
        $this->assertSame(3, $rows[1]['foo_column']->getValue());
        $this->assertSame(2, $rows[2]['foo_column']->getValue());
        $this->assertSame(1, $rows[3]['foo_column']->getValue());
    }

    /**
     * @return void
     * @throws SortToggleException
     */
    public function testSortToggleSetDataAdapterCannotSortThrowsException(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();

        $this->expectException(SortToggleException::class);

        $tableBuilderFactory->createTableBuilder()
            ->addColumn(TextColumn::withName('foo_column')
                ->setValueAdapter(PropertyAccessAdapter::withPropertyPath('[foo]'))
                ->setSortToggle('foo_toggle'))
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([]))
        ;
    }
}
