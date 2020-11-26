<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\RequestAdapter;

interface RequestAdapterInterface
{
    public const SORT_ASCENDING = 'asc';
    public const SORT_DESCENDING = 'desc';

    /**
     * @return array - all query parameters on the request as multidimensional assoc array
     */
    public function getParameters(): array;
}
