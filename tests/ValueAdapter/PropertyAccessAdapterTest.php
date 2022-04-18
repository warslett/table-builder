<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests\ValueAdapter;

use Symfony\Component\PropertyAccess\PropertyAccessor;
use WArslett\TableBuilder\ValueAdapter\PropertyAccessAdapter;
use WArslett\TableBuilder\Tests\TestCase;
use Mockery as m;
use Mockery\Mock;

class PropertyAccessAdapterTest extends TestCase
{
    public function testGetValueGetsValueFromPropertyAccessor()
    {
        $propertyPath = 'foo';
        $propertyAccessor = $this->mockPropertyAccessor();
        $adapter = new PropertyAccessAdapter($propertyPath, $propertyAccessor);
        $row = ['foo'];

        $adapter->getValue($row);

        $propertyAccessor->shouldHaveReceived('getValue')->once()->with($row, $propertyPath);
    }

    public function testGetValueReturnsValue()
    {
        $value = 'bar';
        $adapter = new PropertyAccessAdapter('foo', $this->mockPropertyAccessor($value));

        $actual = $adapter->getValue(['foo']);

        $this->assertSame($value, $actual);
    }

    /**
     * @param string $value
     * @return PropertyAccessor&Mock
     */
    private function mockPropertyAccessor($value = '')
    {
        $propertyAccessor = m::mock(PropertyAccessor::class);
        $propertyAccessor->shouldReceive('getValue')->andReturn($value);
        return $propertyAccessor;
    }
}
