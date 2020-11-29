<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\ValueAdapter;

use WArslett\TableBuilder\Column\TextColumn;
use WArslett\TableBuilder\Exception\NoValueAdapterException;

trait ValueAdapterTrait
{
    private ?ValueAdapterInterface $valueAdapter = null;

    /**
     * @param ValueAdapterInterface $valueAdapter
     * @return $this
     */
    public function setValueAdapter(ValueAdapterInterface $valueAdapter): self
    {
        $this->valueAdapter = $valueAdapter;
        return $this;
    }

    /**
     * @return void
     * @throws NoValueAdapterException
     */
    private function assertHasValueAdapter(): void
    {
        if (null === $this->valueAdapter) {
            throw new NoValueAdapterException(
                sprintf("Cannot handle request until value adapter has been set for %s", $this->name)
            );
        }
    }
}
