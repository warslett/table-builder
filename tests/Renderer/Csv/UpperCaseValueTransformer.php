<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests\Renderer\Csv;

use WArslett\TableBuilder\Renderer\Csv\CsvCellValueTransformerInterface;

final class UpperCaseValueTransformer implements CsvCellValueTransformerInterface
{
    /**
     * @param mixed $value
     * @return string
     */
    public function transformForCsv($value)
    {
        return strtoupper((string) $value);
    }
}
