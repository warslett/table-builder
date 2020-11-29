<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Renderer\Html;

use Throwable;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\TemplateWrapper;
use WArslett\TableBuilder\Column\ActionGroupColumn;
use WArslett\TableBuilder\Column\BooleanColumn;
use WArslett\TableBuilder\RouteGeneratorAdapter\RouteGeneratorAdapterInterface;
use WArslett\TableBuilder\Table;
use WArslett\TableBuilder\TableCell;
use WArslett\TableBuilder\TableHeading;

final class TwigRenderer implements HtmlTableRendererInterface
{
    private Environment $environment;
    private RouteGeneratorAdapterInterface $routeGeneratorAdapter;
    private string $themeTemplatePath;
    private ?TemplateWrapper $template = null;
    private array $cellValueBlocks = [
        ActionGroupColumn::class => 'action_group_cell_value',
        BooleanColumn::class => 'boolean_cell_value'
    ];
    private array $cellValueTemplates = [];

    public function __construct(
        Environment $environment,
        RouteGeneratorAdapterInterface $routeGeneratorAdapter,
        string $themeTemplatePath
    ) {
        $this->environment = $environment;
        $this->routeGeneratorAdapter = $routeGeneratorAdapter;
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
     * @param array $row
     * @param TableCell $cell
     * @return string
     * @throws Throwable
     */
    public function renderTableCellValue(Table $table, array $row, TableCell $cell): string
    {
        $renderingType = $cell->getRenderingType();

        if (isset($this->cellValueTemplates[$renderingType])) {
            return $this->environment->render($this->cellValueTemplates[$renderingType], [
                'table' => $table,
                'row' => $row,
                'cell' => $cell
            ]);
        }

        if (isset($this->cellValueBlocks[$renderingType])) {
            return $this->getTemplate()->renderBlock($this->cellValueBlocks[$renderingType], [
                'table' => $table,
                'row' => $row,
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
     * @throws Throwable
     */
    public function renderTablePagination(Table $table): string
    {
        return $this->getTemplate()->renderBlock('table_pagination', [
            'table' => $table
        ]);
    }

    /**
     * @param string $renderingType
     * @param string $blockName
     * @return void
     */
    public function registerCellValueBlock(string $renderingType, string $blockName)
    {
        $this->cellValueBlocks[$renderingType] = $blockName;
    }

    /**
     * @param string $renderingType
     * @param string $templatePath
     * @return void
     */
    public function registerCellValueTemplate(string $renderingType, string $templatePath)
    {
        $this->cellValueTemplates[$renderingType] = $templatePath;
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
