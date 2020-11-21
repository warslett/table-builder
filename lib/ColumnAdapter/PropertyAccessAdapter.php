<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\ColumnAdapter;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

final class PropertyAccessAdapter implements StringColumnAdapterInterface
{
    private string $propertyPath;
    private PropertyAccessor $propertyAccessor;

    public function __construct(string $propertyPath, ?PropertyAccessor $propertyAccessor = null)
    {
        $this->propertyPath = $propertyPath;
        $this->propertyAccessor = $propertyAccessor ?? PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param mixed $row
     * @return string
     */
    public function getStringValue($row): string
    {
        return (string) $this->propertyAccessor->getValue($row, $this->propertyPath);
    }

    /**
     * @param string $propertyPath
     * @return self
     */
    public static function withPropertyPath(string $propertyPath): self
    {
        return new PropertyAccessAdapter($propertyPath);
    }
}
