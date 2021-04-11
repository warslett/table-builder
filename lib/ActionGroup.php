<?php

declare(strict_types=1);

namespace WArslett\TableBuilder;

use JsonSerializable;
use WArslett\TableBuilder\Action;

class ActionGroup implements JsonSerializable
{
    /**
     * @var array<string, Action>
     */
    private $actions;

    /**
     * @param array<string, Action> $actions
     */
    public function __construct(array $actions)
    {
        $this->actions = $actions;
    }

    /**
     * @return array<string, Action>
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    public function __toString()
    {
        return implode(', ', array_keys($this->actions));
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'actions' => $this->actions
        ];
    }
}
