# Data Adapters
Data Adapters are used for loading data into tables. Data Adapters implement
WArslett\TableBuilder\DataAdapter\DataAdapterInterface.

## <a name="ArrayDataAdapter"></a>ArrayDataAdapter
ArrayDataAdapter will load data from a php array into the table.
```php
use WArslett\TableBuilder\DataAdapter\ArrayDataAdapter;

$dataAdapter = ArrayDataAdapter::withArray([
  ['foo' => 'bar', 'number' => 5],
  ['foo' => 'baz', 'number' => 2],
  ['foo' => 'qux', 'number' => 3]
]);
```
The data can be sorted by mapping a [usort callback](https://www.php.net/manual/en/function.usort.php) to a sort toggle.
```php
$dataAdapter->mapSortToggle('last_login', fn($a, $b) => $a['number'] < $b['number'] ? -1 : 1));
```
The usort callback should sort the data ascending. The adapter will reverse the callback when sorting descended.

It is not recommended however, you could use the array adapter to display data that has been fetched from the database
such as the output from a repository method.
```php
use WArslett\TableBuilder\DataAdapter\ArrayDataAdapter;

$dataAdapter = ArrayDataAdapter::withArray($this->repository->findAll());
```
However, this is not suitable for large sets of data because when the adapter gets a page of data it just takes a slice
of the whole data set. That means the whole data set is fetched on every page rather than only loading the data that
will be loaded into the table for the current page.

If you are working with 1000s of rows of data this is a very inefficient way of paginating data. In these situations it
will be more efficient to use the DoctrineOrmAdapter which will apply the page limit to the query so you only fetch
the data from the database required for the current page.

## <a name="DoctrineOrmAdapter"></a>DoctrineOrmAdapter
DoctrineOrmAdapter will load data from a doctrine ORM Query. It takes an instance of a Doctrine QueryBuilder which it
then alters to count, sort and paginate the data.
```php
use App\Entity\User;
use WArslett\TableBuilder\DataAdapter\DoctrineOrmAdapter;

$entityManager = ...
$dataAdapter = DoctrineOrmAdapter::withQueryBuilder($entityManager->createQueryBuilder()
    ->select('u')
    ->from(User::Class));
```
The data can be sorted by mapping an order by clause to a sort toggle.
```php
$dataAdapter->mapSortToggle('sort_name', 'u.name');
```

[Next: Renderers](./renderers/index.md)
