# Request Adapter
Request Adapters adapt different types of request objects to a common interface which is used by table objects to get
querystring parameters to sort and paginate the data.

## <a name="ArrayRequestAdapter"></a>ArrayRequestAdapter
ArrayAdapter allows you to provide the request querystring parameters as an array. This is useful in scenarios where you
don't have a request object or when you only have the php super globals.
```php
use WArslett\TableBuilder\RequestAdapter\ArrayRequestAdapter;

$adapter = new ArrayRequestAdapter($_GET);

$table = ...
$table->handleRequest($adapter);
```

## <a name="Psr7Adapter"></a>Psr7Adapter
Psr7Adapter adapts Psr7 requests
```php
use Laminas\Diactoros\ServerRequestFactory;
use WArslett\TableBuilder\RequestAdapter\Psr7Adapter;

$request = ServerRequestFactory::fromGlobals();
$adapter = new Psr7Adapter($request);

$table = ...
$table->handleRequest($adapter);
```

## <a name="SymfonyHttpAdapter"></a>SymfonyHttpAdapter
SymfonyHttpAdapter adapts Symfony requests
```php
use Symfony\Component\HttpFoundation\Request;
use WArslett\TableBuilder\RequestAdapter\SymfonyHttpAdapter;

$request = Request::createFromGlobals();
$adapter = new SymfonyHttpAdapter($request);

$table = ...
$table->handleRequest($adapter);
```

[Next: Route Generators](./route_generators.md)
