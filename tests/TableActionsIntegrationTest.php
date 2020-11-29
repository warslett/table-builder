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

class TableActionsIntegrationTest extends TestCase
{

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
}
