<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests;

use Throwable;
use Twig\Environment;
use WArslett\TableBuilder\Column\TextColumn;
use WArslett\TableBuilder\DataAdapter\ArrayDataAdapter;
use WArslett\TableBuilder\Exception\DataAdapterException;
use WArslett\TableBuilder\Exception\NoDataAdapterException;
use WArslett\TableBuilder\Renderer\Html\TwigRenderer;
use WArslett\TableBuilder\RequestAdapter\ArrayRequestAdapter;
use WArslett\TableBuilder\Table;
use WArslett\TableBuilder\TableBuilderFactory;
use WArslett\TableBuilder\Twig\StandardTemplatesLoader;
use WArslett\TableBuilder\Twig\TableRendererExtension;
use WArslett\TableBuilder\ValueAdapter\PropertyAccessAdapter;

class TwigRendererIntegrationTest extends TestCase
{

    /**
     * @return void
     * @throws Throwable
     */
    public function testRenderTable()
    {
        $table = $this->buildTable();

        $twigRenderer = $this->buildRenderer('table-builder/bootstrap4.html.twig');

        $output = $twigRenderer->renderTable($table);

        $resourcePath = __DIR__ . "/resources/twig_renderer/bootstrap4/expected_table.html";
        $resource = fopen($resourcePath, 'r');

        $this->assertSame(trim(fread($resource, filesize($resourcePath))), trim($output));
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testRenderTableRowsPerPageOptions()
    {
        $table = $this->buildTable();

        $twigRenderer = $this->buildRenderer('table-builder/bootstrap4.html.twig');

        $output = $twigRenderer->renderTableRowsPerPageOptions($table);

        $resourcePath = __DIR__ . "/resources/twig_renderer/bootstrap4/expected_table_rows_per_page_options.html";
        $resource = fopen($resourcePath, 'r');

        $this->assertSame(trim(fread($resource, filesize($resourcePath))), trim($output));
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testRenderTableElement()
    {
        $table = $this->buildTable();

        $twigRenderer = $this->buildRenderer('table-builder/bootstrap4.html.twig');

        $output = $twigRenderer->renderTableElement($table);

        $resourcePath = __DIR__ . "/resources/twig_renderer/bootstrap4/expected_table_element.html";
        $resource = fopen($resourcePath, 'r');

        $this->assertSame(trim(fread($resource, filesize($resourcePath))), trim($output));
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testRenderTableHeading()
    {
        $table = $this->buildTable();
        $heading = $table->getHeadings()['foo'];

        $twigRenderer = $this->buildRenderer('table-builder/bootstrap4.html.twig');

        $output = $twigRenderer->renderTableHeading($table, $heading);

        $resourcePath = __DIR__ . "/resources/twig_renderer/bootstrap4/expected_table_heading.html";
        $resource = fopen($resourcePath, 'r');

        $this->assertSame(trim(fread($resource, filesize($resourcePath))), trim($output));
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testRenderTableRow()
    {
        $table = $this->buildTable();
        $row = $table->getRows()[0];

        $twigRenderer = $this->buildRenderer('table-builder/bootstrap4.html.twig');

        $output = $twigRenderer->renderTableRow($table, $row);

        $resourcePath = __DIR__ . "/resources/twig_renderer/bootstrap4/expected_table_row.html";
        $resource = fopen($resourcePath, 'r');

        $this->assertSame(trim(fread($resource, filesize($resourcePath))), trim($output));
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testRenderTableCell()
    {
        $table = $this->buildTable();
        $row = $table->getRows()[0];
        $cell = $row['foo'];

        $twigRenderer = $this->buildRenderer('table-builder/bootstrap4.html.twig');

        $output = $twigRenderer->renderTableCell($table, $row, $cell);

        $resourcePath = __DIR__ . "/resources/twig_renderer/bootstrap4/expected_table_cell.html";
        $resource = fopen($resourcePath, 'r');

        $this->assertSame(trim(fread($resource, filesize($resourcePath))), trim($output));
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testRenderTablePagination()
    {
        $table = $this->buildTable();

        $twigRenderer = $this->buildRenderer('table-builder/bootstrap4.html.twig');

        $output = $twigRenderer->renderTablePagination($table);

        $resourcePath = __DIR__ . "/resources/twig_renderer/bootstrap4/expected_table_pagination.html";
        $resource = fopen($resourcePath, 'r');

        $this->assertSame(trim(fread($resource, filesize($resourcePath))), trim($output));
    }

    /**
     * @return Table
     * @throws DataAdapterException
     * @throws NoDataAdapterException
     */
    private function buildTable(): Table
    {
        $tableBuilderFactory = new TableBuilderFactory();
        return $tableBuilderFactory->createTableBuilder()
            ->setRowsPerPageOptions([2, 10, 20])
            ->setDefaultRowsPerPage(2)
            ->addColumn(TextColumn::withName('foo')
                ->setLabel('Foo')
                ->setValueAdapter(PropertyAccessAdapter::withPropertyPath('[foo]')))
            ->buildTable('user_table')
            ->setDataAdapter(ArrayDataAdapter::withArray([
                ['foo' => 'bar'],
                ['foo' => 'baz'],
                ['foo' => 'qux']
            ]))
            ->handleRequest(ArrayRequestAdapter::withArray([]));
    }

    /**
     * @param string $templatePath
     * @return TwigRenderer
     */
    private function buildRenderer(string $templatePath): TwigRenderer
    {
        $twigEnvironment = new Environment(new StandardTemplatesLoader());
        $twigRenderer = new TwigRenderer($twigEnvironment, $templatePath);
        $twigEnvironment->addExtension(new TableRendererExtension($twigRenderer));
        return $twigRenderer;
    }
}
