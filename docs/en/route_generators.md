# Route Generators
Route Generators generate urls from route names and parameters. Route Generators are used to render urls for Actions.

## <a name="SprintfAdapter"></a>SprintfAdapter
SprintfAdapter generates urls using the php sprintf function
```php
use WArslett\TableBuilder\RouteGeneratorAdapter\SprintfAdapter;

$routeGenerator = new SprintfAdapter();
echo $routeGenerator->renderRoute('/user/%d/delete', [123]); // outputs /user/123/delete
```

If you are using SprintfAdapter you would configure your routes like this
```php
use WArslett\TableBuilder\ActionBuilder;
use WArslett\TableBuilder\ValueAdapter\PropertyAccessAdapter;

$actionBuilder = ActionBuilder::withName('delete')
    ->setRoute('/user/%d/delete', [PropertyAccessAdapter::withPropertyPath('id')]);
```

## <a name="SymfonyRoutingAdapter"></a>SymfonyRoutingAdapter
SymfonyRoutingAdapter generates urls using symfony routes
```php
use WArslett\TableBuilder\RouteGeneratorAdapter\SymfonyRoutingAdapter;

$router = ...
$routeGenerator = new SymfonyRoutingAdapter($router);
echo $routeGenerator->renderRoute('user_delete', ['id' => 123]); // outputs /user/123/delete
```

If you are using SymfonyRoutingAdapter you would configure your routes like this
```php
use WArslett\TableBuilder\ActionBuilder;
use WArslett\TableBuilder\ValueAdapter\PropertyAccessAdapter;

$actionBuilder = ActionBuilder::withName('delete')
    ->setRoute('user_delete', ['id' => PropertyAccessAdapter::withPropertyPath('id')]);
```

[Next: Value Adapters](./value_adapters.md)
