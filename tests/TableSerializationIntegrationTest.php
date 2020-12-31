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

class TableSerializationIntegrationTest extends TestCase
{

    /**
     * @return void
     */
    public function testSerializeNoRequestReturnsEmptyTable(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->buildTable('user_table')
        ;
        $this->assertSame(json_encode([
            'name' => 'user_table',
            'rows_per_page' => 20,
            'max_rows_per_page' => 100,
            'headings' => [],
            'rows' => [],
            'total_rows' => 0,
            'page_number' => 0,
            'rows_per_page_options' => [],
            'sort_column' => null,
            'sort_dir' => 'asc',
        ], JSON_PRETTY_PRINT), json_encode($table, JSON_PRETTY_PRINT));
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testSerializeOneRowOfDataNoColumnsReturnsRowArrayContainingOneEmptyArray(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                ['foo' => 'bar']
            ]))
            ->handleRequest(ArrayRequestAdapter::withArray([]))
        ;

        $this->assertSame(json_encode([
            'name' => 'user_table',
            'rows_per_page' => 20,
            'max_rows_per_page' => 100,
            'headings' => [],
            'rows' => [[]],
            'total_rows' => 1,
            'page_number' => 1,
            'rows_per_page_options' => [],
            'sort_column' => null,
            'sort_dir' => 'asc',
        ], JSON_PRETTY_PRINT), json_encode($table, JSON_PRETTY_PRINT));
    }

    /**
     * @return void
     */
    public function testSerializeOneColumnOneHeading(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $columnName = 'foo';
        $columnLabel = 'Foo Label';
        $table = $tableBuilderFactory->createTableBuilder()
            ->add(TextColumn::withName($columnName)->label($columnLabel))
            ->buildTable('user_table')
        ;

        $this->assertSame(json_encode([
            'name' => 'user_table',
            'rows_per_page' => 20,
            'max_rows_per_page' => 100,
            'headings' => [
                $columnName => [
                    'name' => $columnName,
                    'label' => $columnLabel,
                    'is_sortable' => false
                ]
            ],
            'rows' => [],
            'total_rows' => 0,
            'page_number' => 0,
            'rows_per_page_options' => [],
            'sort_column' => null,
            'sort_dir' => 'asc',
        ], JSON_PRETTY_PRINT), json_encode($table, JSON_PRETTY_PRINT));
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
            ->add(TextColumn::withName('foo')
                ->valueAdapter(PropertyAccessAdapter::withPropertyPath('[foo]')))
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                ['foo' => 'bar', 'baz' => 'qux']
            ]))
            ->handleRequest(ArrayRequestAdapter::withArray([]))
        ;

        $this->assertSame(json_encode([
            'name' => 'user_table',
            'rows_per_page' => 20,
            'max_rows_per_page' => 100,
            'headings' => [
                'foo' => [
                    'name' => 'foo',
                    'label' => 'Foo',
                    'is_sortable' => false
                ]
            ],
            'rows' => [
                [
                    'foo' => [
                        'name' => 'foo',
                        'rendering_type' => TextColumn::class,
                        'value' => 'bar'
                    ]
                ]
            ],
            'total_rows' => 1,
            'page_number' => 1,
            'rows_per_page_options' => [],
            'sort_column' => null,
            'sort_dir' => 'asc',
        ], JSON_PRETTY_PRINT), json_encode($table, JSON_PRETTY_PRINT));
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testSerializeActionGroupColumn(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $columnName = 'actions';
        $actionName = 'delete';
        $actionLabel = "Delete";
        $actionRoute = 'my_route';
        $paramName = 'route_id';
        $paramValue = 123;
        $table = $tableBuilderFactory->createTableBuilder()
            ->add(ActionGroupColumn::withName($columnName)
                ->add(ActionBuilder::withName($actionName)
                    ->label($actionLabel)
                    ->route($actionRoute, [
                        $paramName => PropertyAccessAdapter::withPropertyPath('[id]')
                    ])))
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                ['id' => $paramValue]
            ]))
            ->handleRequest(ArrayRequestAdapter::withArray([]))
        ;

        $this->assertSame(json_encode([
            'name' => 'user_table',
            'rows_per_page' => 20,
            'max_rows_per_page' => 100,
            'headings' => [
                'actions' => [
                    'name' => 'actions',
                    'label' => 'Actions',
                    'is_sortable' => false
                ]
            ],
            'rows' => [
                [
                    'actions' => [
                        'name' => 'actions',
                        'rendering_type' => ActionGroupColumn::class,
                        'value' => [
                            'actions' => [
                                'delete' => [
                                    'label' => 'Delete',
                                    'route' => 'my_route',
                                    'route_params' => ['route_id' => 123]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'total_rows' => 1,
            'page_number' => 1,
            'rows_per_page_options' => [],
            'sort_column' => null,
            'sort_dir' => 'asc',
        ], JSON_PRETTY_PRINT), json_encode($table, JSON_PRETTY_PRINT));
    }
}
