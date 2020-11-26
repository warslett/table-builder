<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Column;

use WArslett\TableBuilder\Exception\NoValueAdapterException;
use WArslett\TableBuilder\Exception\ValueException;
use WArslett\TableBuilder\TableCell;
use WArslett\TableBuilder\TableHeading;

abstract class AbstractColumn implements ColumnInterface
{
    protected string $name;
    protected ?string $label = null;
    protected ?string $sortToggle = null;

    /**
     * Final protected construct (use static named constructors for concretions)
     * @param string $name
     */
    final protected function __construct(string $name)
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
     * @return string|null
     */
    public function getSortToggle(): ?string
    {
        return $this->sortToggle;
    }

    /**
     * @param string|null $sortToggle
     * @return $this
     */
    public function setSortToggle(?string $sortToggle): self
    {
        $this->sortToggle = $sortToggle;
        return $this;
    }

    /**
     * @return TableHeading
     */
    public function buildTableHeading(): TableHeading
    {
        return new TableHeading($this->name, $this->label ?? $this->name, null !== $this->sortToggle);
    }

    /**
     * @param mixed $row
     * @return TableCell
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    public function buildTableCell($row): TableCell
    {
        return new TableCell(static::class, $this->getCellValue($row));
    }

    /**
     * @param mixed $row
     * @return mixed
     * @throws NoValueAdapterException
     */
    abstract protected function getCellValue($row);

    /**
     * @psalm-suppress UnsafeInstantiation - we know it's safe because we made the constructor final
     * @param string $name
     * @return static
     */
    public static function withName(string $name): self
    {
        return new static($name);
    }
}
