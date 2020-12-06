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

## <a name="AdapterPattern"></a>Adapter Pattern
Table Builder uses the adapter pattern in a few different ways. This makes it easy to extend and modify by implementing
your own adapters. You can create your own Columns, Data Adapters, Value Adapters, Request Adapters, Route Generators
and Renderers allowing to do whatever you want with Table Builder without needing to change the core classes.
