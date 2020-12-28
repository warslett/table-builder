<?php

declare(strict_types=1);

namespace WArslett\TableBuilder;

use WArslett\TableBuilder\Column\ColumnInterface;

interface TableBuilderInterface
{

    /**
     * @param ColumnInterface $column
     * @return $this
     */
    public function add(ColumnInterface $column): self;

    /**
     * @param int $defaultRowsPerPage
     * @return $this
     */
    public function defaultRowsPerPage(int $defaultRowsPerPage): self;

    /**
     * @param int $maxRowsPerPage
     * @return $this
     */
    public function maxRowsPerPage(int $maxRowsPerPage): self;

    /**
     * @param array<int> $rowsPerPageOptions
     * @return $this
     */
    public function rowsPerPageOptions(array $rowsPerPageOptions): self;

    /**
     * @param string $name
     * @return Table
     */
    public function buildTable(string $name): Table;
}
