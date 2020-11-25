<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\RouteGeneratorAdapter;

final class SprintfAdapter implements RouteGeneratorAdapterInterface
{
    public function renderRoute(string $route, array $params = []): string
    {
        return sprintf($route, ...$params);
    }
}
