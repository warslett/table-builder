<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\RouteGeneratorAdapter;

final class SprintfAdapter implements RouteGeneratorAdapterInterface
{
    /**
     * @param string $route
     * @param array<int|string, int|string> $params
     * @return string
     */
    public function renderRoute(string $route, array $params = []): string
    {
        return sprintf($route, ...$params);
    }
}
