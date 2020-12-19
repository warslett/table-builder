<?php

declare(strict_types=1);

namespace WArslett\TableBuilder;

interface ActionBuilderInterface
{

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param mixed $row
     * @return bool
     */
    public function isAllowedFor($row): bool;

    /**
     * @param mixed $row
     * @return Action
     */
    public function buildAction($row): Action;
}
