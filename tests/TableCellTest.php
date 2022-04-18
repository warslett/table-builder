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

        new TableCell('foo', 'bar', new stdClass());
    }

    public function testGetAttributeDoesntExistReturnsDefault()
    {
        $cell = new TableCell('foo', 'bar', 'value');
        $default = 'quux';

        $attribute = $cell->getAttribute('qux', $default);

        $this->assertSame($default, $attribute);
    }

    public function testGetAttributeExistsReturnsAttribute()
    {
        $cell = new TableCell('foo', 'bar', 'value');
        $value = 'quuz';
        $cell->setAttribute('qux', $value);

        $attribute = $cell->getAttribute('qux', 'quux');

        $this->assertSame($value, $attribute);
    }
}
