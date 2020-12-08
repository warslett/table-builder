# Getting Started

## <a name="Installation"></a>Installation
`composer require warslett/table-builder`
or if you are using symfony `composer require warslett/table-builder-bundle warslett/table-builder`

## <a name="BasicUsage"></a>Basic Usage
Table Builder allows you to configure the way a table should be structured and how data should be loaded into it using
PHP and then build the table using parameters from a request to perform sorting and pagination.
``` php
use WArslett\TableBuilder\Column\DateTimeColumn;
use WArslett\TableBuilder\Column\TextColumn;
use WArslett\TableBuilder\DataAdapter\DoctrineOrmAdapter;
use WArslett\TableBuilder\TableBuilderFactory;
use WArslett\TableBuilder\ValueAdapter\PropertyAccessAdapter;

// The table builder factory is a service you can register in your service container
$tableBuilderFactory = new TableBuilderFactory();

// Configure the table structure with a range of out the box column types
$tableBuilder = $tableBuilderFactory->createTableBuilder()
    ->setRowsPerPageOptions([10, 20, 50])
    ->setDefaultRowsPerPage(10)
    ->addColumn(TextColumn::withName('email')
        ->setLabel('Email')
        ->setSortToggle('email')
        ->setValueAdapter(PropertyAccessAdapter::withPropertyPath('email')))
    ->addColumn(DateTimeColumn::withName('last_login')
        ->setLabel('Last Login')
        ->setDateTimeFormat('Y-m-d H:i:s')
        ->setSortToggle('last_login')
        ->setValueAdapter(PropertyAccessAdapter::withPropertyPath('lastLogin')));

// Build the table object
$table = $tableBuilder->buildTable('users');

// Configure how data will be loaded into the table
$entityManager = ...
$queryBuilder = $entityManager->createQueryBuilder()
    ->select('u')
    ->from(User::class, 'u');

$dataAdapter = DoctrineOrmAdapter::withQueryBuilder($queryBuilder)
    ->mapSortToggle('email', 'u.email')
    ->mapSortToggle('last_login', 'u.lastLogin');

$table->setDataAdapter($dataAdapter);

// Uses parameters on the request to load data into the table with sorting and pagination
$table->handleRequest(SymfonyHttpAdapter::withRequest($request));
```
Once the table has been built it can be rendered in a variety of ways. If you are using twig with the twig extension
you can render in a template like so:
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
If you are not using twig you can render the table as html just as well using the PhtmlRenderer.

## <a name="Sorting"></a>Sorting
Table results can be sorted using parameters on the query string of the request. TwigRenderer and PhtmlRenderer will
display links in the table headings to toggle different sorts on or off.

Columns that extend AbstractColumn include a configuration method `setSortToggle`. A sort toggle is a string that can
be used to map a Column to configuration on the data adapter for sorting your data. First assign your column a sort
toggle to enable sorting on that column.
```php
$column->setSortToggle('sort_name');
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
When the table is rendered by the TwigRenderer or PhtmlRenderer it will include a clickable toggle on the column heading
to toggle the sort ascending or descending. The link will set the sort_column parameter on the querystring of the
request which applies the corresponding sort toggle to the data adapter. For example the querystring
`?users[sort_column]=email&users[sort_dir]=desc` will sort the results descending using the sort toggle for the column
named "email".

## <a name="Pagination"></a>Pagination
Table results are paginated using parameters on the query string. TwigRenderer and PhtmlRenderer will display page links
below the table.

Tables have a "rows per page" limit and the table will only display results up to the limiy. When the data surpasses the
limit, the data adapter will load a page of results selected using the 'page' attribute in the query string. For example
if the table is named "users" then the querystring `?users[page]=2` will display the second page.

The rows per page limit can also be selected using the querystring parameter "rows_per_page". For example
`?users[page]=1&users[rows_per_page]=10` will display the first page of up to 10 results per page. There is a maximum
rows per page limit which is 100 by default. You can configure the maximum limit for the table builder like this:
```php
$tableBuilder->setMaxRowsPerPage(200);
```
The default rows per page if no query string parameter is provided is 20 which can also be overridden on the table
builder:
```php
$tableBuilder->setDefaultRowsPerPage(50);
```
TwigRenderer and PhtmlRenderer can display a set of rows per page options as links above the table. Rows per page
options can be enabled on the table builder:
```php
$tableBuilder->setRowsPerPageOptions([10, 20, 50]);
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

use WArslett\TableBuilder\Column\TextColumn;
use WArslett\TableBuilder\TableBuilderFactoryInterface;
use WArslett\TableBuilder\TableBuilderInterface;
use WArslett\TableBuilder\ValueAdapter\PropertyAccessAdapter;

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
            ->setRowsPerPageOptions([10, 20, 50])
            ->setDefaultRowsPerPage(10)
            ->addColumn(TextColumn::withName('email')
                ->setLabel('Email')
                ->setSortToggle('email')
                ->setValueAdapter(PropertyAccessAdapter::withPropertyPath('email')))
            ->addColumn(DateTimeColumn::withName('last_login')
                ->setLabel('Last Login')
                ->setDateTimeFormat('Y-m-d H:i:s')
                ->setSortToggle('last_login')
                ->setValueAdapter(PropertyAccessAdapter::withPropertyPath('lastLogin')));
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
use WArslett\TableBuilder\RequestAdapter\SymfonyHttpAdapter;

class UserTableController
{
    private UserTableFactory $userTableFactory;
    private Twig\Environment $twigEnvironment;

    public function __construct(
        UserTableFactory $userTableFactory,
        Twig\Environment $twigEnvironment;
    ) {
        $this->userTableFactory = $userTableFactory;
        $this->twigEnvironment = $twigEnvironment;
    }

    public function __invoke(Request $request): Response
    {
        $table = $this->userTableFactory->buildTable('users');
        $table->handleRequest(SymfonyHttpAdapter::withRequest($request));
        
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
