<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Column;

use WArslett\TableBuilder\ValueAdapter\StringValueAdapterInterface;
use WArslett\TableBuilder\Exception\NoValueAdapterException;

final class TextColumn extends AbstractColumn
{
    private ?StringValueAdapterInterface $valueAdapter = null;

    /**
     * @param StringValueAdapterInterface $valueAdapter
     * @return $this
     */
    public function setValueAdapter(StringValueAdapterInterface $valueAdapter): self
    {
        $this->valueAdapter = $valueAdapter;
        return $this;
    }

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

        return $this->valueAdapter->getStringValue($row);
    }
}
