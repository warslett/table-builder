# PhtmlRenderer
Phtml Renderer renders html tables using phtml templates. It has 0 third party dependencies other than plain old PHP.
Create the service like this:
```php
use WArslett\TableBuilder\Renderer\Html\PhtmlRenderer;

$renderer = new PhtmlRenderer();
```

The constructor can take two optional parameters. The first is an implementation of RouteGeneratorAdapterInterface which
is used to generate urls for Actions. If no RouteGeneratorAdapter is provided it will default to the SprintfAdapter.
You can inject an alternative such as the SymfonyRoutingAdapter to render Symfony routes:
```php
use WArslett\TableBuilder\Renderer\Html\PhtmlRenderer;
use WArslett\TableBuilder\RouteGeneratorAdapter\SymfonyRoutingAdapter;

$router = ...;
$adapter = new SymfonyRoutingAdapter($router);
$renderer = new PhtmlRenderer($adapter);
```

The second argument is the path to the theme directory. The standard theme is the default, but the bootstrap4 theme is
also available.
```php
use WArslett\TableBuilder\Renderer\Html\PhtmlRenderer;
use WArslett\TableBuilder\RouteGeneratorAdapter\SprintfAdapter;

$renderer = new PhtmlRenderer(new SprintfAdapter(), PhtmlRenderer::BOOTSTRAP4_THEME_DIRECTORY);
```

You can render a whole table:
```php
<div class="container">
<?= $renderer->renderTable($table) ?>
</div>
```

Or you can render parts of the table separately:
```php
<table class="table">
 <thead>
 <tr>
     <?php foreach ($table->getHeadings() as $heading) : ?>
         <?= $renderer->renderTableHeading($table, $heading) ?>
     <?php endforeach; ?>
 </tr>
 </thead>
 <tbody>
     <?php foreach ($table->getRows() as $row) : ?>
         <?= $renderer->renderTableRow($table, $row) ?>
     <?php endforeach; ?>
 </tbody>
</table>
```

You can register custom cell value templates for columns:
```php
use App\TableBuilder\Column\MyCustomColumn;

$renderer->registerCellValueTemplate(MyCustomColumn::class, 'custom/cell/value/template.phtml');
```

You can create your own custom theme by implementing the following templates in a directory:
```
action_group_cell_value.phtml
boolean_cell_value.phtml
table.phtml
table_cell.phtml
table_element.phtml
table_heading.phtml
table_pagination.phtml
table_row.phtml
table_rows_per_page_options.phtml
```

Then provide the path of the directory as the second argument of the constructor for PhtmlRenderer.

[Next: TwigRenderer](./twig_renderer.md)
