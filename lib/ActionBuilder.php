<?php

declare(strict_types=1);

namespace WArslett\TableBuilder;

use Closure;
use WArslett\TableBuilder\ValueAdapter\ValueAdapterInterface;

final class ActionBuilder implements ActionBuilderInterface
{

    /** @var string */
    private string $name;

    /** @var string|null */
    private ?string $label = null;

    /** @var Closure|null */
    private ?Closure $condition = null;

    /** @var string|null */
    private ?string $route = null;

    /** @var array */
    private array $attributes = [];

    /** @var array<mixed, ValueAdapterInterface> */
    private array $routeParams = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $label
     * @return ActionBuilder
     */
    public function setLabel(string $label): ActionBuilder
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @param $row
     * @return bool
     */
    public function isAllowedFor($row): bool
    {
        return null === $this->condition ? true : ($this->condition)($row);
    }

    /**
     * @param Closure $condition
     * @return $this
     */
    public function setCondition(Closure $condition): self
    {
        $this->condition = $condition;
        return $this;
    }

    /**
     * @param string $route
     * @param array<mixed, ValueAdapterInterface> $routeParams
     * @return $this
     */
    public function setRoute(string $route, array $routeParams = []): self
    {
        $this->route = $route;
        $this->routeParams = $routeParams;
        return $this;
    }

    /**
     * @param string $attribute
     * @param mixed $value
     * @return $this
     */
    public function setAttribute(string $attribute, $value): self
    {
        $this->attributes[$attribute] = $value;
        return $this;
    }

    /**
     * @param mixed $row
     * @return Action
     */
    public function buildAction($row): Action
    {
        $action = new Action($this->label ?? $this->name, $this->attributes);

        if (null !== $this->route) {
            $action->setRoute($this->route, array_map(
                fn(ValueAdapterInterface $valueAdapter) => $valueAdapter->getValue($row),
                $this->routeParams
            ));
        }

        return $action;
    }

    /**
     * @param string $name
     * @return self
     */
    public static function withName(string $name): self
    {
        return new self($name);
    }
}
