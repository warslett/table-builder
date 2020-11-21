<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\DataAdapter;

use WArslett\TableBuilder\Exception\DataAdapterException;

interface DataAdapterInterface
{

    /**
     * @param int $pageNumber
     * @param int $rowsPerPage
     * @return array
     * @throws DataAdapterException
     */
    public function getPage(int $pageNumber, int $rowsPerPage): array;

    /**
     * @return int
     * @throws DataAdapterException
     */
    public function countTotalRows(): int;
}
