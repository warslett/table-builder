<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests\Renderer\Csv;

use Exception;
use League\Csv\Writer;
use WArslett\TableBuilder\Column\TextColumn;
use WArslett\TableBuilder\DataAdapter\ArrayDataAdapter;
use WArslett\TableBuilder\Exception\DataAdapterException;
use WArslett\TableBuilder\Exception\NoDataAdapterException;
use WArslett\TableBuilder\Exception\SortToggleException;
use WArslett\TableBuilder\Exception\ValueException;
use WArslett\TableBuilder\Renderer\Csv\CsvRenderer;
use WArslett\TableBuilder\RequestAdapter\ArrayRequestAdapter;
use WArslett\TableBuilder\Table;
use WArslett\TableBuilder\TableBuilderFactory;
use WArslett\TableBuilder\TableBuilderInterface;
use WArslett\TableBuilder\Tests\TestCase;
use WArslett\TableBuilder\ValueAdapter\PropertyAccessAdapter;

class CsvRendererIntegrationTest extends TestCase
{
    private const EXPECTATION_RESOURCES_DIR = __DIR__ . "/../../resources/expectations/csv/";

    /**
     * @return void
     * @throws Exception
     */
    public function testRenderTableRendersCsv(): void
    {
        $table = $this->buildTable($this->getTableBuilder());
        $renderer = new CsvRenderer();

        $csv = Writer::createFromString();

        $renderer->renderTable($table, $csv);

        $content = $csv->getContent();
        $this->assertOutputEquivalentToResource(self::EXPECTATION_RESOURCES_DIR . 'table.csv', $content);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testRenderTableIncludeHeaderFalseExcludesHeader(): void
    {
        $table = $this->buildTable($this->getTableBuilder());
        $renderer = new CsvRenderer();

        $csv = Writer::createFromString();

        $renderer
            ->includeHeader(false)
            ->renderTable($table, $csv);

        $content = $csv->getContent();
        $this->assertOutputEquivalentToResource(self::EXPECTATION_RESOURCES_DIR . 'table_exclude_header.csv', $content);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testRenderTableWithValueTransformerTransformsValue(): void
    {
        $table = $this->buildTable($this->getTableBuilder());
        $renderer = new CsvRenderer();

        $csv = Writer::createFromString();

        $renderer
            ->registerCellValueTransformer(TextColumn::class, new UpperCaseValueTransformer())
            ->renderTable($table, $csv);

        $content = $csv->getContent();
        $this->assertOutputEquivalentToResource(
            self::EXPECTATION_RESOURCES_DIR . 'table_transformed_cell_values.csv',
            $content
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testRenderTableWithValueTransformerTransformsInvalidValueThrowsException(): void
    {
        $table = $this->buildTable($this->getTableBuilder());
        $renderer = new CsvRenderer();

        $csv = Writer::createFromString();

        $this->expectException(ValueException::class);
        $this->expectExceptionMessage(sprintf("Invalid value transformed from %s", InvalidValueTransformer::class));

        $renderer
            ->registerCellValueTransformer(TextColumn::class, new InvalidValueTransformer())
            ->renderTable($table, $csv);
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
                    ['foo' => 'baz', 'bar' => 'foo'],
                    ['foo' => 'qux', 'bar' => 'foo bar']
                ])
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
            ->add(TextColumn::withName('foo')
                ->label('Foo')
                ->valueAdapter(PropertyAccessAdapter::withPropertyPath('[foo]')))
            ->add(TextColumn::withName('bar')
                ->label('Bar')
                ->valueAdapter(PropertyAccessAdapter::withPropertyPath('[bar]')));
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
        $resourceContents = fread($resource, filesize($resourcePath));

        $output = trim($output);
        $resourceContents = trim($resourceContents);

        $this->assertSame($resourceContents, $output);
    }
}
