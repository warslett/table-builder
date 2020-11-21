<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Column;

use WArslett\TableBuilder\Exception\NoColumnAdapterException;
use WArslett\TableBuilder\TableCell;
use WArslett\TableBuilder\TableHeading;

abstract class AbstractColumn implements ColumnInterface
{
    protected string $name;
    protected ?string $label = null;

    /**
     * Private construct (use static named constructors for concretions)
     * @param string $name
     */
    private function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string|null $label
     * @return $this
     */
    public function setLabel(?string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return TableHeading
     */
    public function buildTableHeading(): TableHeading
    {
        return new TableHeading($this->label ?? $this->name);
    }

    /**
     * @param mixed $row
     * @return TableCell
     * @throws NoColumnAdapterException
     */
    public function buildTableCell($row): TableCell
    {
        return new TableCell(static::class, $this->getCellValue($row));
    }

    /**
     * @param mixed $row
     * @return mixed
     * @throws NoColumnAdapterException
     */
    abstract protected function getCellValue($row);

    /**
     * @psalm-suppress UnsafeInstantiation - we know it's safe because we made the constructor private
     * @param string $name
     * @return static
     */
    public static function withName(string $name): self
    {
        return new static($name);
    }
}
