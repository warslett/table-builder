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

class TablePaginationIntegrationTest extends TestCase
{

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
}
