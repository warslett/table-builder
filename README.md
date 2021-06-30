# Table Builder
[![Latest Stable Version](https://poser.pugx.org/warslett/table-builder/v)](//packagist.org/packages/warslett/table-builder)
[![Build Status](https://circleci.com/gh/warslett/table-builder.png?style=shield)](https://circleci.com/gh/warslett/table-builder)
[![codecov](https://codecov.io/gh/warslett/table-builder/branch/master/graph/badge.svg?token=TLPUHTMP2E)](https://codecov.io/gh/warslett/table-builder)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fwarslett%2Ftable-builder%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/warslett/table-builder/master)
![Psalm coverage](https://shepherd.dev/github/warslett/table-builder/coverage.svg)
[![Total Downloads](https://poser.pugx.org/warslett/table-builder/downloads)](//packagist.org/packages/warslett/table-builder)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)

Table builder provides table abstraction, table building and table rendering. Allowing you to configure your tables,
load your data into them and then render them in a variety of ways. The package can help you implement functionality
common to most table actions in CRUD applications including pagination, sorting, row actions, conditional formatting,
and exporting the table to csv.

## Installation
`composer require warslett/table-builder`

If you are using symfony there is an optional bundle that will configure the services:

`composer require warslett/table-builder-bundle warslett/table-builder`

## Requirements
PHP 7.4 or 8.0.

## Documentation
Full documentation available [here](https://github.com/warslett/table-builder/blob/master/docs/en/index.md).

## Overview

### Table Building
Configure your tables using a variety of column types or implement your own column types. Then load data into the table
using one of our data adapters or implement your own. Handle a request to apply sorting and pagination using one of our
request adapters or implement your own.
``` php
// Configure the table structure with a range of out the box column types
$tableBuilder = $this->tableBuilderFactory->createTableBuilder()
    ->rowsPerPageOptions([10, 20, 50])
    ->defaultRowsPerPage(10)
    ->add(TextColumn::withProperty('email')
        ->sortable())
    ->add(DateTimeColumn::withProperty('last_login')
        ->format('Y-m-d H:i:s')
        ->sortable())
    ->add(ActionGroupColumn::withName('actions')
        ->add(ActionBuilder::withName('update')
            ->route('user_update', ['id' => 'id'])) // map 'id' parameter to property path 'id'
        ->add(ActionBuilder::withName('delete')
            ->route('user_delete', ['id' => 'id'])
            ->attribute('extra_classes', ['btn-danger'])));

// Build the table object
$table = $tableBuilder->buildTable('users');

// Configure how data will be loaded into the table
$queryBuilder = $this->entityManager->createQueryBuilder()
    ->select('u')
    ->from(User::class, 'u');

$dataAdapter = DoctrineOrmAdapter::withQueryBuilder($queryBuilder)
    ->mapSortToggle('email', 'u.email')
    ->mapSortToggle('last_login', 'u.lastLogin');

$table->setDataAdapter($dataAdapter);

// Uses parameters on the request to load data into the table with sorting and pagination
$table->handleSymfonyRequest($request);

// OR with a Psr7 Request
$table->handlePsr7Request($request);
```

### Table Rendering
Modeling tables in an abstract way allows us to provide a variety of generic renderers for rendering them. 

For example, with the TwigRendererExtension registered you can render the table in a twig template like this:
``` twig
<div class="container">
    {{ table(table) }}
</div>
```

Or if you aren't using twig you can use the PhtmlRenderer which uses plain old php templates and has 0 third party
dependencies:
``` php
use WArslett\TableBuilder\Renderer\Html\PhtmlRenderer;

$renderer = new PhtmlRenderer();
echo $renderer->renderTable($table);
```

Both of the above renderers are themeable and are available with a standard theme and bootstrap4 theme out the box.

![rendered table](https://github.com/warslett/table-builder/raw/master/docs/img/example.png "Rendered Html Table")

You can also render tables as CSV documents:
```php
use League\Csv\Writer;
use WArslett\TableBuilder\Renderer\Csv\CsvRenderer;

$csvRenderer = new CsvRenderer();
$csvRenderer->renderTable($table, Writer::createFromPath('/tmp/mycsv.csv'));
```

### Single Page Applications
Tables also implement JsonSerializable so they can be encoded as json in a response and consumed by a single page
application.

``` php
// GET /users/table
return new JsonResponse($table);
```

## <a name="Dependencies"></a>Dependencies
Table builder has minimal core dependencies however some optional features have additional dependencies.
* CsvRenderer and related classes depends on `league/csv`
* TwigRenderer and related classes depends on `twig/twig`
* DoctrineORMAdapter data adapter depends on `doctrine/orm`
* SymfonyHttpAdapter response adapter depends on `symfony/http-foundation`
* Psr7Adapter response adapter depends on `psr/http-message`
* SymfonyRoutingAdapter route generator adapter depends on `symfony/routing`
