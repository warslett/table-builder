<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\ColumnAdapter;

interface StringColumnAdapterInterface
{

    /**
     * @param mixed $row
     * @return string
     */
    public function getStringValue($row): string;
}
