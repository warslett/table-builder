<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\DataAdapter;

use WArslett\TableBuilder\Exception\DataAdapterException;

interface DataAdapterInterface
{

    /**
     * @param int $pageNumber
     * @param int $rowsPerPage
     * @param string|null $sortToggle
     * @param bool $isSortedDescending
     * @return array
     * @throws DataAdapterException
     */
    public function getPage(
        int $pageNumber,
        int $rowsPerPage,
        ?string $sortToggle = null,
        bool $isSortedDescending = false
    ): array;

    /**
     * @return int
     * @throws DataAdapterException
     */
    public function countTotalRows(): int;

    /**
     * @param string $sortToggle
     * @return bool
     */
    public function canSort(string $sortToggle): bool;
}
