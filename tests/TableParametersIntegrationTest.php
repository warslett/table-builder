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

class TableParametersIntegrationTest extends TestCase
{
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
            ->defaultRowsPerPage(5)
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
            ->defaultRowsPerPage(5)
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
            ->defaultRowsPerPage(10)
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
}
