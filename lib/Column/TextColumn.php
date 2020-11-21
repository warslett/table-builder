<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Column;

use WArslett\TableBuilder\ColumnAdapter\StringColumnAdapterInterface;
use WArslett\TableBuilder\Exception\NoColumnAdapterException;

final class TextColumn extends AbstractColumn
{
    private ?StringColumnAdapterInterface $columnAdapter = null;

    /**
     * @param StringColumnAdapterInterface $columnAdapter
     * @return $this
     */
    public function setColumnAdapter(StringColumnAdapterInterface $columnAdapter): self
    {
        $this->columnAdapter = $columnAdapter;
        return $this;
    }

    /**
     * @param mixed $row
     * @return mixed
     * @throws NoColumnAdapterException
     */
    protected function getCellValue($row)
    {
        if (null === $this->columnAdapter) {
            throw new NoColumnAdapterException(
                sprintf("Cannot handle request until data adapter has been set for %s", $this->name)
            );
        }

        return $this->columnAdapter->getStringValue($row);
    }
}
