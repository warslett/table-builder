<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Renderer\Csv;

interface CsvCellValueTransformerInterface
{

    /**
     * @param mixed $value
     * @return bool|int|float|string|null
     */
    public function transformForCsv($value);
}
