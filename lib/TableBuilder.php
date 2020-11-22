<?php

declare(strict_types=1);

namespace WArslett\TableBuilder;

use WArslett\TableBuilder\Column\ColumnInterface;

final class TableBuilder implements TableBuilderInterface
{
    /** @var ColumnInterface[] */
    private array $columns = [];

    /** @var int */
    private int $defaultRowsPerPage = Table::DEFAULT_ROWS_PER_PAGE;

    /** @var int */
    private int $maxRowsPerPage = Table::DEFAULT_MAX_ROWS_PER_PAGE;

    /** @var array<int> */
    private array $rowsPerPageOptions = [];

    /**
     * @param ColumnInterface $column
     * @return $this
     */
    public function addColumn(ColumnInterface $column): self
    {
        $this->columns[$column->getName()] = $column;
        return $this;
    }

    /**
     * @param int $defaultRowsPerPage
     * @return $this
     */
    public function setDefaultRowsPerPage(int $defaultRowsPerPage): self
    {
        $this->defaultRowsPerPage = $defaultRowsPerPage;
        return $this;
    }

    /**
     * @param int $maxRowsPerPage
     * @return $this
     */
    public function setMaxRowsPerPage(int $maxRowsPerPage): self
    {
        $this->maxRowsPerPage = $maxRowsPerPage;
        return $this;
    }

    /**
     * @param array<int> $rowsPerPageOptions
     * @return $this
     */
    public function setRowsPerPageOptions(array $rowsPerPageOptions): self
    {
        $this->rowsPerPageOptions = $rowsPerPageOptions;
        return $this;
    }

    /**
     * @param string $name
     * @return Table
     */
    public function buildTable(string $name): Table
    {
        return new Table(
            $name,
            $this->columns,
            $this->defaultRowsPerPage,
            $this->maxRowsPerPage,
            $this->rowsPerPageOptions
        );
    }
}
