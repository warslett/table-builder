<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests\Renderer\Html;

use Exception;
use SimpleXMLElement;
use Throwable;
use Twig\Environment;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use WArslett\TableBuilder\ActionBuilder;
use WArslett\TableBuilder\Column\ActionGroupColumn;
use WArslett\TableBuilder\Column\BooleanColumn;
use WArslett\TableBuilder\Column\TextColumn;
use WArslett\TableBuilder\DataAdapter\ArrayDataAdapter;
use WArslett\TableBuilder\Exception\DataAdapterException;
use WArslett\TableBuilder\Exception\NoDataAdapterException;
use WArslett\TableBuilder\Exception\SortToggleException;
use WArslett\TableBuilder\Renderer\Html\TwigRenderer;
use WArslett\TableBuilder\RequestAdapter\ArrayRequestAdapter;
use WArslett\TableBuilder\RouteGeneratorAdapter\SprintfAdapter;
use WArslett\TableBuilder\Table;
use WArslett\TableBuilder\TableBuilderFactory;
use WArslett\TableBuilder\TableBuilderInterface;
use WArslett\TableBuilder\Tests\TestCase;
use WArslett\TableBuilder\Twig\StandardTemplatesLoader;
use WArslett\TableBuilder\Twig\TableRendererExtension;
use WArslett\TableBuilder\ValueAdapter\CallbackAdapter;
use WArslett\TableBuilder\ValueAdapter\PropertyAccessAdapter;

class TwigRendererIntegrationTest extends TestCase
{
    private const EXPECTATION_RESOURCES_DIR = __DIR__ . "/../../resources/expectations/html/";

    public function getTestData(): array
    {
        return [
            'standard' => ['template' => 'standard'],
            'bootstrap4' => ['template' => 'bootstrap4'],
        ];
    }

    /**
     * @dataProvider getTestData
     * @param string $template
     * @return void
     * @throws Throwable
     */
    public function testRenderTable(string $template)
    {
        $table = $this->buildTable($this->getTableBuilder());
        $twigRenderer = $this->buildRenderer("table-builder/$template.html.twig");

        $output = $twigRenderer->renderTable($table);

        $resourcePath = self::EXPECTATION_RESOURCES_DIR . "$template/expected_table.html";
        $this->assertOutputEquivalentToResource($resourcePath, $output);
    }

    /**
     * @dataProvider getTestData
     * @param string $template
     * @return void
     * @throws Throwable
     */
    public function testRenderTableRowsPerPageOptions(string $template)
    {
        $table = $this->buildTable($this->getTableBuilder());
        $twigRenderer = $this->buildRenderer("table-builder/$template.html.twig");

        $output = $twigRenderer->renderTableRowsPerPageOptions($table);

        $resourcePath = self::EXPECTATION_RESOURCES_DIR . "$template/expected_table_rows_per_page_options.html";
        $this->assertOutputEquivalentToResource($resourcePath, $output);
    }

    /**
     * @dataProvider getTestData
     * @param string $template
     * @return void
     * @throws Throwable
     */
    public function testRenderTableElement(string $template)
    {
        $table = $this->buildTable($this->getTableBuilder());
        $twigRenderer = $this->buildRenderer("table-builder/$template.html.twig");

        $output = $twigRenderer->renderTableElement($table);

        $resourcePath = self::EXPECTATION_RESOURCES_DIR . "$template/expected_table_element.html";
        $this->assertOutputEquivalentToResource($resourcePath, $output);
    }


    /**
     * @dataProvider getTestData
     * @param string $template
     * @return void
     * @throws Throwable
     */
    public function testRenderTableHeading(string $template)
    {
        $table = $this->buildTable($this->getTableBuilder());
        $heading = $table->getHeadings()['foo'];

        $twigRenderer = $this->buildRenderer("table-builder/$template.html.twig");

        $output = $twigRenderer->renderTableHeading($table, $heading);

        $resourcePath = self::EXPECTATION_RESOURCES_DIR . "$template/expected_table_heading.html";

        $this->assertOutputEquivalentToResource($resourcePath, $output);
    }

