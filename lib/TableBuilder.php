<?php

declare(strict_types=1);

namespace WArslett\TableBuilder;

use WArslett\TableBuilder\Column\ColumnInterface;

final class TableBuilder implements TableBuilderInterface
{
    /** @var ColumnInterface[] */
    private $columns = [];

    /** @var int */
    private $defaultRowsPerPage = Table::DEFAULT_ROWS_PER_PAGE;

    /** @var int */
    private $maxRowsPerPage = Table::DEFAULT_MAX_ROWS_PER_PAGE;

    /** @var array<int> */
    private $rowsPerPageOptions = [];

    /**
     * @param ColumnInterface $column
     * @return $this
     */
    public function add(ColumnInterface $column): TableBuilderInterface
    {
        $this->columns[$column->getName()] = $column;
        return $this;
    }

    /**
     * @param int $defaultRowsPerPage
     * @return $this
     */
    public function defaultRowsPerPage(int $defaultRowsPerPage): TableBuilderInterface
    {
        $this->defaultRowsPerPage = $defaultRowsPerPage;
        return $this;
    }

    /**
     * @param int $maxRowsPerPage
     * @return $this
     */
    public function maxRowsPerPage(int $maxRowsPerPage): TableBuilderInterface
    {
        $this->maxRowsPerPage = $maxRowsPerPage;
        return $this;
    }

    /**
     * @param array<int> $rowsPerPageOptions
     * @return $this
     */
    public function rowsPerPageOptions(array $rowsPerPageOptions): TableBuilderInterface
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
