<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Column;

use WArslett\TableBuilder\Exception\NoValueAdapterException;
use WArslett\TableBuilder\Exception\ValueException;
use WArslett\TableBuilder\ValueAdapter\ValueAdapterTrait;

final class BooleanColumn extends AbstractColumn
{
    use ValueAdapterTrait;

    /**
     * @param mixed $row
     * @return mixed
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    protected function getCellValue($row)
    {
        $this->assertHasValueAdapter();

        $value = $this->valueAdapter->getValue($row);
        if (false === is_bool($value)) {
            throw new ValueException(sprintf("Value for column %s should be of type bool", $this->name));
        }

        return $value;
    }
}
