<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\ValueAdapter;

interface ValueAdapterInterface
{
    /**
     * @param mixed $row
     * @return mixed
     */
    public function getValue($row);
}
