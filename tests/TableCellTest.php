<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests;

use stdClass;
use WArslett\TableBuilder\Exception\ValueException;
use WArslett\TableBuilder\TableCell;

class TableCellTest extends TestCase
{

    public function testConstructNonStringableValueThrowsException()
    {
        $this->expectException(ValueException::class);

        new TableCell('foo', new stdClass());
    }
}
