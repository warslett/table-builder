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
using one of our data adapters or implement your own. Handle a request to apply sorting an pagination using one of our
request adapters or implement your own.
``` php
$table = $this->tableBuilderFactory->createTableBuilder()
    ->addColumn(TextColumn::withName('first_name')
        ->setLabel('First Name')
        ->setValueAdapter(PropertyAccessAdapter::withPropertyPath('firstName')))
    ->addColumn(TextColumn::withName('last_name')
        ->setLabel('Last Name')
        ->setValueAdapter(PropertyAccessAdapter::withPropertyPath('lastName')))
    ->addColumn(TextColumn::withName('major')
        ->setLabel('Major Name')
        ->setValueAdapter(PropertyAccessAdapter::withPropertyPath('major.name')))
    ->buildTable('students')
    ->setDataAdapter(DoctrineOrmAdapter::withQueryBuilder($this->entityManager->createQueryBuilder()
        ->select('s')
        ->from(Student::class, 's')
        ->where('s.major = :major')
        ->setParameter('major', $major)
    ))
    ->handleRequest(SymfonyHttpAdapter::withRequest($request))
;
```

### Table Abstraction
Table Builder models tables in an abstract way that isn't coupled to any particular data source or output. You can
interact with tables like this:
``` php
$tableName = $table->getName(); // "students"
$tablePageNumber = $table->getPageNumber(); // 1

$tableHeadings = $table->getHeadings();
$firstNameHeading = $tableHeadings['first_name'];
$firstNameLabel = $firstNameHeading->getLabel(); // "First Name"

$rows = $table->getRows();
$firstRow = $row[0];
$firstRowFirstNameCell = $firstRow['first_name'];
$firstRowFirstNameValue = $firstRowFirstNameCell->getValue(); // "John"
```

### Table Rendering
Modeling tables in an abstract way allows us to provide a variety of generic renderers for rendering them. Renderers
that render html implement the interface HtmlTableRendererInterface. The twig renderer is provided out of the box and is
themeable with a bootstrap4 theme provided out the box.
``` php
use Twig\Environment;
use WArslett\TableBuilder\Renderer\Html\TwigRenderer;
use WArslett\TableBuilder\Twig\StandardTemplatesLoader;
use WArslett\TableBuilder\Twig\TableRendererExtension;

$twigEnvironment = new Environment(new StandardTemplatesLoader());
$renderer = new TwigRenderer($twigEnvironment, 'table-builder/bootstrap4.html.twig');
$twigEnvironment->addExtension(new TableRendererExtension($renderer));

echo $renderer->renderTable($table);
```
Or with the TableRendererExtension added to your twig environment you can render directly within twig
``` twig
<div class="container">
    {# table twig function takes a table object #}
    {{ table(table) }}
</div>
```
Will output html:
``` html
<table class="table">
    <thead>
        <tr>
            <th scope="col">First Name</th>
            <th scope="col">Last Name</th>
            <th scope="col">Major Name</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>John</td>
            <td>Smith</td>
            <td>Fine Art</td>
        </tr>
        <tr>
            <td>Jane</td>
            <td>Smith</td>
            <td>Computer Science</td>
        </tr>
        ...
    </tbody>
</table>
<div class="btn-group" role="group">
    <a href="?students%5Bpage%5D=1" class="btn btn-primary active">1</a>
    <a href="?students%5Bpage%5D=2" class="btn btn-primary">2</a>
    <a href="?students%5Bpage%5D=3" class="btn btn-primary">3</a>
</div>
```
It is possible to render multiple tables on the same page which will sort and paginate independently.

## Dependencies
Table builder has no core dependencies however some optional features have dependencies.
* TwigRenderer and related classes depends on `twig/twig`
* DoctrineORMAdapter data adapter depends on `doctrine/orm`
* PropertyAccessAdapter value adapter depends on `symfony/property-access`
* SymfonyHttpAdapter response adapter depends on `symfony/http-foundation`
