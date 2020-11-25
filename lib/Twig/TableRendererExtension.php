<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use WArslett\TableBuilder\Renderer\Html\HtmlTableRendererInterface;

final class TableRendererExtension extends AbstractExtension
{
    private HtmlTableRendererInterface $htmlTableRenderer;

    public function __construct(HtmlTableRendererInterface $htmlTableRenderer)
    {
        $this->htmlTableRenderer = $htmlTableRenderer;
    }

    /**
     * @return array<TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'table',
                [$this->htmlTableRenderer, 'renderTable'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'table_rows_per_page_options',
                [$this->htmlTableRenderer, 'renderTableRowsPerPageOptions'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'table_element',
                [$this->htmlTableRenderer, 'renderTableElement'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'table_heading',
                [$this->htmlTableRenderer, 'renderTableHeading'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'table_row',
                [$this->htmlTableRenderer, 'renderTableRow'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'table_cell',
                [$this->htmlTableRenderer, 'renderTableCell'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'table_cell_value',
                [$this->htmlTableRenderer, 'renderTableCellValue'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'table_route',
                [$this->htmlTableRenderer, 'renderTableRoute'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'table_pagination',
                [$this->htmlTableRenderer, 'renderTablePagination'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
