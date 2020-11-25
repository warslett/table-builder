<?php

declare(strict_types=1);

namespace WArslett\TableBuilder;

class Action
{
    private string $label;
    private ?string $route = null;
    private array $routeParams = [];

    public function __construct(string $label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return string|null
     */
    public function getRoute(): ?string
    {
        return $this->route;
    }

    /**
     * @return array
     */
    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    /**
     * @param string $route
     * @param array $routeParams
     * @return void
     */
    public function setRoute(string $route, array $routeParams = [])
    {
        $this->route = $route;
        $this->routeParams = $routeParams;
    }
}
