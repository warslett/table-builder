# Table Builder [![Build Status](https://circleci.com/gh/warslett/table-builder.png?style=shield)](https://circleci.com/gh/warslett/table-builder)
**THIS PACKAGE IS CURRENTLY UNDER DEVELOPMENT**

Table builder provides table abstraction, table building and table rendering. Allowing you to configure your tables,
load your data into them and then render them in a variety of ways with common table functionality such as pagination
and sorting taken care of.

## Installation
`composer require warslett/table-builder`

## Requirements
PHP 7.4
This package has no core composer dependencies but some of the optional features have dependencies. See dependencies
below.

## Features

### Table Building
Configure your tables using a variety of column types or implement your own column types. Then load data into the table
using one of our data adapters or implement your own. Handle a request to apply sorting and pagination using one of our
request adapters or implement your own.
``` php
// Configure the table structure with a range of out the box column types
$tableBuilder = $this->tableBuilderFactory->createTableBuilder()
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
        ->setValueAdapter(PropertyAccessAdapter::withPropertyPath('lastLogin')))
    ->addColumn(ActionGroupColumn::withName('actions')
        ->setLabel('Actions')
        ->addActionBuilder(ActionBuilder::withName('update')
            ->setLabel('Update')
            ->setRoute('user_update', [
                'id' => PropertyAccessAdapter::withPropertyPath('id')
            ]))
        ->addActionBuilder(ActionBuilder::withName('delete')
            ->setLabel('Delete')
            ->setRoute('user_delete', [
                'id' => PropertyAccessAdapter::withPropertyPath('id')
            ])));

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
$table->handleRequest(SymfonyHttpAdapter::withRequest($request));
```

### Table Rendering
Modeling tables in an abstract way allows us to provide a variety of generic renderers for rendering them. Renderers
that render html implement the interface HtmlTableRendererInterface. The twig renderer is provided out of the box and is
themeable with a bootstrap4 theme provided out the box.
``` php
use Twig\Environment;
use WArslett\TableBuilder\Renderer\Html\TwigRenderer;
use WArslett\TableBuilder\RouteGeneratorAdapter\SymfonyRoutingAdapter;
use WArslett\TableBuilder\Twig\StandardTemplatesLoader;
use WArslett\TableBuilder\Twig\TableRendererExtension;

// Route generators are used for generating routes to actions in your table (Symfony and Sprintf adapters provided otb)
$routeGenerator = new SymfonyRoutingAdapter($router);

// StandardTemplatesLoader loads the templates for the standard themes in the table-builder/ namespace
$twigEnvironment = new Environment(new StandardTemplatesLoader());
$renderer = new TwigRenderer($twigEnvironment, $routeGenerator, 'table-builder/bootstrap4.html.twig');
$twigEnvironment->addExtension(new TableRendererExtension($renderer)); // Extension must be registered before rendering

echo $renderer->renderTable($table);
```
Or with the TableRendererExtension added to your twig environment you can render directly within twig
``` twig
<div class="container">
    {{ table(table) }}
</div>
```

### Single Page Applications
Tables also implement JsonSerializable so they can be encoded as json in a response and consumed by a single page
application.

``` php
// GET /users/table
return new JsonResponse($table);
```

## Dependencies
Table builder has no core dependencies however some optional features have dependencies.
* TwigRenderer and related classes depends on `twig/twig`
* DoctrineORMAdapter data adapter depends on `doctrine/orm`
* PropertyAccessAdapter value adapter depends on `symfony/property-access`
* SymfonyHttpAdapter response adapter depends on `symfony/http-foundation`
* SymfonyRoutingAdapter route generator adapter depends on `symfony/routing`
