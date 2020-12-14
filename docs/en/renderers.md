# Renderers
A Table Renderer renders a Table object as another format. Table Renderers that render html tables implement the
interface [HtmlTableRendererInterface](../../lib/Renderer/Html/HtmlTableRendererInterface.php).

## <a name="PhtmlRenderer"></a>PhtmlRenderer
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
            <?= $this->renderTableHeading($table, $heading) ?>
        <?php endforeach; ?>
    </tr>
    </thead>
    <tbody>
        <?php foreach ($table->getRows() as $row) : ?>
            <?= $this->renderTableRow($table, $row) ?>
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

## <a name="TwigRenderer"></a>TwigRenderer
Twig Renderer renders html tables using twig templates. It depends on `twig/twig`. If you are using the
table-builder-bundle with symfony it will build and register the service for you. Otherwise, you can create the service
like this:
```php
use Twig\Environment;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use WArslett\TableBuilder\Renderer\Html\TwigRenderer;
use WArslett\TableBuilder\Twig\StandardTemplatesLoader;
use WArslett\TableBuilder\Twig\TableRendererExtension;

$twigEnvironment = new Environment(new ChainLoader([
    new StandardTemplatesLoader(), //loads the standard table builder templates
    new FilesystemLoader('templates/') //your template loader
]));
$twigRenderer = new TwigRenderer($twigEnvironment, new SprintfAdapter());
$twigEnvironment->addExtension(new TableRendererExtension($twigRenderer)); // the extension must be loaded
```

The constructor takes two parameters. The first is the Twig Environment and the second is an implementation of
RouteGeneratorAdapterInterface which is used to generate urls for Actions.
You can inject an alternative such as the SymfonyRoutingAdapter to render Symfony routes:
```php
use WArslett\TableBuilder\Renderer\Html\TwigRenderer;
use WArslett\TableBuilder\RouteGeneratorAdapter\SymfonyRoutingAdapter;

$twigEnvironment = ...
$router = ...
$adapter = new SymfonyRoutingAdapter($router);
$twigRenderer = new TwigRenderer($twigEnvironment, $adapter);
```

The third argument is the path to the theme file. The standard theme is the default, but the bootstrap4 theme is also
available.
```php
use WArslett\TableBuilder\Renderer\Html\TwigRenderer;
use WArslett\TableBuilder\RouteGeneratorAdapter\SprintfAdapter;

$twigEnvironment = ...
$twigRenderer = new TwigRenderer($twigEnvironment, new SprintfAdapter(), TwigRenderer::BOOTSTRAP4_THEME_PATH);
```

You can render a whole table:
```twig
<div class="container">
    {{ table(table) }}
</div>
```

Or you can render parts of the table separately:
```twig
<table>
    <thead>
        <tr>
            {% for column_name, heading in table.headings %}
                {{ table_heading(table, heading) }}
            {% endfor %}
        </tr>
    </thead>
    <tbody>
        {% for row in table.rows %}
            {{ table_row(table, row) }}
        {% endfor %}
    </tbody>
</table>
```

You can register custom cell value templates for columns:
```php
use App\TableBuilder\Column\MyCustomColumn;

$renderer->registerCellValueTemplate(MyCustomColumn::class, 'custom/cell/value/template.html.twig');
```

Or if you are using your own custom theme your can register blocks for rendering custom cell values for columns:
```php
use App\TableBuilder\Column\MyCustomColumn;

$renderer->registerCellValueBlock(MyCustomColumn::class, 'my_cell_value_block');
```

You can create your own custom theme by creating a template file which implements the following blocks:
```
action_group_cell_value
boolean_cell_value
table
table_cell
table_element
table_heading
table_pagination
table_row
table_rows_per_page_options
```

Or if you want to extend an existing template you can do this:
```twig
{% extends 'table-builder/standard.html.twig' %}

{% block table_element %}
    <table class="my-custom-table-class">
        <thead>
            <tr>
                {% for heading in table.headings %}
                    {{ table_heading(table, heading) }}
                {% endfor %}
            </tr>
        </thead>
        <tbody>
            {% for row in table.rows %}
                {{ table_row(table, row) }}
            {% endfor %}
        </tbody>
    </table>
{% endblock %}
```

Then provide the path of the file as the second argument of the constructor for TwigRenderer (relative to your template
loader).

### <a name="SinglePageApplication"></a>Rendering Tables in Single Page Applications
Tables also implement JsonSerializable so they can be encoded as json in a response and rendered by a single page
application.

``` php
// GET /users/table
return new JsonResponse($table);
```