    /**
     * @dataProvider getTestData
     * @param string $template
     * @return void
     * @throws Throwable
     */
    public function testRenderTableRow(string $template)
    {
        $table = $this->buildTable($this->getTableBuilder());
        $row = $table->getRows()[0];
        $twigRenderer = $this->buildRenderer("table-builder/$template.html.twig");

        $output = $twigRenderer->renderTableRow($table, $row);

        $resourcePath = self::EXPECTATION_RESOURCES_DIR . "$template/expected_table_row.html";
        $this->assertOutputEquivalentToResource($resourcePath, $output);
    }

    /**
     * @dataProvider getTestData
     * @param string $template
     * @return void
     * @throws Throwable
     */
    public function testRenderTableCell(string $template)
    {
        $table = $this->buildTable($this->getTableBuilder());
        $row = $table->getRows()[0];
        $cell = $row['foo'];
        $twigRenderer = $this->buildRenderer("table-builder/$template.html.twig");

        $output = $twigRenderer->renderTableCell($table, $cell);

        $resourcePath = self::EXPECTATION_RESOURCES_DIR . "$template/expected_table_cell.html";
        $this->assertOutputEquivalentToResource($resourcePath, $output);
    }

    /**
     * @dataProvider getTestData
     * @param string $template
     * @return void
     * @throws Throwable
     */
    public function testRenderTableCellValueActionGroupColumnRenderingType(string $template)
    {
        $builder = $this->getTableBuilder()
            ->add(ActionGroupColumn::withName('actions')
                ->label('Actions')
                ->add(ActionBuilder::withName('delete')
                    ->label('Delete')
                    ->route('/delete/%s', [PropertyAccessAdapter::withPropertyPath('[foo]')])));

        $table = $this->buildTable($builder);
        $row = $table->getRows()[0];
        $cell = $row['actions'];
        $renderer = $this->buildRenderer("table-builder/$template.html.twig");

        $output = $renderer->renderTableCellValue($table, $cell);

        $resourcePath = self::EXPECTATION_RESOURCES_DIR . "$template/expected_action_group_cell_value.html";
        $this->assertOutputEquivalentToResource($resourcePath, $output);
    }

    /**
     * @dataProvider getTestData
     * @param string $template
     * @return void
     * @throws Throwable
     */
    public function testRenderTableCellValueBooleanColumnRenderingType(string $template)
    {
        $builder = $this->getTableBuilder()
            ->add(BooleanColumn::withName('boolean')
                ->label('Boolean')
                ->valueAdapter(CallbackAdapter::withCallback(fn($row): bool => false)));

        $table = $this->buildTable($builder);
        $row = $table->getRows()[0];
        $cell = $row['boolean'];
        $renderer = $this->buildRenderer("table-builder/$template.html.twig");

        $output = $renderer->renderTableCellValue($table, $cell);

        $resourcePath = self::EXPECTATION_RESOURCES_DIR . "$template/expected_boolean_cell_value.html";
        $this->assertOutputEquivalentToResource($resourcePath, $output);
    }


