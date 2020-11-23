<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Renderer\Html;

use Throwable;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\TemplateWrapper;
use WArslett\TableBuilder\Table;
use WArslett\TableBuilder\TableCell;
use WArslett\TableBuilder\TableHeading;

final class TwigRenderer implements HtmlTableRendererInterface
{
    private Environment $environment;
    private string $themeTemplatePath;
    private ?TemplateWrapper $template = null;

    public function __construct(Environment $environment, string $themeTemplatePath)
    {
        $this->environment = $environment;
        $this->themeTemplatePath = $themeTemplatePath;
    }

    /**
     * @param Table $table
     * @return string
     * @throws Throwable
     */
    public function renderTable(Table $table): string
    {
        return $this->getTemplate()->renderBlock('table', [
            'table' => $table
        ]);
    }

    /**
     * @param Table $table
     * @return string
     * @throws Throwable
     */
    public function renderTableRowsPerPageOptions(Table $table): string
    {
        return $this->getTemplate()->renderBlock('table_rows_per_page_options', [
            'table' => $table
        ]);
    }

    /**
     * @param Table $table
     * @return string
     * @throws Throwable
     */
    public function renderTableElement(Table $table): string
    {
        return $this->getTemplate()->renderBlock('table_element', [
            'table' => $table
        ]);
    }

    /**
     * @param Table $table
     * @param TableHeading $heading
     * @return string
     * @throws Throwable
     */
    public function renderTableHeading(Table $table, TableHeading $heading): string
    {
        return $this->getTemplate()->renderBlock('table_heading', [
            'table' => $table,
            'heading' => $heading
        ]);
    }

    /**
     * @param Table $table
     * @param array $row
     * @return string
     * @throws Throwable
     */
    public function renderTableRow(Table $table, array $row): string
    {
        return $this->getTemplate()->renderBlock('table_row', [
            'table' => $table,
            'row' => $row
        ]);
    }

    /**
     * @param Table $table
     * @param array $row
     * @param TableCell $cell
     * @return string
     * @throws Throwable
     */
    public function renderTableCell(Table $table, array $row, TableCell $cell): string
    {
        return $this->getTemplate()->renderBlock('table_cell', [
            'table' => $table,
            'row' => $row,
            'cell' => $cell
        ]);
    }

    /**
     * @param Table $table
     * @return string
     * @throws Throwable
     */
    public function renderTablePagination(Table $table): string
    {
        return $this->getTemplate()->renderBlock('table_pagination', [
            'table' => $table
        ]);
    }

    /**
     * @return TemplateWrapper
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function getTemplate(): TemplateWrapper
    {
        if (null === $this->template) {
            $this->template = $this->environment->load($this->themeTemplatePath);
        }

        return $this->template;
    }
}
