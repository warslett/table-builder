<?php

declare(strict_types=1);

namespace WArslett\TableBuilder;

use JsonSerializable;

class TableHeading implements JsonSerializable
{
    private $name;
    private $label;
    private $isSortable;

    public function __construct(string $name, string $label, bool $isSortable)
    {
        $this->name = $name;
        $this->label = $label;
        $this->isSortable = $isSortable;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return bool
     */
    public function isSortable(): bool
    {
        return $this->isSortable;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'is_sortable' => $this->isSortable
        ];
    }
}
