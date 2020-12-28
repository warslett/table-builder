# Getting Started

## <a name="Installation"></a>Installation
`composer require warslett/table-builder`

If you are using symfony there is an optional bundle that will configure the services:

`composer require warslett/table-builder-bundle warslett/table-builder`

## <a name="BasicUsage"></a>Basic Usage
Table Builder allows you to configure the structure of a table and how data should be loaded into it and then build the
table using parameters from a request to perform sorting and pagination.

Create a new Table Builder using a Table Builder Factory
``` php
use WArslett\TableBuilder\TableBuilderFactory;

// The table builder factory is a stateless service you can register in your service container
$tableBuilderFactory = new TableBuilderFactory();
$tableBuilder = $tableBuilderFactory->createTableBuilder()
```

We can configure pagination on our table builder like this:
``` php
$tableBuilder
    ->rowsPerPageOptions([10, 20, 50])
    ->defaultRowsPerPage(10);
```

And we can add [columns](./columns.md) to our table builder like this:
```php
use WArslett\TableBuilder\Column\BooleanColumn;
use WArslett\TableBuilder\Column\DateTimeColumn;
use WArslett\TableBuilder\Column\TextColumn;

$tableBuilder->add(TextColumn::withName('email')
    ->label('Email')
    ->property('email')
    ->sortable());
        
$tableBuilder->add(BooleanColumn::withName('is_active')
    ->label('Active')
    ->property('active')
    ->sortable());
        
$tableBuilder->add(DateTimeColumn::withName('last_login')
    ->label('Last Login')
    ->property('lastLogin')
    ->format('Y-m-d H:i:s')
    ->sortable());
```

