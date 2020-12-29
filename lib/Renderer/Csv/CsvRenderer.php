<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Renderer\Csv;

use League\Csv\CannotInsertRecord;
use League\Csv\Writer;
use WArslett\TableBuilder\Exception\ValueException;
use WArslett\TableBuilder\Table;
use WArslett\TableBuilder\TableCell;
use WArslett\TableBuilder\TableHeading;

class CsvRenderer
{
    /** @var bool */
    private bool $includeHeader = true;

    /** @var array<string, CsvCellValueTransformerInterface> */
    private array $cellValueTransformers = [];

    /**
     * @param Table $table
     * @param Writer $csv
     * @return void
     * @throws CannotInsertRecord
     * @throws ValueException
     */
    public function renderTable(Table $table, Writer $csv): void
    {
        if ($this->includeHeader) {
            $csv->insertOne(array_map(fn(TableHeading $heading) => $heading->getLabel(), $table->getHeadings()));
        }

        foreach ($table->getRows() as $row) {
            $csv->insertOne(array_map(function (TableCell $cell) {
                if (isset($this->cellValueTransformers[$cell->getRenderingType()])) {
                    $transformer = $this->cellValueTransformers[$cell->getRenderingType()];
                    $transformedValue = $transformer->transformForCsv($cell->getValue());

                    if (false === is_scalar($transformedValue)) {
                        throw new ValueException(sprintf("Invalid value transformed from %s", get_class($transformer)));
                    }

                    return $transformedValue;
                }

                return $cell->getValue();
            }, $row));
        }
    }

    /**
     * @param bool $includeHeader
     * @return $this
     */
    public function includeHeader(bool $includeHeader): self
    {
        $this->includeHeader = $includeHeader;
        return $this;
    }

    /**
     * @param string $renderingType
     * @param CsvCellValueTransformerInterface $transformer
     * @return $this
     */
    public function registerCellValueTransformer(
        string $renderingType,
        CsvCellValueTransformerInterface $transformer
    ): self {
        $this->cellValueTransformers[$renderingType] = $transformer;
        return $this;
    }
}
