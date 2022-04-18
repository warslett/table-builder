<?php

declare(strict_types=1);

namespace WArslett\TableBuilder;

use Closure;
use Jawira\CaseConverter\CaseConverterException;
use Jawira\CaseConverter\Convert;
use WArslett\TableBuilder\Exception\ValueAdapterException;
use WArslett\TableBuilder\ValueAdapter\CallbackAdapter;
use WArslett\TableBuilder\ValueAdapter\PropertyAccessAdapter;
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

    /**
     * Private construct (use static named constructors for concretions)
     * @param string $name
     */
    private function __construct(string $name)
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
    public function label(string $label): ActionBuilder
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @param mixed $row
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
    public function condition(Closure $condition): self
    {
        $this->condition = $condition;
        return $this;
    }

    /**
     * @psalm-suppress RedundantConditionGivenDocblockType - docblock type is only enforced in function
     * @param string $route
     * @param array<int|string, ValueAdapterInterface|string|Closure> $routeParams - A map of route parameters to the
     *     corresponding property of the row. Either a property path as a string, a Closure or an implementation of
     *     ValueAdapter
     * @return $this
     * @throws ValueAdapterException
     */
    public function route(string $route, array $routeParams = []): self
    {
        $this->route = $route;

        $this->routeParams = array_map(function ($parameter): ValueAdapterInterface {
            if ($parameter instanceof ValueAdapterInterface) {
                return $parameter;
            }

            if (is_string($parameter)) {
                return PropertyAccessAdapter::withPropertyPath($parameter);
            }

            if ($parameter instanceof Closure) {
                return CallbackAdapter::withCallback($parameter);
            }

            throw new ValueAdapterException("Each parameter in a route must be either a property path, a "
                . "callback or an implementation of ValueAdapterInterface");
        }, $routeParams);
        return $this;
    }

    /**
     * @param string $attribute
     * @param mixed $value
     * @return $this
     */
    public function attribute(string $attribute, $value): self
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
        $builder = new self($name);

        try {
            $converter = new Convert($name);
            $builder->label = $converter->toTitle();
        } catch (CaseConverterException $e) {
            $builder->label = $name;
        }

        return $builder;
    }
}
