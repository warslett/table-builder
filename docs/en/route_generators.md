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

$actionBuilder = ActionBuilder::withName('delete')
    ->route('/user/%d/delete', ['id']);
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

$actionBuilder = ActionBuilder::withName('delete')
    ->route('user_delete', ['id' => 'id']);
```

[Next: Value Adapters](./value_adapters.md)
