# Columns
Column objects are used to configure the column structure of a table and also to build Table Headings and Table Cells.
Column classes implement `WArslett\TableBuilder\Column\ColumnInterface` and can be added to a Table Builder instance
like this:
```php
$tableBuilder->addColumn($column);
```
All Table Builder column types extend `WArslett\TableBuilder\Column\AbstractColumn` which includes configuration methods
`setLabel`, `setSortToggle` and `afterBuildCell`.

Some columns use the trait `WArslett\TableBuilder\ValueAdapter\ValueAdapterTrait` which includes the configuration
method `setValueAdapter`. Value Adapters provide an abstract way of getting the value of a cell from a row for a column.

## <a name="TextColumn"></a>TextColumn
Text Column is used for just rendering a value as text. Cell values in Text Columns must be castable as strings.
```php
use WArslett\TableBuilder\Column\TextColumn;
use WArslett\TableBuilder\ValueAdapter\PropertyAccessAdapter;

$column = TextColumn::withName('my_text_column')
    ->setLabel('My Column Label Heading')
    ->setSortToggle('my_sort_toggle')
    ->setValueAdapter(PropertyAccessAdapter::withPropertyPath('foo'));
```
The above example would take the value of the property `foo` for each row and cast it to a string as the value for each
cell in the column. If a cell value resolved by the ValueAdapter cannot be cast as a string a ValueException will be
thrown.

