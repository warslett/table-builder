# Columns
Column objects are used to configure the column structure of a table and also to build Table Headings and Table Cells.
Column classes implement WArslett\TableBuilder\Column\ColumnInterface and can be added to a Table Builder instance like
this:
```php
$tableBuilder->addColumn($column);
```
All Table Builder column types extend WArslett\TableBuilder\Column\AbstractColumn which includes configuration methods
`setLabel`, `setSortToggle` and `afterBuildCell`.

Some columns use the trait WArslett\TableBuilder\ValueAdapter\ValueAdapterTrait which includes the configuration method
`setValueAdapter`. Value Adapters provide an abstract way of getting the value of a cell from a row for a column.

## TextColumn
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

## DateTimeColumn
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

## BooleanColumn
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
