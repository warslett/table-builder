<?php

declare(strict_types=1);

namespace WArslett\TableBuilder;

use JsonSerializable;

class Action implements JsonSerializable
{
    /** @var string */
    private $label;

    /** @var string|null */
    private $route = null;

    /** @var array */
    private $routeParams = [];

    /** @var array */
    private $attributes;

    public function __construct(string $label, array $attributes = [])
    {
        $this->label = $label;
        $this->attributes = $attributes;
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

    /**
     * @param string $attribute
     * @param mixed $default
     * @return mixed
     */
    public function getAttribute(string $attribute, $default = null)
    {
        return $this->attributes[$attribute] ?? $default;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'label' => $this->label,
            'route' => $this->route,
            'route_params' => $this->routeParams
        ];
    }
}
