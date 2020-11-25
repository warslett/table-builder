<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\RouteGeneratorAdapter;

use Symfony\Component\Routing\RouterInterface;

final class SymfonyRoutingAdapter implements RouteGeneratorAdapterInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function renderRoute(string $route, array $params = []): string
    {
        return $this->router->generate($route, $params);
    }
}
