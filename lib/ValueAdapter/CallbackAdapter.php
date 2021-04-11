<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\ValueAdapter;

use Closure;

final class CallbackAdapter implements ValueAdapterInterface
{
    private $callback;

    public function __construct(Closure $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param mixed $row
     * @return mixed
     */
    public function getValue($row)
    {
        return ($this->callback)($row);
    }

    /**
     * @param Closure $callable
     * @return self
     */
    public static function withCallback(Closure $callable): self
    {
        return new self($callable);
    }
}
