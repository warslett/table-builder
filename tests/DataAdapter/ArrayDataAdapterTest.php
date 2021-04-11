<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests\DataAdapter;

use WArslett\TableBuilder\DataAdapter\ArrayDataAdapter;
use WArslett\TableBuilder\Tests\TestCase;

class ArrayDataAdapterTest extends TestCase
{

    public function testGetPageNoSortToggleGetsPageOfResult()
    {
        $dataAdapter = ArrayDataAdapter::withArray([
            ['foo' => 'bar'],
            ['foo' => 'baz'],
            ['foo' => 'qux'],
        ]);

        $data = $dataAdapter->getPage(2, 2);

        $this->assertSame([['foo' => 'qux']], $data);
    }

    public function testGetPageSortsResults()
    {
        $dataAdapter = ArrayDataAdapter::withArray([
            ['foo' => 3],
            ['foo' => 1],
            ['foo' => 2],
            ['foo' => 4],
        ])->mapSortToggle('foo', function ($a, $b) {
            return $a['foo'] < $b['foo'] ? -1 : 1;
        });

        $data = $dataAdapter->getPage(1, 4, 'foo');

        $this->assertSame([
            ['foo' => 1],
            ['foo' => 2],
            ['foo' => 3],
            ['foo' => 4]
        ], $data);
    }

    public function testGetPageSortsResultsDescending()
    {
        $dataAdapter = ArrayDataAdapter::withArray([
            ['foo' => 3],
            ['foo' => 1],
            ['foo' => 2],
            ['foo' => 4],
        ])->mapSortToggle('foo', function ($a, $b) {
            return $a['foo'] < $b['foo'] ? -1 : 1;
        });

        $data = $dataAdapter->getPage(1, 4, 'foo', true);

        $this->assertSame([
            ['foo' => 4],
            ['foo' => 3],
            ['foo' => 2],
            ['foo' => 1]
        ], $data);
    }

    public function testCountTotalRows()
    {
        $dataAdapter = ArrayDataAdapter::withArray([
            ['foo' => 3],
            ['foo' => 1],
            ['foo' => 2],
            ['foo' => 4],
        ]);

        $count = $dataAdapter->countTotalRows();

        $this->assertSame(4, $count);
    }

    public function testCanSortUnMappedSortToggleIsFalse()
    {
        $dataAdapter = ArrayDataAdapter::withArray([]);

        $this->assertFalse($dataAdapter->canSort('foo'));
    }

    public function testCanSortMappedSortToggleIsTrue()
    {
        $dataAdapter = ArrayDataAdapter::withArray([])->mapSortToggle('foo', function ($a, $b) {
            return 0;
        });

        $this->assertTrue($dataAdapter->canSort('foo'));
    }
}
