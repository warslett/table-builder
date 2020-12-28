# Rendering Tables in Single Page Applications
Tables also implement JsonSerializable so they can be encoded as json in a response and rendered by a single page
application.

``` php
// GET /users/table
return new JsonResponse($table);
```

[Next: Route Generators](../route_generators.md)