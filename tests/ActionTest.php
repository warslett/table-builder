<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests;

use WArslett\TableBuilder\Action;

class ActionTest extends TestCase
{
    public function testGetAttributeValue(): void
    {
        $attribute = 'bar';
        $action = new Action('Label', ['key' => $attribute]);
        $default = 'foo';

        $value = $action->getAttribute('key', $default);

        $this->assertSame($attribute, $value);
    }

    public function testGetAttributeDefaultValue(): void
    {
        $action = new Action('Label');
        $default = 'foo';

        $value = $action->getAttribute('key', $default);

        $this->assertSame($default, $value);
    }
}
