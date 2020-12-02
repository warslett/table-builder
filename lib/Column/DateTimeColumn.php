<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Column;

use DateTime;
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
    public function setDateTimeFormat(string $dateTimeFormat): self
    {
        $this->dateTimeFormat = $dateTimeFormat;
        return $this;
    }

    /**
     * @param mixed $row
     * @return string
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    protected function getCellValue($row)
    {
        $this->assertHasValueAdapter();

        $value = $this->valueAdapter->getValue($row);
        if (false === $value instanceof DateTime) {
            throw new ValueException(sprintf("Value for column %s should be of type DateTime", $this->name));
        }

        return $value->format($this->dateTimeFormat);
    }
}
