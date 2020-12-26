<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Column;

use Closure;
use WArslett\TableBuilder\Exception\NoValueAdapterException;
use WArslett\TableBuilder\Exception\ValueException;
use WArslett\TableBuilder\TableCell;
use WArslett\TableBuilder\TableHeading;

/**
 * @template T
 */
abstract class AbstractColumn implements ColumnInterface
{
    protected string $name;
    protected ?string $label = null;
    protected ?string $sortToggle = null;
    protected ?Closure $afterBuildCell = null;

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
    final public function getName(): string
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
    final public function getSortToggle(): ?string
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
     * @param Closure $callback
     * @return $this
     */
    public function afterBuildCell(Closure $callback): self
    {
        $this->afterBuildCell = $callback;
        return $this;
    }

    /**
     * @return TableHeading
     */
    final public function buildTableHeading(): TableHeading
    {
        return new TableHeading($this->name, $this->label ?? $this->name, null !== $this->sortToggle);
    }

    /**
     * @param mixed $row
     * @return TableCell<T>
     * @throws NoValueAdapterException
     * @throws ValueException
     */
    final public function buildTableCell($row): TableCell
    {
        $cell =  new TableCell($this->name, static::class, $this->getCellValue($row));

        if (null !== $this->afterBuildCell) {
            ($this->afterBuildCell)($cell, $row);
        }

        return $cell;
    }

    /**
     * @param mixed $row
     * @return T|null
     * @throws NoValueAdapterException
     */
    abstract protected function getCellValue($row);

    /**
     * @param string $name
     * @return static
     */
    public static function withName(string $name): self
    {
        return new static($name);
    }
}
