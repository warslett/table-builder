<?php

declare(strict_types=1);

namespace WArslett\TableBuilder;

class TableHeading
{
    private string $label;

    public function __construct(string $label)
    {
        $this->label = $label;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}
