<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests;

use stdClass;
use WArslett\TableBuilder\Column\TextColumn;
use WArslett\TableBuilder\Exception\DataAdapterException;
use WArslett\TableBuilder\Exception\SortToggleException;
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
            ->add(TextColumn::withName($columnName))
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
            ->add(TextColumn::withName($columnName))
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
            ->add(TextColumn::withName($columnName)->label($columnLabel))
            ->buildTable('user_table')
        ;

        $headings = $table->getHeadings();
        $this->assertSame($columnLabel, $headings[$columnName]->getLabel());
    }

    /**
     * @return void
     */
    public function testNoColumnLabelNameIsIncludedOnHeadingConvertsNameToTitleCase(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $columnName = 'foo_bar';

        $table = $tableBuilderFactory->createTableBuilder()
            ->add(TextColumn::withName($columnName))
            ->buildTable('user_table')
        ;

        $headings = $table->getHeadings();
        $this->assertSame('Foo Bar', $headings[$columnName]->getLabel());
    }

    /**
     * @return void
     */
    public function testNoColumnLabelNameIsNonConvertableSetsLabelToColumnName(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $columnName = hex2bin('afb1e7f88c26d7b7cb81aa9632c02ca8');

        $table = $tableBuilderFactory->createTableBuilder()
            ->add(TextColumn::withName($columnName))
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
            ->add(TextColumn::withName('foo'))
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                ['foo' => 'bar']
            ]))
            ->handleParameters([])
        ;
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testOneRowOfArrayDataOneColumnWithPropertyAdapterMapsRowToCell(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->add(TextColumn::withName('foo')->property('[foo]'))
            ->defaultRowsPerPage(1)
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                ['foo' => 'bar', 'baz' => 'qux']
            ]))
            ->handleParameters([])
        ;

        $rows = $table->getRows();
        $this->assertArrayHasKey(0, $rows);
        $this->assertSame(TextColumn::class, $rows[0]['foo']->getRenderingType());
        $this->assertSame('bar', $rows[0]['foo']->getValue());
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testOneRowOfObjectDataOneColumnWithPropertyAdapterMapsRowToCell(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $object = new stdClass();
        $object->foo = 'bar';
        $object->baz = 'qux';
        $table = $tableBuilderFactory->createTableBuilder()
            ->add(TextColumn::withName('foo')->property('foo'))
            ->defaultRowsPerPage(1)
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([$object]))
            ->handleParameters([])
        ;

        $rows = $table->getRows();
        $this->assertArrayHasKey(0, $rows);
        $this->assertSame(TextColumn::class, $rows[0]['foo']->getRenderingType());
        $this->assertSame('bar', $rows[0]['foo']->getValue());
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testOneRowOfObjectDataOneColumnWithPropertyMapsRowToCellWithNameAsProperty(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $object = new stdClass();
        $object->foo = 'bar';
        $object->baz = 'qux';
        $table = $tableBuilderFactory->createTableBuilder()
            ->add(TextColumn::withProperty('foo'))
            ->defaultRowsPerPage(1)
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([$object]))
            ->handleParameters([])
        ;

        $rows = $table->getRows();
        $this->assertArrayHasKey(0, $rows);
        $this->assertSame(TextColumn::class, $rows[0]['foo']->getRenderingType());
        $this->assertSame('bar', $rows[0]['foo']->getValue());
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testOneRowOfObjectDataOneColumnWithCallbackAdapterMapsRowToCell(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $object = new stdClass();
        $object->foo = 'bar';
        $object->baz = 'qux';
        $table = $tableBuilderFactory->createTableBuilder()
            ->add(TextColumn::withName('foo')->callback(function ($row) {
                return $row->foo;
            }))
            ->defaultRowsPerPage(1)
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([$object]))
            ->handleParameters([])
        ;

        $rows = $table->getRows();
        $this->assertArrayHasKey(0, $rows);
        $this->assertSame(TextColumn::class, $rows[0]['foo']->getRenderingType());
        $this->assertSame('bar', $rows[0]['foo']->getValue());
    }
}
