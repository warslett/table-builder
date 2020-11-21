<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\DataAdapter;

final class ArrayDataAdapter implements DataAdapterInterface
{
    private array $array;

    public function __construct(array $array)
    {
        $this->array = $array;
    }

    /**
     * @param array $array
     * @return $this
     */
    public static function withArray(array $array): self
    {
        return new self($array);
    }

    public function getPage(int $pageNumber, int $rowsPerPage): array
    {
        return array_slice($this->array, ($pageNumber - 1) * $rowsPerPage, $rowsPerPage);
    }

    public function countTotalRows(): int
    {
        return count($this->array);
    }
}
