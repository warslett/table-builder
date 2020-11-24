<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Renderer\Html;

use WArslett\TableBuilder\Table;
use WArslett\TableBuilder\TableCell;
use WArslett\TableBuilder\TableHeading;

interface HtmlTableRendererInterface
{
    /**
     * @param Table $table
     * @return string
     */
    public function renderTable(Table $table): string;

    /**
     * @param Table $table
     * @return string
     */
    public function renderTableRowsPerPageOptions(Table $table): string;

    /**
     * @param Table $table
     * @return string
     */
    public function renderTableElement(Table $table): string;

    /**
     * @param Table $table
     * @param TableHeading $heading
     * @return string
     */
    public function renderTableHeading(Table $table, TableHeading $heading): string;

    /**
     * @param Table $table
     * @param array<TableCell> $row
     * @return string
     */
    public function renderTableRow(Table $table, array $row): string;

    /**
     * @param Table $table
     * @param array<TableCell> $row
     * @param TableCell $cell
     * @return string
     */
    public function renderTableCell(Table $table, array $row, TableCell $cell): string;

    /**
     * @param Table $table
     * @param array<TableCell> $row
     * @param TableCell $cell
     * @return string
     */
    public function renderTableCellValue(Table $table, array $row, TableCell $cell): string;

    /**
     * @param Table $table
     * @return string
     */
    public function renderTablePagination(Table $table): string;
}
