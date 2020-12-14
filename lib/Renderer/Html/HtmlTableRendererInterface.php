<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Renderer\Html;

use WArslett\TableBuilder\Table;
use WArslett\TableBuilder\TableCell;
use WArslett\TableBuilder\TableHeading;

interface HtmlTableRendererInterface
{
    /**
     * Render the whole table including the rows per page links and pagination
     * @param Table $table
     * @return string
     */
    public function renderTable(Table $table): string;

    /**
     * Render the rows per page links for the table
     * @param Table $table
     * @return string
     */
    public function renderTableRowsPerPageOptions(Table $table): string;

    /**
     * Render just the table element without the rows per page options or pagination
     * @param Table $table
     * @return string
     */
    public function renderTableElement(Table $table): string;

    /**
     * Render a single table heading (th element)
     * @param Table $table
     * @param TableHeading $heading
     * @return string
     */
    public function renderTableHeading(Table $table, TableHeading $heading): string;

    /**
     * Render a single table row (tr element)
     * @param Table $table
     * @param array<TableCell> $row
     * @return string
     */
    public function renderTableRow(Table $table, array $row): string;

    /**
     * Render a single table cell (td element)
     * @param Table $table
     * @param TableCell $cell
     * @return string
     */
    public function renderTableCell(Table $table, TableCell $cell): string;

    /**
     * Render the value of the cell (just the content excluding the outer html tags)
     * @param Table $table
     * @param TableCell $cell
     * @return string
     */
    public function renderTableCellValue(Table $table, TableCell $cell): string;

    /**
     * Render a table route with params as a url
     * @param string $route
     * @param array $params
     * @return string
     */
    public function renderTableRoute(string $route, array $params = []): string;

    /**
     * Render the pagination for the table
     * @param Table $table
     * @return string
     */
    public function renderTablePagination(Table $table): string;
}
