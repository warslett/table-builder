<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests;

use stdClass;
use WArslett\TableBuilder\Exception\ValueException;
use WArslett\TableBuilder\TableCell;
use WArslett\TableBuilder\Tests\TestCase;
use Mockery as m;
use Mockery\Mock;

class TableCellTest extends TestCase
{

    public function testConstructNonStringableValueThrowsException()
    {
        $this->expectException(ValueException::class);

        $cell = new TableCell('foo', new stdClass());
    }
}
