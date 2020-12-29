<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests\Twig;

use Exception;
use Twig\Node\Node;
use WArslett\TableBuilder\Renderer\Html\HtmlTableRendererInterface;
use WArslett\TableBuilder\Twig\TableRendererExtension;
use WArslett\TableBuilder\Tests\TestCase;
use Mockery as m;
use Mockery\Mock;

class TableRendererExtensionTest extends TestCase
{
    public function requiredFunctions()
    {
        return [
            'table' => ['table', 'renderTable'],
            'table_rows_per_page_options' => ['table_rows_per_page_options', 'renderTableRowsPerPageOptions'],
            'table_element' => ['table_element', 'renderTableElement'],
            'table_heading' => ['table_heading', 'renderTableHeading'],
            'table_row' => ['table_row', 'renderTableRow'],
            'table_cell' => ['table_cell', 'renderTableCell'],
            'table_cell_value' => ['table_cell_value', 'renderTableCellValue'],
            'table_route' => ['table_route', 'renderTableRoute'],
            'table_pagination' => ['table_pagination', 'renderTablePagination'],
        ];
    }

    /**
     * @dataProvider requiredFunctions
     * @param string $functionName
     * @param string $mappedMethod
     * @return void
     * @throws Exception
     */
    public function testHasFunction(string $functionName, string $mappedMethod)
    {
        $renderer = $this->mockRenderer();
        $extension = new TableRendererExtension($renderer);

        $functions = $extension->getFunctions();

        foreach ($functions as $function) {
            if ($function->getName() === $functionName) {
                $this->addToAssertionCount(1);
                $this->assertSame(['html'], $function->getSafe($this->mockNode()));
                $callable = $function->getCallable();
                $this->assertIsCallable($callable);
                $this->assertSame([$renderer, $mappedMethod], $callable);
                return;
            }
        }

        throw new Exception("No function named \"$functionName\"");
    }

    /**
     * @return HtmlTableRendererInterface&Mock
     */
    private function mockRenderer(): HtmlTableRendererInterface
    {
        return m::mock(HtmlTableRendererInterface::class);
    }

    /**
     * @return Node&Mock
     */
    private function mockNode(): Node
    {
        return m::mock(Node::class);
    }
}
