<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\RequestAdapter;

interface RequestAdapterInterface
{

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getParameter(string $name);
}
