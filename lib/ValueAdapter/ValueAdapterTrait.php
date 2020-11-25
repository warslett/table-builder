<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\ValueAdapter;

use WArslett\TableBuilder\Column\TextColumn;

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
}
