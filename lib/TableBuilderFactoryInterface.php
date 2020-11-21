<?php

declare(strict_types=1);

namespace WArslett\TableBuilder;

interface TableBuilderFactoryInterface
{

    /**
     * @return TableBuilderInterface
     */
    public function createTableBuilder(): TableBuilderInterface;
}