    /**
     * @dataProvider getTestData
     * @param string $template
     * @return void
     * @throws Throwable
     */
    public function testRenderTableCellValueNoBlockOrTemplateForRenderingType(string $template)
    {
        $table = $this->buildTable($this->getTableBuilder());
        $row = $table->getRows()[0];
        $cell = $row['foo'];
        $twigRenderer = $this->buildRenderer("table-builder/$template.html.twig");

        $output = $twigRenderer->renderTableCellValue($table, $cell);

        $this->assertSame('bar', $output);
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testRenderTableCellValueWithCellValueBlockRendersWithCellValueBlock()
    {
        $table = $this->buildTable($this->getTableBuilder());
        $row = $table->getRows()[0];
        $cell = $row['foo'];
        $twigRenderer = $this->buildRenderer('bootstrap4_with_cellvalue_block.html.twig');
        $twigRenderer->registerCellValueBlock(TextColumn::class, 'my_cell_value_block');

        $output = $twigRenderer->renderTableCellValue($table, $cell);

        $this->assertSame('MY_BLOCKbarMY_BLOCK', $output);
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testRenderTableCellValueWithCellValueTemplateRendersWithCellValueTemplate()
    {
        $table = $this->buildTable($this->getTableBuilder());
        $row = $table->getRows()[0];
        $cell = $row['foo'];
        $twigRenderer = $this->buildRenderer('table-builder/bootstrap4.html.twig');
        $twigRenderer->registerCellValueTemplate(TextColumn::class, 'column_template.html.twig');

        $output = $twigRenderer->renderTableCellValue($table, $cell);

        $this->assertSame('MY_TEMPLATEbarMY_TEMPLATE', $output);
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testRenderTableCellValueWithCellValueTemplateAndCellValueBlockRendersWithTemplate()
    {
        $table = $this->buildTable($this->getTableBuilder());
        $row = $table->getRows()[0];
        $cell = $row['foo'];
        $twigRenderer = $this->buildRenderer('bootstrap4_with_cellvalue_block.html.twig');
        $twigRenderer->registerCellValueBlock(TextColumn::class, 'my_cell_value_block');
        $twigRenderer->registerCellValueTemplate(TextColumn::class, 'column_template.html.twig');

        $output = $twigRenderer->renderTableCellValue($table, $cell);

        $this->assertSame('MY_TEMPLATEbarMY_TEMPLATE', $output);
    }

    /**
     * @dataProvider getTestData
     * @param string $template
     * @return void
     * @throws Throwable
     */
    public function testRenderTablePagination(string $template)
    {
        $table = $this->buildTable($this->getTableBuilder());
        $twigRenderer = $this->buildRenderer("table-builder/$template.html.twig");

        $output = $twigRenderer->renderTablePagination($table);

        $resourcePath = self::EXPECTATION_RESOURCES_DIR . "$template/expected_table_pagination.html";
        $this->assertOutputEquivalentToResource($resourcePath, $output);
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testRenderTableRoute()
    {
        $twigRenderer = $this->buildRenderer('table-builder/bootstrap4.html.twig');

        $output = $twigRenderer->renderTableRoute('/resource/%d', [123]);

        $this->assertSame('/resource/123', $output);
    }

    /**
     * @param TableBuilderInterface $builder
     * @return Table
     * @throws DataAdapterException
     * @throws NoDataAdapterException
     * @throws SortToggleException
     */
    private function buildTable(TableBuilderInterface $builder): Table
    {
        return $builder->buildTable('user_table')
            ->setDataAdapter(
                ArrayDataAdapter::withArray([
                    ['foo' => 'bar'],
                    ['foo' => 'baz'],
                    ['foo' => 'qux']
                ])->mapSortToggle('foo', fn($a, $b) => 0)
            )
            ->handleRequest(ArrayRequestAdapter::withArray([]));
    }

    /**
     * @return TableBuilderInterface
     */
    private function getTableBuilder(): TableBuilderInterface
    {
        $tableBuilderFactory = new TableBuilderFactory();
        return $tableBuilderFactory->createTableBuilder()
            ->rowsPerPageOptions([2, 10, 20])
            ->defaultRowsPerPage(2)
            ->add(TextColumn::withName('foo')
                ->label('Foo')
                ->sortToggle('foo')
                ->valueAdapter(PropertyAccessAdapter::withPropertyPath('[foo]')));
    }

    /**
     * @param string $templatePath
     * @return TwigRenderer
     */
    private function buildRenderer(string $templatePath): TwigRenderer
    {
        $twigEnvironment = new Environment(new ChainLoader([
            new StandardTemplatesLoader(),
            new FilesystemLoader(__DIR__ . '/../../resources/twig_renderer/test_templates')
        ]));
        $twigRenderer = new TwigRenderer($twigEnvironment, new SprintfAdapter(), $templatePath);
        $twigEnvironment->addExtension(new TableRendererExtension($twigRenderer));
        return $twigRenderer;
    }

    /**
     * @param string $resourcePath
     * @param string $output
     * @return void
     * @throws Exception
     */
    private function assertOutputEquivalentToResource(string $resourcePath, string $output)
    {
        $resource = fopen($resourcePath, 'r');
        $resourceHtml = fread($resource, filesize($resourcePath));

        $output = trim($output);
        $resourceHtml = trim($resourceHtml);

        try {
            $outputDom = new SimpleXMLElement("<root>$output</root>");
        } catch (Exception $e) {
            throw new Exception("Failed parsing output dom:\n<root>$output</root>");
        }

        try {
            $resourceDom = new SimpleXMLElement("<root>$resourceHtml</root>");
        } catch (Exception $e) {
            throw new Exception("Failed parsing resource dom:\n<root>$resourceHtml</root>");
        }

        $this->assertEquals($resourceDom, $outputDom);
    }
}
