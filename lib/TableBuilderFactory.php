<?php

declare(strict_types=1);

namespace WArslett\TableBuilder;

final class TableBuilderFactory implements TableBuilderFactoryInterface
{
    /**
     * @return TableBuilderInterface
     */
    public function createTableBuilder(): TableBuilderInterface
    {
        return new TableBuilder();
    }
}
