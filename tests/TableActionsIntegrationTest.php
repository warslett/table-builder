<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests;

use WArslett\TableBuilder\Action;
use WArslett\TableBuilder\ActionBuilder;
use WArslett\TableBuilder\ActionGroup;
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
        $attributeKey = 'foo';
        $attributeValue = 'bar';
        $paramValue = 123;
        $table = $tableBuilderFactory->createTableBuilder()
            ->add(ActionGroupColumn::withName($columnName)
                ->add(ActionBuilder::withName($actionName)
                    ->label($actionLabel)
                    ->attribute($attributeKey, $attributeValue)
                    ->route($actionRoute, [
                        $paramName => PropertyAccessAdapter::withPropertyPath('[id]')
                    ])))
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                ['id' => $paramValue]
            ]))
            ->handleRequest(ArrayRequestAdapter::withArray([]))
        ;
        /** @var ActionGroup $actionGroup */
        $actionGroup = $table->getRows()[0][$columnName]->getValue();
        $actions = $actionGroup->getActions();

        $this->assertArrayHasKey($actionName, $actions);
        /** @var Action $action */
        $action = $actions[$actionName];
        $this->assertSame($actionLabel, $action->getLabel());
        $this->assertSame($attributeValue, $action->getAttribute($attributeKey));
        $this->assertSame($actionRoute, $action->getRoute());
        $this->assertSame([$paramName => $paramValue], $action->getRouteParams());
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testActionGroupActionConditionFalseExcludesAction(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->add(ActionGroupColumn::withName('actions')
                ->add(ActionBuilder::withName('delete')
                    ->condition(fn($row) => false)))
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                []
            ]))
            ->handleRequest(ArrayRequestAdapter::withArray([]))
        ;
        /** @var ActionGroup $actionGroup */
        $actionGroup = $table->getRows()[0]['actions']->getValue();
        $actions = $actionGroup->getActions();

        $this->assertEmpty($actions);
    }

    /**
     * @return void
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     * @throws SortToggleException
     */
    public function testActionGroupActionConditionTrueIncludesAction(): void
    {
        $tableBuilderFactory = new TableBuilderFactory();
        $table = $tableBuilderFactory->createTableBuilder()
            ->add(ActionGroupColumn::withName('actions')
                ->add(ActionBuilder::withName('delete')
                    ->condition(fn($row) => true)))
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                []
            ]))
            ->handleRequest(ArrayRequestAdapter::withArray([]))
        ;
        /** @var ActionGroup $actionGroup */
        $actionGroup = $table->getRows()[0]['actions']->getValue();
        $actions = $actionGroup->getActions();

        $this->assertArrayHasKey('delete', $actions);
    }
}
