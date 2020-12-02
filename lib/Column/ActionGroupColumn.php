<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Column;

use WArslett\TableBuilder\ActionBuilderInterface;
use WArslett\TableBuilder\ActionGroup;

/**
 * @extends AbstractColumn<ActionGroup>
 */
final class ActionGroupColumn extends AbstractColumn
{
    /** @var array<string, ActionBuilderInterface> */
    private array $actionBuilders = [];

    /**
     * @param ActionBuilderInterface $actionBuilder
     * @return $this
     */
    public function addActionBuilder(ActionBuilderInterface $actionBuilder): self
    {
        $this->actionBuilders[$actionBuilder->getName()] = $actionBuilder;
        return $this;
    }

    /**
     * @param mixed $row
     * @return ActionGroup
     */
    protected function getCellValue($row): ActionGroup
    {
        return new ActionGroup(array_map(
            fn(ActionBuilderInterface $actionBuilder) => $actionBuilder->buildAction($row),
            $this->actionBuilders
        ));
    }
}