## <a name="DateTimeColumn"></a>DateTimeColumn
DateTime Column is used for rendering an instance of a DateTime object with a given format.
```php
use WArslett\TableBuilder\Column\DateTimeColumn;
use WArslett\TableBuilder\ValueAdapter\PropertyAccessAdapter;

$column = DateTimeColumn::withName('my_datetime_column')
    ->setLabel('My Column Label Heading')
    ->setDateTimeFormat('Y-m-d H:i:s')
    ->setSortToggle('my_sort_toggle')
    ->setValueAdapter(PropertyAccessAdapter::withPropertyPath('foo'));
```
DateTimeColumn includes the configuration method setDateTimeFormat which takes a
[php date format string](https://www.php.net/manual/en/datetime.format.php). If no format is provided it will default to
the format 'Y-m-d H:i:s'. If the cell value is not a DateTime then a ValueException will be thrown.

## <a name="Boolean"></a>BooleanColumn
Boolean Column is used for rendering boolean values.
```php
use WArslett\TableBuilder\Column\BooleanColumn;
use WArslett\TableBuilder\ValueAdapter\PropertyAccessAdapter;

$column = BooleanColumn::withName('my_text_column')
    ->setLabel('My Column Label Heading')
    ->setSortToggle('my_sort_toggle')
    ->setValueAdapter(PropertyAccessAdapter::withPropertyPath('foo'));
```
Renderers will normally default to casting table cell values as strings in which case cell values for this column would
be rendered as a 1 or a 0. TwigRenderer and PhtmlRenderer however will render the value using html entities `&#10004;`
(&#10004;) for true and `&#10008;` (&#10008;) for false. If the cell value is not a boolean then a ValueException will
be thrown.

## <a name="ActionGroupColumn"></a>ActionGroupColumn
Action Group Column is used for rendering a group of actions that can be rendered as links or buttons.
```php
use WArslett\TableBuilder\ActionBuilder;
use WArslett\TableBuilder\Column\ActionGroupColumn;
use WArslett\TableBuilder\ValueAdapter\PropertyAccessAdapter;

$actionBuilder = ActionBuilder::withName('update')
    ->setLabel('Update')
    ->setRoute('user_update', [
        'id' => PropertyAccessAdapter::withPropertyPath('id')
    ]);

$column = ActionGroupColumn::withName('actions')
    ->setLabel('Actions')
    ->addActionBuilder($actionBuilder);
```

You can also set attributes such as extra classes on action builders:
```php
use WArslett\TableBuilder\ActionBuilder;
use WArslett\TableBuilder\ValueAdapter\PropertyAccessAdapter;

$actionBuilder = ActionBuilder::withName('delete')
    ->setLabel('Delete')
    ->setAttribute('extra_classes', ['btn-danger'])
    ->setRoute('user_delete', [
        'id' => PropertyAccessAdapter::withPropertyPath('id')
    ]);
```

You can set a condition so that the action can be excluded from the group if a condition is not met:
```php
use WArslett\TableBuilder\ActionBuilder;
use WArslett\TableBuilder\ValueAdapter\PropertyAccessAdapter;

$actionBuilder = ActionBuilder::withName('delete')
    ->setLabel('Delete')
    ->setCondition(fn(User $user) => $this->authorizationChecker->isGranted('delete' $user))
    ->setRoute('user_delete', [
        'id' => PropertyAccessAdapter::withPropertyPath('id')
    ]);
```

ActionGroupColumn includes a configuration method `addActionBuilder` which can be called multiple times to add multiple
ActionBuilder instances to the group. The ActionBuilder class configures how the action will be built for each row. The
configuration method `setRoute` on the ActionBuilder is used to set the route and route parameters for the action.

The route parameters are a key value pair array where the value is an instance of ValueAdapterInterface which is used to
resolve the parameters for each row.

`WArslett\TableBuilder\RouteGeneratorAdapter\RouteGeneratorAdapterInterface` provides an abstract way of generating
urls from routes and route params which can be used by Renderers. If you are using symfony routing then
`WArslett\TableBuilder\RouteGeneratorAdapter\SymfonyRoutingAdapter` will generate routes using the symfony router.

If you are not using symfony routing then the `WArslett\TableBuilder\RouteGeneratorAdapter\SprintfAdapter` is also
available which will allow you to configure routes like this:
```php
$actionBuilder->setRoute('/users/%d/update', [
    PropertyAccessAdapter::withPropertyPath('id')
]);
```
Route Generator Adapters are normally injected into the constructor of the renderer. If you are using the symfony bundle
with the TwigRenderer it will do this for you automatically.

## <a name="ConditionalFormatting"></a>Conditional Formatting
Columns that extend AbstractColumn include a configuration method `afterBuildCell` which takes a callback that can be
used to alter the value or attributes of a cell after it is created. This is useful for applying conditional formatting.
```php
$column->afterBuildCell(function (TableCell $cell) {
    if ($cell->getValue() > 5) {
        $cell->setAttribute('extra_classes', ['text-red']);
    }
});
```
Note that a cell's attributes do not necessarily relate directly to html attributes. The attributes you set on a cell
will need to be attributes that your renderer knows what to do with. TwigRenderer and PhtmlRenderer will set the
attribute 'extra_classes' in the class attribute of the td element for the cell in addition to any other classes used
by the theme.

The closure takes a TableCell as the first parameter and can optionally take the whole data row as a second parameter
for more complex conditional behaviour.
```php
$column->afterBuildCell(function (TableCell $cell, User $user) {
    if (false === $user->isActive()) {
        $cell->setValue($cell->getValue() . ' (inactive)');
    }
});
```
## <a name="ImplementingColumns"></a>Implementing your own Columns
Any class that implements ColumnInterface can be used as a column to build Table Cells and Table Headings. The easiest
way to create a custom Column type however is to extend AbstractColumn.
Let's say you wanted to create a table column to display an image. The url for the image is a property on each row of
data. We will start by creating a new class for our Image Column:
```php
<?php

declare(strict_types=1);

namespace App\TableBuilder\Column;

use WArslett\TableBuilder\Column\AbstractColumn;
use WArslett\TableBuilder\Exception\NoValueAdapterException;
use WArslett\TableBuilder\ValueAdapter\ValueAdapterTrait;

/**
 * @extends AbstractColumn<string>
 */
final class ImageColumn extends AbstractColumn
{
    // Using the value adapter trait allows client code to set a value adapter when configuring the column
    use ValueAdapterTrait;

    /**
     * @param mixed $row
     * @return string
     * @throws NoValueAdapterException
     */
    protected function getCellValue($row)
    {
        // The assertHasValueAdapter method will throw a NoValueDapterException if no ValueAdapter has been set
        $this->assertHasValueAdapter();

        // We are just going to return the value resolved by the value adapter but we could do extra validation here
        return $this->valueAdapter->getValue($row);
    }
}
```
Now we can add our new column to a table builder:
```php
$tableBuilder->addColumn(ImageColumn::withName('my_text_column')
    ->setLabel('Profile Image')
    ->setValueAdapter(PropertyAccessAdapter::withPropertyPath('profileImageUrl')));
```
If we were to render this table now with the Twig Renderer it would default to casting the value as a string and so it
would just display the url as text in the table cell. In order to display the cell value as an actual image we would
need to provide a cell value template for our new column. With this example in twig we might create a template that
looks like this:
```twig
{# templates/table-builder/image-cell-value.html.twig #}
<img src="{{ cell.value }}" />
```
We can then register the template with twig renderer in PHP like so:
```php
use App\TableBuilder\Column\ImageColumn;

$twigRenderer->registerCellValueTemplate(ImageColumn::class, 'table-builder/image-cell-value.html.twig');
```
Or if you are using the table-builder-bundle with symfony you can register it in config like this:
```yaml
# config/packages/table_builder.yaml
table_builder:

  twig_renderer:
    cell_value_templates:
      App\TableBuilder\Column\ImageColumn: 'table-builder/image-cell-value.html.twig'
```
Every renderer will have it's own way of registering how different cell values should be rendered. You should take
a look at the documentation for your renderer to find out more

### Implementing Columns with more complex values
Let's say you want to implement a column where that value is made up of more information than can be modelled with just
scalar values. For example let's say our ImageColumn needs to also include a configurable alt tag for each image.

A TableCell value can be any type that can be cast as a string. The reason for this is that when there is no other way
of rendering a cell value a renderer will always fall back to just casting the value as a string. Therefore we can
create a value object with our own Image class.
```php
<?php

declare(strict_types=1);

namespace App\TableBuilder\CellValue;

class Image
{
    public string $url;
    public string $altText;
    
    public function __toString(): string
    {
        return sprintf("%s (See %s)", $this->altText, $this->url);
    }
}
```
Then our Column should look like this:
```php
<?php

declare(strict_types=1);

namespace App\TableBuilder\Column;

use App\TableBuilder\CellValue\Image;
use WArslett\TableBuilder\Column\AbstractColumn;
use WArslett\TableBuilder\Exception\ColumnException;
use WArslett\TableBuilder\Exception\NoValueAdapterException;
use WArslett\TableBuilder\ValueAdapter\ValueAdapterTrait;

/**
 * @extends AbstractColumn<Image>
 */
final class ImageColumn extends AbstractColumn
{
    // Using the value adapter trait allows client code to set a value adapter when configuring the column
    use ValueAdapterTrait;
    
    private ?string $altText = null;
    
    /**
     * Configuration Method for configuring the alt tag for the image
     *
     * @param string $altText
     * @return $this
     */
    public function setAltText(string $altText): self
    {
        $this->altText = $altText;
        return $this;
    }

    /**
     * @param mixed $row
     * @return string
     * @throws NoValueAdapterException
     * @throws ColumnException
     */
    protected function getCellValue($row)
    {
        // The assertHasValueAdapter method will throw a NoValueDapterException if no ValueAdapter has been set
        $this->assertHasValueAdapter();
        
        if (null === $this->altText) {
          throw new ColumnException("Missing alt text");
        }

        $image = new Image();
        $image->url = $this->valueAdapter->getValue($row);
        $image->altText = $this->altText;
        
        return $image;
    }
}
```
and our twig template becomes:
```twig
{# templates/table-builder/image-cell-value.html.twig #}
<img src="{{ cell.value.url }}" alt="{{ cell.value.altText }}" />
```

[Next: Data Adapters](./data_adapters.md)
