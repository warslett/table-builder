<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Column;

use WArslett\TableBuilder\TableCell;
use WArslett\TableBuilder\TableHeading;

interface ColumnInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string|null
     */
    public function getSortToggle(): ?string;

    /**
     * @return TableHeading
     */
    public function buildTableHeading(): TableHeading;

    /**
     * @param mixed $row
     * @return TableCell
     */
    public function buildTableCell($row): TableCell;
}
