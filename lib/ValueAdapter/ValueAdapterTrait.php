<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\ValueAdapter;

use Closure;
use WArslett\TableBuilder\Column\AbstractColumn;
use WArslett\TableBuilder\Exception\NoValueAdapterException;

/**
 * @psalm-require-extends \WArslett\TableBuilder\Column\AbstractColumn
 */
trait ValueAdapterTrait
{
    private ?ValueAdapterInterface $valueAdapter = null;

    /**
     * Sets the value adapter for the column which is any class that implements ValueAdapterInterface and can retrieve
     * a cell value from a row of data.
     *
     * @param ValueAdapterInterface $valueAdapter
     * @return $this
     */
    public function valueAdapter(ValueAdapterInterface $valueAdapter): self
    {
        $this->valueAdapter = $valueAdapter;
        return $this;
    }

    /**
     * Sets the value adapter for the column to the PropertyAccessAdapter which returns the value of the property at the
     * given path for each row
     *
     * @codeCoverageIgnore - just an adapter
     * @param string $propertyPath - the path to the property eg. to access $object->getId() use 'id' to access
     *                               $array['foo'] use the path '[foo]'
     * @return $this
     */
    public function property(string $propertyPath): self
    {
        return $this->valueAdapter(PropertyAccessAdapter::withPropertyPath($propertyPath));
    }

    /**
     * Sets the value adapter for the column to the CallbackAdapter which retrieves the value of the cell for each row
     * by passing the row to the given closure.
     *
     * @codeCoverageIgnore - just an adapter
     * @param Closure $closure - a closure that retrieves the value for the cell
     * @return $this
     */
    public function callback(Closure $closure): self
    {
        return $this->valueAdapter(CallbackAdapter::withCallback($closure));
    }

    /**
     * @return void
     * @throws NoValueAdapterException
     */
    private function assertHasValueAdapter(): void
    {
        if (null === $this->valueAdapter) {
            throw new NoValueAdapterException(
                sprintf("Cannot handle request until value adapter has been set for %s", $this->name)
            );
        }
    }

    /**
     * @param string $property
     * @return static
     */
    public static function withProperty(string $property): self
    {
        /** @var static $column */
        $column = self::withName($property);
        $column->property($property);
        return $column;
    }
}
