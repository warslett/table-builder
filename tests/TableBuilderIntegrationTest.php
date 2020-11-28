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

class TableBuilderIntegrationTest extends TestCase
{

    /**
     * @return void
     */
    public function testNoRequestReturnsEmptyTable(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->buildTable('user_table')
        ;
        $this->assertEmpty($table->getHeadings());
        $this->assertEmpty($table->getRows());
    }

    /**
     * @return void
     */
    public function testSetsNameOnTable(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $name = 'user_table';
        $table = $tableBuilderFactory->createTableBuilder()
            ->buildTable($name)
        ;
        $this->assertSame($name, $table->getName());
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     */
    public function testHandlesRequestWithNoDataAdapterThrowsException(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $name = 'user_table';

        $this->expectException(NoDataAdapterException::class);

        $tableBuilderFactory->createTableBuilder()
            ->buildTable($name)
            ->handleRequest(ArrayRequestAdapter::withArray([]))
        ;
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testOneRowOfDataNoColumnsReturnsArrayContainingOneEmptyArray(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                ['foo' => 'bar']
            ]))
            ->handleRequest(ArrayRequestAdapter::withArray([]))
        ;

        $rows = $table->getRows();
        $this->assertArrayHasKey(0, $rows);
        $this->assertArrayNotHasKey(1, $rows);
        $this->assertSame([], $rows[0]);
    }

    /**
     * @return void
     */
    public function testOneColumnOneHeading(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $columnName = 'foo';
        $table = $tableBuilderFactory->createTableBuilder()
            ->addColumn(TextColumn::withName($columnName))
            ->buildTable('user_table')
        ;

        $headings = $table->getHeadings();
        $this->assertSame(1, count($headings));
        $this->assertArrayHasKey($columnName, $headings);
    }

    /**
     * @return void
     */
    public function testColumnNameIsIncludedOnHeading(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $columnName = 'foo';

        $table = $tableBuilderFactory->createTableBuilder()
            ->addColumn(TextColumn::withName($columnName))
            ->buildTable('user_table')
        ;

        $headings = $table->getHeadings();
        $this->assertSame($columnName, $headings[$columnName]->getName());
    }

    /**
     * @return void
     */
    public function testColumnLabelIsIncludedOnHeading(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $columnName = 'foo';
        $columnLabel = 'Foo Heading';

        $table = $tableBuilderFactory->createTableBuilder()
            ->addColumn(TextColumn::withName($columnName)->setLabel($columnLabel))
            ->buildTable('user_table')
        ;

        $headings = $table->getHeadings();
        $this->assertSame($columnLabel, $headings[$columnName]->getLabel());
    }

    /**
     * @return void
     */
    public function testNoColumnLabelNameIsIncludedOnHeading(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $columnName = 'foo';

        $table = $tableBuilderFactory->createTableBuilder()
            ->addColumn(TextColumn::withName($columnName))
            ->buildTable('user_table')
        ;

        $headings = $table->getHeadings();
        $this->assertSame($columnName, $headings[$columnName]->getLabel());
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testOneRowOfDataOneColumnNoAdapterHandleRequestThrowsException(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();

        $this->expectException(NoValueAdapterException::class);

        $tableBuilderFactory->createTableBuilder()
            ->addColumn(TextColumn::withName('foo'))
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                ['foo' => 'bar']
            ]))
            ->handleRequest(ArrayRequestAdapter::withArray([]))
        ;
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testOneRowOfDataOneColumnMapsRowToCell(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->addColumn(TextColumn::withName('foo')
                ->setValueAdapter(PropertyAccessAdapter::withPropertyPath('[foo]')))
            ->setDefaultRowsPerPage(1)
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                ['foo' => 'bar', 'baz' => 'qux']
            ]))
            ->handleRequest(ArrayRequestAdapter::withArray([]))
        ;

        $rows = $table->getRows();
        $this->assertArrayHasKey(0, $rows);
        $this->assertSame(TextColumn::class, $rows[0]['foo']->getRenderingType());
        $this->assertSame('bar', $rows[0]['foo']->getValue());
    }

    /**
     * @return void
     */
    public function testOneRowPerPageHasOneRowsPerPage(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->setDefaultRowsPerPage(1)
            ->buildTable('user_table')
        ;

        $this->assertSame(1, $table->getRowsPerPage());
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testTwoRowsOfDataOneRowPerPageHasTwoTotalRows(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->setDefaultRowsPerPage(1)
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                ['foo' => 'bar'],
                ['foo' => 'baz']
            ]))
            ->handleRequest(ArrayRequestAdapter::withArray([]))
        ;

        $this->assertSame(1, $table->getRowsPerPage());
        $this->assertSame(2, $table->getTotalRows());
        $this->assertSame(1, $table->getPageNumber());
        $this->assertSame(2, $table->getTotalPages());
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testEmptyRequestPageNumber1(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->setDefaultRowsPerPage(1)
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([]))
            ->handleRequest(ArrayRequestAdapter::withArray([]))
        ;

        $this->assertSame(1, $table->getPageNumber());
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testTwoRowsOfDataOneRowPerPageHasTwoTotalPages(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->setDefaultRowsPerPage(1)
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                ['foo' => 'bar'],
                ['foo' => 'baz']
            ]))
            ->handleRequest(ArrayRequestAdapter::withArray([]))
        ;

        $this->assertSame(2, $table->getTotalPages());
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testTwoRowsOfDataOneRowPerPageHasOneRowInTable(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->setDefaultRowsPerPage(1)
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                ['foo' => 'bar'],
                ['foo' => 'baz']
            ]))
            ->handleRequest(ArrayRequestAdapter::withArray([]))
        ;

        $this->assertArrayHasKey(0, $table->getRows());
        $this->assertArrayNotHasKey(1, $table->getRows());
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testTwoRowsOfDataOneRowPerPageRequestPageOneMapsFirstResultColumn(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->addColumn(TextColumn::withName('foo')
                ->setValueAdapter(PropertyAccessAdapter::withPropertyPath('[foo]')))
            ->setDefaultRowsPerPage(1)
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                ['foo' => 'bar'],
                ['foo' => 'baz']
            ]))
            ->handleRequest(ArrayRequestAdapter::withArray(['user_table' => ['page' => 1]]))
        ;

        $this->assertArrayHasKey(0, $table->getRows());
        $this->assertSame('bar', $table->getRows()[0]['foo']->getValue());
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testTwoRowsOfDataOneRowPerPageRequestPageTwoMapsSecondResultColumn(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->addColumn(TextColumn::withName('foo')
                ->setValueAdapter(PropertyAccessAdapter::withPropertyPath('[foo]')))
            ->setDefaultRowsPerPage(1)
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                ['foo' => 'bar'],
                ['foo' => 'baz']
            ]))
            ->handleRequest(ArrayRequestAdapter::withArray(['user_table' => ['page' => 2]]))
        ;

        $this->assertArrayHasKey(0, $table->getRows());
        $this->assertEquals('baz', $table->getRows()[0]['foo']->getValue());
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testThreeRowsOfDataTwoRowPerPageRequestPageOneHasTwoRowsInTable(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->setDefaultRowsPerPage(2)
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                ['foo' => 'bar'],
                ['foo' => 'baz'],
                ['foo' => 'qux']
            ]))
            ->handleRequest(ArrayRequestAdapter::withArray(['user_table' => ['page' => 1]]))
        ;

        $this->assertArrayHasKey(0, $table->getRows());
        $this->assertArrayHasKey(1, $table->getRows());
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testThreeRowsOfDataTwoRowPerPageRequestPageTwoHasOneRowInTable(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->setDefaultRowsPerPage(1)
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                ['foo' => 'bar'],
                ['foo' => 'baz'],
                ['foo' => 'qux']
            ]))
            ->handleRequest(ArrayRequestAdapter::withArray(['user_table' => ['page' => 2]]))
        ;

        $this->assertArrayHasKey(0, $table->getRows());
        $this->assertArrayNotHasKey(1, $table->getRows());
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testThreeRowsOfDataDefaultTwoRowsPerPageRequestOneRowPerPageOneRowPerPage(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->setDefaultRowsPerPage(2)
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                ['foo' => 'bar'],
                ['foo' => 'baz'],
                ['foo' => 'qux']
            ]))
            ->handleRequest(ArrayRequestAdapter::withArray(['user_table' => ['rows_per_page' => 1]]))
        ;

        $this->assertSame(1, $table->getRowsPerPage());
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testThreeRowsOfDataDefaultTwoRowsPerPageRequestOneRowPerPageOneRowInTable(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->setDefaultRowsPerPage(2)
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                ['foo' => 'bar'],
                ['foo' => 'baz'],
                ['foo' => 'qux']
            ]))
            ->handleRequest(ArrayRequestAdapter::withArray(['user_table' => ['rows_per_page' => 1]]))
        ;

        $this->assertArrayHasKey(0, $table->getRows());
        $this->assertArrayNotHasKey(1, $table->getRows());
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testThreeRowsOfDataMaxTwoRowsPerPageRequestThreeRowsPerPageHasTwoRowsPerPage(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->setMaxRowsPerPage(2)
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                ['foo' => 'bar'],
                ['foo' => 'baz'],
                ['foo' => 'qux']
            ]))
            ->handleRequest(ArrayRequestAdapter::withArray(['user_table' => ['rows_per_page' => 3]]))
        ;

        $this->assertSame(2, $table->getRowsPerPage());
    }

    /**
     * @return void
     */
    public function testSetRowsPerPageOptions(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $rowsPerPageOptions = [10, 20, 50];
        $table = $tableBuilderFactory->createTableBuilder()
            ->setRowsPerPageOptions($rowsPerPageOptions)
            ->buildTable('user_table')
        ;

        $this->assertSame($rowsPerPageOptions, $table->getRowsPerPageOptions());
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testTableGetParamsReturnsRequestParams(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([]))
            ->handleRequest(ArrayRequestAdapter::withArray(['foo' => 'bar']))
        ;

        $params = $table->getParams();
        $this->assertArrayHasKey('foo', $params);
        $this->assertSame('bar', $params['foo']);
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testTableGetParamsMergesTableParams(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->setDefaultRowsPerPage(5)
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([]))
            ->handleRequest(ArrayRequestAdapter::withArray(['user_table' => [
                'sort_column' => 'foo',
                'sort_dir' => 'desc'
            ]]))
        ;

        $params = $table->getParams();
        $this->assertArrayHasKey('user_table', $params);
        $this->assertSame([
            'page' => 1,
            'rows_per_page' => 5,
            'sort_column' => 'foo',
            'sort_dir' => 'desc'
        ], $params['user_table']);
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testTableGetParamsMergesInputParamsWithTableParams(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->setDefaultRowsPerPage(5)
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([]))
            ->handleRequest(ArrayRequestAdapter::withArray(['foo' => 'bar']))
        ;

        $params = $table->getParams(['page' => 2]);
        $this->assertArrayHasKey('user_table', $params);
        $this->assertSame([
            'page' => 2,
            'rows_per_page' => 5,
            'sort_column' => null,
            'sort_dir' => null,
        ], $params['user_table']);
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testTableQueryParamNotArrayIgnores(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->setDefaultRowsPerPage(10)
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([]))
            ->handleRequest(ArrayRequestAdapter::withArray(['user_table' => 'foo']))
        ;

        $this->assertSame($table->getParams(), [
            'user_table' => [
                'page' => 1,
                'rows_per_page' => 10,
                'sort_column' => null,
                'sort_dir' => null
            ]
        ]);
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testActionGroupColumnWithActionOneRowBuildsAction(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $columnName = 'actions';
        $actionName = 'delete';
        $actionLabel = "Delete";
        $actionRoute = 'my_route';
        $paramName = 'route_id';
        $paramValue = 123;
        $table = $tableBuilderFactory->createTableBuilder()
            ->addColumn(ActionGroupColumn::withName($columnName)
                ->addActionBuilder(ActionBuilder::withName($actionName)
                    ->setLabel($actionLabel)
                    ->setRoute($actionRoute, [
                        $paramName => PropertyAccessAdapter::withPropertyPath('[id]')
                    ])))
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                ['id' => $paramValue]
            ]))
            ->handleRequest(ArrayRequestAdapter::withArray([]))
        ;
        $actions = $table->getRows()[0][$columnName]->getValue()->getActions();

        $this->assertArrayHasKey($actionName, $actions);
        /** @var Action $action */
        $action = $actions[$actionName];
        $this->assertSame($actionLabel, $action->getLabel());
        $this->assertSame($actionRoute, $action->getRoute());
        $this->assertSame([$paramName => $paramValue], $action->getRouteParams());
    }

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
