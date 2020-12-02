<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Column;

use WArslett\TableBuilder\Exception\NoValueAdapterException;
use WArslett\TableBuilder\ValueAdapter\ValueAdapterTrait;

/**
 * @extends AbstractColumn<string>
 */
final class TextColumn extends AbstractColumn
{
    use ValueAdapterTrait;

    /**
     * @param mixed $row
     * @return string
     * @throws NoValueAdapterException
     */
    protected function getCellValue($row)
    {
        $this->assertHasValueAdapter();

        return $this->valueAdapter->getValue($row);
    }
}
