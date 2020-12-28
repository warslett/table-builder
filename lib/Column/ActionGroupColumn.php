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
    public function add(ActionBuilderInterface $actionBuilder): self
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
        $actionGroupActions = [];
        foreach ($this->actionBuilders as $actionName => $actionBuilder) {
            if ($actionBuilder->isAllowedFor($row)) {
                $actionGroupActions[$actionName] = $actionBuilder->buildAction($row);
            }
        }
        return new ActionGroup($actionGroupActions);
    }
}
