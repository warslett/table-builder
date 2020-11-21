<?php

declare(strict_types=1);

namespace WArslett\TableBuilder;

class TableCell
{
    private string $renderingType;

    /** @var mixed */
    private $value;

    /**
     * @param string $renderingType
     * @param mixed $value
     */
    public function __construct(string $renderingType, $value)
    {
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
}
