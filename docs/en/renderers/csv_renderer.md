# CSV Renderer
CSV Renderer renders tables as CSV. It depends on `league/csv` by
[The League of Extraordinary Packages](https://csv.thephpleague.com/).

The CsvRenderer can be constructed like this:
```php
use WArslett\TableBuilder\Renderer\Csv\CsvRenderer;

$csvRenderer = new CsvRenderer();
```

You can render a csv to a file like this:
```php
use League\Csv\Writer;
use WArslett\TableBuilder\Renderer\Csv\CsvRenderer;

$csvRenderer = new CsvRenderer();
$table = ...
$csvRenderer->renderTable($table, Writer::createFromPath('/tmp/mycsv.csv'));
```

The renderTableMethod can take any instance of League\Csv\Writer as it's second argument and can be configured in all
the usual ways.
```php
use League\Csv\Writer;
use WArslett\TableBuilder\Renderer\Csv\CsvRenderer;

$csvRenderer = new CsvRenderer();
$table = ...
$writer = Writer::createFromPath('/tmp/mycsv.csv')
    ->setDelimiter("\t")
    ->setNewline("\r\n");
$csvRenderer->renderTable($table, $writer);
```

The headings will be included as the top row of the csv by default. You can exclude the headings row like this:
```php
<?php
use League\Csv\Writer;
use WArslett\TableBuilder\Renderer\Csv\CsvRenderer;

$csvRenderer = new CsvRenderer();
$table = ...
$csvRenderer
    ->includeHeader(false)
    ->renderTable($table, Writer::createFromPath('/tmp/mycsv.csv'));
```

Cell values will be rendered by default by first casting to a string value. You can define how you want particular cell
values to be rendered for specific columns by creating Cell Value Transformers like this:
```php
<?php

declare(strict_types=1);

namespace App\TableBuilder\Renderer\Csv\Transformer;

use WArslett\TableBuilder\Renderer\Csv\CsvCellValueTransformerInterface;

final class MyCustomColumnTransformer implements CsvCellValueTransformerInterface
{

    /**
     * @param mixed $value
     * @return string
     */
    public function transformForCsv($value)
    {
        $transformedValue = ... // Transform the value
        return $transformedValue;
    }
}
```

You can then register your transformer like this:
```php
use League\Csv\Writer;
use WArslett\TableBuilder\Renderer\Csv\CsvRenderer;
use App\TableBuilder\Renderer\Csv\Transformer\MyCustomColumnTransformer;
use App\TableBuilder\Column\MyCustomColumn;

$csvRenderer = new CsvRenderer();
$table = ...
$csvRenderer
    ->registerCellValueTransformer(MyCustomColumn::class, new MyCustomColumnTransformer())
    ->renderTable($table, Writer::createFromPath('/tmp/mycsv.csv'));
```

You can create a controller to export a table as CSV with Symfony like this:
```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\TableBuilder\TableFactory\UserTableFactory;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WArslett\TableBuilder\Renderer\Csv\CsvRenderer;
use WArslett\TableBuilder\RequestAdapter\SymfonyHttpAdapter;

class UserTableCsvExportController
{
    private UserTableFactory $userTableFactory;
    private CsvRenderer $csvRenderer;

    public function __construct(
        UserTableFactory $userTableFactory,
        CsvRenderer $csvRenderer
    ) {
        $this->userTableFactory = $userTableFactory;
        $this->csvRenderer = $csvRenderer;
    }

    public function __invoke(Request $request): Response
    {
        $table = $this->userTableFactory->buildTable('users');
        $table->handleRequest(SymfonyHttpAdapter::withRequest($request));
        
        $writer = Writer::createFromPath('php://temp', 'r+');
        $this->csvRenderer->renderTable($table, $writer);
        
        return new Response($writer->getContent(), 200, [
            'Content-Encoding' => 'none',
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="users.csv"'
        ]);
    }
}
```

[Next: Single Page Applications](./single_page_applications.md)
