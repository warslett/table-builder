<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests\Renderer\Csv;

use stdClass;
use WArslett\TableBuilder\Renderer\Csv\CsvCellValueTransformerInterface;

final class InvalidValueTransformer implements CsvCellValueTransformerInterface
{
    /**
     * @param mixed $value
     * @return string
     */
    public function transformForCsv($value)
    {
        return new stdClass();
    }
}
