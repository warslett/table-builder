<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Renderer\Html;

use WArslett\TableBuilder\Column\ActionGroupColumn;
use WArslett\TableBuilder\Column\BooleanColumn;
use WArslett\TableBuilder\RouteGeneratorAdapter\RouteGeneratorAdapterInterface;
use WArslett\TableBuilder\RouteGeneratorAdapter\SprintfAdapter;
use WArslett\TableBuilder\Table;
use WArslett\TableBuilder\TableCell;
use WArslett\TableBuilder\TableHeading;

final class PhtmlRenderer implements HtmlTableRendererInterface
{
    public const STANDARD_THEME_DIRECTORY_PATH = __DIR__ . '/../../../templates/phtml/table-builder/standard';
    public const BOOTSTRAP4_THEME_DIRECTORY_PATH = __DIR__ . '/../../../templates/phtml/table-builder/bootstrap4';
    private const RELATIVE_DEFAULT_CELL_VALUE_TEMPLATES = [
        ActionGroupColumn::class => "action_group_cell_value.phtml",
        BooleanColumn::class => "boolean_cell_value.phtml"
    ];

    private RouteGeneratorAdapterInterface $routeGeneratorAdapter;
    private string $themeDirectoryPath;
    private array $cellValueTemplates;

    public function __construct(
        ?RouteGeneratorAdapterInterface $routeGeneratorAdapter = null,
        string $themeDirectoryPath = self::STANDARD_THEME_DIRECTORY_PATH
    ) {
        $this->routeGeneratorAdapter = $routeGeneratorAdapter ?? new SprintfAdapter();
        $this->themeDirectoryPath = $themeDirectoryPath;

        $this->cellValueTemplates = array_map(
            fn ($relativePath) => "$this->themeDirectoryPath/$relativePath",
            self::RELATIVE_DEFAULT_CELL_VALUE_TEMPLATES
        );
    }

    /**
     * @param Table $table
     * @return string
     */
    public function renderTable(Table $table): string
    {
        return $this->renderTemplate("$this->themeDirectoryPath/table.phtml", [
            'table' => $table
        ]);
    }

    /**
     * @param Table $table
     * @return string
     */
    public function renderTableRowsPerPageOptions(Table $table): string
    {
        return $this->renderTemplate("$this->themeDirectoryPath/table_rows_per_page_options.phtml", [
            'table' => $table
        ]);
    }

    /**
     * @param Table $table
     * @return string
     */
    public function renderTableElement(Table $table): string
    {
        return $this->renderTemplate("$this->themeDirectoryPath/table_element.phtml", [
            'table' => $table
        ]);
    }

    /**
     * @param Table $table
     * @param TableHeading $heading
     * @return string
     */
    public function renderTableHeading(Table $table, TableHeading $heading): string
    {
        return $this->renderTemplate("$this->themeDirectoryPath/table_heading.phtml", [
            'table' => $table,
            'heading' => $heading
        ]);
    }

    /**
     * @param Table $table
     * @param array<TableCell> $row
     * @return string
     */
    public function renderTableRow(Table $table, array $row): string
    {
        return $this->renderTemplate("$this->themeDirectoryPath/table_row.phtml", [
            'table' => $table,
            'row' => $row
        ]);
    }

    /**
     * @param Table $table
     * @param TableCell $cell
     * @return string
     */
    public function renderTableCell(Table $table, TableCell $cell): string
    {
        return $this->renderTemplate("$this->themeDirectoryPath/table_cell.phtml", [
            'table' => $table,
            'cell' => $cell
        ]);
    }

    /**
     * @param Table $table
     * @param TableCell $cell
     * @return string
     */
    public function renderTableCellValue(Table $table, TableCell $cell): string
    {
        if (isset($this->cellValueTemplates[$cell->getRenderingType()])) {
            return $this->renderTemplate($this->cellValueTemplates[$cell->getRenderingType()], [
                'table' => $table,
                'cell' => $cell
            ]);
        }

        return (string) $cell->getValue();
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    public function renderTableRoute(string $route, array $params = []): string
    {
        return $this->routeGeneratorAdapter->renderRoute($route, $params);
    }

    /**
     * @param Table $table
     * @return string
     */
    public function renderTablePagination(Table $table): string
    {
        return $this->renderTemplate("$this->themeDirectoryPath/table_pagination.phtml", ['table' => $table]);
    }

    /**
     * @param string $renderingType
     * @param string $templatePath
     * @return void
     */
    public function registerCellValueTemplate(string $renderingType, string $templatePath): void
    {
        $this->cellValueTemplates[$renderingType] = $templatePath;
    }

    /**
     * @psalm-suppress UnresolvableInclude - unresolvable include is template
     * @param string $template
     * @param array $params
     * @return string
     */
    private function renderTemplate(string $template, array $params = []): string
    {
        foreach ($params as $key => $value) {
            ${$key} = $value;
        }

        ob_start();
        require "$template";
        return ob_get_clean();
    }
}
