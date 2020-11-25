<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Column;

use WArslett\TableBuilder\ValueAdapter\ValueAdapterInterface;
use WArslett\TableBuilder\Exception\NoValueAdapterException;
use WArslett\TableBuilder\ValueAdapter\ValueAdapterTrait;

final class TextColumn extends AbstractColumn
{
    use ValueAdapterTrait;

    /**
     * @param mixed $row
     * @return mixed
     * @throws NoValueAdapterException
     */
    protected function getCellValue($row)
    {
        if (null === $this->valueAdapter) {
            throw new NoValueAdapterException(
                sprintf("Cannot handle request until value adapter has been set for %s", $this->name)
            );
        }

        return $this->valueAdapter->getValue($row);
    }
}
