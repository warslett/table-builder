<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\ValueAdapter;

interface StringValueAdapterInterface
{

    /**
     * @param mixed $row
     * @return string
     */
    public function getStringValue($row): string;
}
