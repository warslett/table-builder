<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests;

use WArslett\TableBuilder\Column\TextColumn;
use WArslett\TableBuilder\ValueAdapter\PropertyAccessAdapter;
use WArslett\TableBuilder\DataAdapter\ArrayDataAdapter;
use WArslett\TableBuilder\Exception\NoValueAdapterException;
use WArslett\TableBuilder\Exception\NoDataAdapterException;
use WArslett\TableBuilder\RequestAdapter\ArrayRequestAdapter;
use WArslett\TableBuilder\TableBuilderFactory;
use WArslett\TableBuilder\TableCell;

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
