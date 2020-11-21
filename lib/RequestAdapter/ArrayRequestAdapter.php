<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\RequestAdapter;

final class ArrayRequestAdapter implements RequestAdapterInterface
{
    private array $array;

    public function __construct(array $array)
    {
        $this->array = $array;
    }

    /**
     * @param array $array
     * @return self
     */
    public static function withArray(array $array): self
    {
        return new self($array);
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getParameter(string $name)
    {
        return $this->array[$name] ?? null;
    }
}
