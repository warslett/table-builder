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
    public const STANDARD_THEME_PATH = 'table-builder/standard.html.twig';
    public const BOOTSTRAP4_THEME_PATH = 'table-builder/bootstrap4.html.twig';

    /** @var Environment */
    private $environment;

    /** @var RouteGeneratorAdapterInterface */
    private $routeGeneratorAdapter;

    /** @var string */
    private $themeTemplatePath;

    /** @var TemplateWrapper|null */
    private $template = null;

    /** @var array<string, string> */
    private $cellValueBlocks = [
        ActionGroupColumn::class => 'action_group_cell_value',
        BooleanColumn::class => 'boolean_cell_value'
    ];

    /** @var array<string, string> */
    private $cellValueTemplates = [];

    public function __construct(
        Environment $environment,
        RouteGeneratorAdapterInterface $routeGeneratorAdapter,
        string $themeTemplatePath = self::STANDARD_THEME_PATH
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
     * @param array<TableCell> $row
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
     * @param TableCell $cell
     * @return string
     * @throws Throwable
     */
    public function renderTableCell(Table $table, TableCell $cell): string
    {
        return $this->getTemplate()->renderBlock('table_cell', [
            'table' => $table,
            'cell' => $cell
        ]);
    }

    /**
     * @param Table $table
     * @param TableCell $cell
     * @return string
     * @throws Throwable
     */
    public function renderTableCellValue(Table $table, TableCell $cell): string
    {
        $renderingType = $cell->getRenderingType();

        if (isset($this->cellValueTemplates[$renderingType])) {
            return $this->environment->render($this->cellValueTemplates[$renderingType], [
                'table' => $table,
                'cell' => $cell
            ]);
        }

        if (isset($this->cellValueBlocks[$renderingType])) {
            return $this->getTemplate()->renderBlock($this->cellValueBlocks[$renderingType], [
                'table' => $table,
                'cell' => $cell
            ]);
        }

        return (string) $cell->getValue();
    }

    /**
     * @param string $route
     * @param array<int|string, int|string> $params
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
    public function registerCellValueBlock(string $renderingType, string $blockName): void
    {
        $this->cellValueBlocks[$renderingType] = $blockName;
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
