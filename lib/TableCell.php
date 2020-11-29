<?php

declare(strict_types=1);

namespace WArslett\TableBuilder;

use JsonSerializable;
use WArslett\TableBuilder\Exception\ValueException;

class TableCell implements JsonSerializable
{
    /** @var string */
    private string $name;

    /** @var string */
    private string $renderingType;

    /** @var mixed */
    private $value;

    /**
     * @param string $name
     * @param string $renderingType
     * @param mixed $value
     * @throws ValueException
     */
    public function __construct(string $name, string $renderingType, $value)
    {
        $this->name = $name;

        /* TableCell value must be capable of casting as a string without blowing up. This guarantees that renderers can
           always fall back to rendering as a string. From PHP 8 we will be able to replace this with a union type
           typehint incorporating Stringable interface */
        if (
            false === is_scalar($value)
            && false === method_exists($value, '__toString')
            && $value !== null
        ) {
            throw new ValueException("Failed constructing TableCell with value that cannot be cast to string");
        }

        $this->renderingType = $renderingType;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getRenderingType(): string
    {
        return $this->renderingType;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'rendering_type' => $this->renderingType,
            'value' => $this->value
        ];
    }
}
