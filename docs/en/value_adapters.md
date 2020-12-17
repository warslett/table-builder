# Value Adapters
Value Adapters are used to configure how a value will be selected from the zone. They can be used for configuring values
on Columns:
```php
use WArslett\TableBuilder\Column\TextColumn;

$adapter = ...
$column = TextColumn::withName('user_name')
    ->setValueAdapter($valueAdapter);
```

They can also be used for configuring parameters on routes:
```php
use WArslett\TableBuilder\ActionBuilder;

$adapter = ...
$actionBuilder = ActionBuilder::withName('delete')
    ->setRoute('user_delete', [
        'id' => $adapter
    ]);
```

## <a name="CallbackAdapter"></a>Callback Adapter
Callback Adapter allows you to select the value using a Closure.
```php
use WArslett\TableBuilder\ValueAdapter\CallbackAdapter;

$adapter = CallbackAdapter::withCallback(fn($user) => $user->getName());
```

## <a name="PropertyAccessAdapter"></a>Property Access Adapter
Property Access Adapter is dependent on `symfony/property-access` and allows you to select the value using a Symfony
Property Access property path.
```php
use WArslett\TableBuilder\ValueAdapter\PropertyAccessAdapter;

$adapter = PropertyAccessAdapter::withPropertyPath('name');
```