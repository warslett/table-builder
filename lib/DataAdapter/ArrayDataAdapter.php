<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\DataAdapter;

final class ArrayDataAdapter implements DataAdapterInterface
{
    /** @var array */
    private array $array;

    /** @var array<string, callable> */
    private array $sortToggleMapping = [];

    public function __construct(array $array)
    {
        $this->array = $array;
    }

    /**
     * @param int $pageNumber
     * @param int $rowsPerPage
     * @param string|null $sortToggle
     * @param bool $isSortedDescending
     * @return array
     */
    public function getPage(
        int $pageNumber,
        int $rowsPerPage,
        ?string $sortToggle = null,
        bool $isSortedDescending = false
    ): array {
        $array = $this->array;

        if (null !== $sortToggle && isset($this->sortToggleMapping[$sortToggle])) {
            $callable = $this->sortToggleMapping[$sortToggle];
            usort($array, function ($a, $b) use ($callable, $isSortedDescending) {
                $result = $callable($a, $b);
                return $isSortedDescending ? 0 - $result : $result;
            });
        }

        return array_slice($array, ($pageNumber - 1) * $rowsPerPage, $rowsPerPage);
    }

    /**
     * @return int
     */
    public function countTotalRows(): int
    {
        return count($this->array);
    }

    public function canSort(string $sortToggle): bool
    {
        return isset($this->sortToggleMapping[$sortToggle]);
    }

    /**
     * @param string $sortToggle
     * @param callable $callable
     * @return $this
     */
    public function mapSortToggle(string $sortToggle, callable $callable): self
    {
        $this->sortToggleMapping[$sortToggle] = $callable;
        return $this;
    }

    /**
     * @param array $array
     * @return $this
     */
    public static function withArray(array $array): self
    {
        return new self($array);
    }
}
