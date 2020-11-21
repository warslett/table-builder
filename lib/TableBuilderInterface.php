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
    public function addColumn(ColumnInterface $column): self;

    /**
     * @param int $defaultRowsPerPage
     * @return $this
     */
    public function setDefaultRowsPerPage(int $defaultRowsPerPage): self;

    /**
     * @param int $maxRowsPerPage
     * @return $this
     */
    public function setMaxRowsPerPage(int $maxRowsPerPage): self;

    /**
     * @param string $name
     * @return Table
     */
    public function buildTable(string $name): Table;
}
