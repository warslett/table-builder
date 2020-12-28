<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Column;

use DateTimeInterface;
use WArslett\TableBuilder\Exception\NoValueAdapterException;
use WArslett\TableBuilder\Exception\ValueException;
use WArslett\TableBuilder\ValueAdapter\ValueAdapterTrait;

/**
 * @extends AbstractColumn<string>
 */
final class DateTimeColumn extends AbstractColumn
{
    use ValueAdapterTrait;

    /** @var string */
    private string $dateTimeFormat = 'Y-m-d H:i:s';

    /**
     * @param string $dateTimeFormat
     * @return $this
     */
    public function format(string $dateTimeFormat): self
    {
        $this->dateTimeFormat = $dateTimeFormat;
        return $this;
    }

    /**
     * @param mixed $row
     * @return string|null
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    protected function getCellValue($row)
    {
        $this->assertHasValueAdapter();
        $value = $this->valueAdapter->getValue($row);

        if (is_null($value)) {
            return null;
        }

        if (false === $value instanceof DateTimeInterface) {
            throw new ValueException(sprintf("Value for column %s should be of type DateTimeInterface", $this->name));
        }

        return $value->format($this->dateTimeFormat);
    }
}
