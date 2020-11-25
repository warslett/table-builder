<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\RouteGeneratorAdapter;

interface RouteGeneratorAdapterInterface
{

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    public function renderRoute(string $route, array $params = []): string;
}
