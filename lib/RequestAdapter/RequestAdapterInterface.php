<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\RequestAdapter;

interface RequestAdapterInterface
{

    /**
     * @return array - all query parameters on the request as multidimensional assoc array
     */
    public function getParameters(): array;
}