We can also add an [Action Group Column](./columns.md#ActionGroupColumn) which will build a group of actions for each
row in the table (which can then be rendered as links or buttons):
```php
use WArslett\TableBuilder\Column\ActionGroupColumn;

$tableBuilder->add(ActionGroupColumn::withName('email')
    ->label('Actions')
    ->add(ActionBuilder::withName('update')
        ->label('Update')
        ->route('user_update', [
            'id' => 'id'
        ]))
    ->add(ActionBuilder::withName('delete')
        ->label('Delete')
        ->route('user_delete', [
            'id' => 'id'
        ])));
```

When we've finished configuring the structure of our table we can build the table object
```php
$table = $tableBuilder->buildTable('users');
```

Then we can configure how we want data to be loaded into the table with a [data adapter](./data_adapters.md):
```php
use WArslett\TableBuilder\DataAdapter\DoctrineOrmAdapter;

$entityManager = ...
$queryBuilder = $entityManager->createQueryBuilder()
    ->select('u')
    ->from(User::class, 'u');

$dataAdapter = DoctrineOrmAdapter::withQueryBuilder($queryBuilder)
    ->mapSortToggle('email', 'u.email')
    ->mapSortToggle('is_active', 'u.active')
    ->mapSortToggle('last_login', 'u.lastLogin');

$table->setDataAdapter($dataAdapter);
```

Finally, our table can use parameters from a request to load a page of data:
```php
$table->handleSymfonyRequest($request);
```

Or alternatively using a Psr7 Request:
```php
$table->handlePsr7Request($request);
```

Or just using an array of parameters
```php
$table->handleParameters($_GET);
```

Once the table has been built and data loaded it can be rendered in a variety of ways. If you are using twig with the
twig extension you can render in a template like so:
```php
$twigEnvironment = ...
echo $twigEnvironment->render('table.html.twig', [
  'table' => $table;
]);
```

The twig would look something like this:
``` twig
{# table.html.twig #}
<div class="container">
    {{ table(table) }}
</div>
```

[Read More](./renderers.md#TwigRenderer)

![rendered table](https://github.com/warslett/table-builder/raw/master/docs/img/example.png "Rendered Html Table")

If you are not using twig you can render the table as html just as well using the
[PhtmlRenderer](./renderers.md#PhtmlRenderer).

## <a name="Sorting"></a>Sorting
Table results can be sorted using parameters on the query string of the request. Html Renderers will display links in
the table headings to toggle different sorts on or off.

Columns that extend AbstractColumn include a configuration method `sortToggle`. A sort toggle is a string that can
be used to map a Column to configuration on the data adapter for sorting your data. First assign your column a sort
toggle to enable sorting on that column.
```php
$column->sortToggle('sort_name');
```

Alternatively you can call the configuration method `sortable` which will set the sortToggle for the column to the
columns name:
```php
$column->sortable();
```

Then map the sort toggle on your data adapter. Different data adapters map sort toggles in different ways. For example
DoctrineOrmAdapter maps sort toggles to a field in the query which will then be used in the "order by" clause:
```php
$dataAdapter->mapSortToggle('sort_name', 'u.name');
```
The ArrayDataAdapter maps Sort Toggles to closures which are then used in a usort.
```php
$dataAdapter->mapSortToggle('last_login', fn(User $a, User $b) => $a->getLastLogin() < $b->getLastLogin() ? -1 : 1));
```
When the table is rendered by an Html Renderer it will include a clickable toggle on the column heading to toggle the
sort ascending or descending. The link will set the sort_column parameter on the querystring of the request which
applies the corresponding sort toggle to the data adapter. For example the querystring
`?users[sort_column]=email&users[sort_dir]=desc` will sort the results descending using the sort toggle for the column
named "email".

## <a name="Pagination"></a>Pagination
Table results are paginated using parameters on the query string. Html Renderers will display page links below the
table.

Tables have a "rows per page" limit and the table will only display results up to the limit. When the data surpasses the
limit, the data adapter will load a page of results selected using the 'page' attribute in the query string. For example
if the table is named "users" then the querystring `?users[page]=2` will display the second page.

The rows per page limit can also be selected using the querystring parameter "rows_per_page". For example
`?users[page]=1&users[rows_per_page]=10` will display the first page of up to 10 results per page. There is a maximum
rows per page limit which is 100 by default. You can configure the maximum limit for the table builder like this:
```php
$tableBuilder->maxRowsPerPage(200);
```

The default rows per page if no query string parameter is provided is 20 which can also be overridden on the table
builder:
```php
$tableBuilder->defaultRowsPerPage(50);
```

Html Renderers can display a set of rows per page options as links above the table. Rows per page options can be enabled
on the table builder:
```php
$tableBuilder->rowsPerPageOptions([10, 20, 50]);
```

You can allow unlimited rows per page:
```php
$tableBuilder
    ->maxRowsPerPage(INF)
    ->defaultRowsPerPage(INF);
```

## <a name="ReusableTables"></a>Building Reusable Tables
In most cases you will not want to build tables directly in your Controllers. This will violate the Single
Responsibility Principle and also makes it difficult to reuse the same table configuration in multiple places. The
library does not mandate any particular way of organizing your code. The recommended approach is with factory services
that you can then inject into your Controllers.

We can create a Table Builder Factory which will contain the configuration for our table's structure that can be reused
in different parts of the application:
```php
<?php

declare(strict_types=1);

namespace App\TableBuilder\TableBuilderFactory;

use WArslett\TableBuilder\Column\DateTimeColumn;
use WArslett\TableBuilder\Column\TextColumn;
use WArslett\TableBuilder\TableBuilderFactoryInterface;
use WArslett\TableBuilder\TableBuilderInterface;

final class UserTableBuilderFactory implements TableBuilderFactoryInterface
{
    private TableBuilderFactoryInterface $tableBuilderFactory;

    public function __construct(TableBuilderFactoryInterface $tableBuilderFactory)
    {
        $this->tableBuilderFactory = $tableBuilderFactory;
    }

    public function createTableBuilder(): TableBuilderInterface
    {
        return $this->tableBuilderFactory->createTableBuilder()
            ->rowsPerPageOptions([10, 20, 50])
            ->defaultRowsPerPage(10)
            ->add(TextColumn::withName('email')
                ->label('Email')
                ->property('email')
                ->sortable())
            ->add(DateTimeColumn::withName('last_login')
                ->label('Last Login')
                ->property('lastLogin')
                ->format('Y-m-d H:i:s')
                ->sortable());
    }
}
```
In the above example we inject an instance of TableBuilderFactoryInterface which is also implemented by our class. This
allows our Table Builder Factories to decorate each other so that our table structure can, if required be built up using
a chain of decorators.

Table Builder Factories allow us to reuse table structure throughout our application. We could now inject our Table
Builder Factory into our Controller, and we could configure our data in our there. Alternatively we might want to factor
this logic into a factory as well. In this case we would use a TableFactory like this:
```php
<?php

declare(strict_types=1);

namespace App\TableBuilder\TableFactory;

use Doctrine\ORM\EntityManagerInterface;
use WArslett\TableBuilder\DataAdapter\DoctrineOrmAdapter;
use WArslett\TableBuilder\Table;
use WArslett\TableBuilder\TableBuilderFactoryInterface;

class UserTableFactory
{
    private TableBuilderFactoryInterface $tableBuilderFactory;
    private EntityManagerInterface $entityManager;

    public function __construct(
        TableBuilderFactoryInterface $tableBuilderFactory,
        EntityManagerInterface $entityManager
    ) {
        $this->tableBuilderFactory = $tableBuilderFactory;
        $this->entityManager = $entityManager;
    }

    public function buildTable(string $name): Table
    {
        $tableBuilder = $this->tableBuilderFactory->createTableBuilder();
    
        // Build the table object
        $table = $tableBuilder->buildTable($name);
        
        // Configure how data will be loaded into the table
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u');
        
        $dataAdapter = DoctrineOrmAdapter::withQueryBuilder($queryBuilder)
            ->mapSortToggle('email', 'u.email')
            ->mapSortToggle('last_login', 'u.lastLogin');
        
        $table->setDataAdapter($dataAdapter);
        
        return $table;
    }
}
```
Now our controller is very simple and is left with minimal responsibilities:
```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\TableBuilder\TableFactory\UserTableFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig;

class UserTableController
{
    private UserTableFactory $userTableFactory;
    private Twig\Environment $twigEnvironment;

    public function __construct(
        UserTableFactory $userTableFactory,
        Twig\Environment $twigEnvironment
    ) {
        $this->userTableFactory = $userTableFactory;
        $this->twigEnvironment = $twigEnvironment;
    }

    public function __invoke(Request $request): Response
    {
        $table = $this->userTableFactory->buildTable('users');
        $table->handleSymfonyRequest($request);
        
        return new Response($this->twigEnvironment->render('table_page.html.twig', [
          'table' => $table
        ]));
    }
}
```
This approach keeps our classes small. Each class has minimal dependencies. Our responsibilities are nicely separated.
You could choose to build your table structure in your Table Factory or do something else if you prefer.

## <a name="AdapterPattern"></a>Adapter Pattern
Table Builder uses the adapter pattern in a few different ways. This makes it easy to extend and modify by implementing
your own adapters. You can create your own Columns, Data Adapters, Value Adapters, Request Adapters, Route Generators
and Renderers allowing you to do whatever you want with Table Builder without needing to change the core classes.

[Next: Columns](./columns.md)
