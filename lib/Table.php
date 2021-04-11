<?php

declare(strict_types=1);

namespace WArslett\TableBuilder;

use JsonSerializable;
use Psr\Http\Message\RequestInterface as Psr7RequestInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use WArslett\TableBuilder\Column\ColumnInterface;
use WArslett\TableBuilder\DataAdapter\DataAdapterInterface;
use WArslett\TableBuilder\Exception\DataAdapterException;
use WArslett\TableBuilder\Exception\NoDataAdapterException;
use WArslett\TableBuilder\Exception\SortToggleException;
use WArslett\TableBuilder\RequestAdapter\ArrayRequestAdapter;
use WArslett\TableBuilder\RequestAdapter\Psr7Adapter;
use WArslett\TableBuilder\RequestAdapter\RequestAdapterInterface;
use WArslett\TableBuilder\RequestAdapter\SymfonyHttpAdapter;

class Table implements JsonSerializable
{
    public const DEFAULT_ROWS_PER_PAGE = 20;
    public const DEFAULT_MAX_ROWS_PER_PAGE = 100;

    /** @var string */
    private $name;

    /** @var array<string, ColumnInterface> */
    private $columns;

    /** @var int */
    private $rowsPerPage;

    /** @var int */
    private $maxRowsPerPage;

    /** @var array<string, TableHeading> */
    private $headings = [];

    /** @var DataAdapterInterface|null  */
    private $dataAdapter = null;

    /** @var array - the query params of the handled request */
    private $params = [];

    /** @var array<array<string, TableCell>> */
    private $rows = [];

    /** @var int */
    private $totalRows = 0;

    /** @var int */
    private $pageNumber = 0;

    /** @var string|null */
    private $sortColumnName = null;

    /** @var bool */
    private $isSortedDescending = false;

    /** @var array<int> */
    private $rowsPerPageOptions;

    public function __construct(
        string $name,
        array $columns,
        int $defaultRowsPerPage = self::DEFAULT_ROWS_PER_PAGE,
        int $maxRowsPerPage = self::DEFAULT_MAX_ROWS_PER_PAGE,
        array $rowsPerPageOptions = []
    ) {
        $this->name = $name;
        $this->columns = $columns;
        $this->headings = array_map(function (ColumnInterface $column): TableHeading {
            return $column->buildTableHeading();
        }, $this->columns);
        $this->rowsPerPage = $defaultRowsPerPage;
        $this->maxRowsPerPage = $maxRowsPerPage;
        $this->rowsPerPageOptions = $rowsPerPageOptions;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param DataAdapterInterface $dataAdapter
     * @return $this
     * @throws SortToggleException
     */
    public function setDataAdapter(DataAdapterInterface $dataAdapter): self
    {
        foreach ($this->columns as $column) {
            $sortToggle = $column->getSortToggle();
            if (null !== $sortToggle && false === $dataAdapter->canSort($sortToggle)) {
                throw new SortToggleException(sprintf(
                    "The data adapter cannot sort using the toggle \"%s\" which is set on the column \"%s\". "
                    . "Did you forget some config?",
                    $sortToggle,
                    $column->getName()
                ));
            }
        }

        $this->dataAdapter = $dataAdapter;
        return $this;
    }

    /**
     * @param RequestAdapterInterface $request
     * @return $this
     * @throws NoDataAdapterException
     * @throws DataAdapterException
     */
    public function handleRequest(RequestAdapterInterface $request): self
    {
        if (null === $this->dataAdapter) {
            throw new NoDataAdapterException("Cannot handle request until data adapter has been set");
        }

        $this->params = $request->getParameters();
        $tableRequestParameters = $this->params[$this->name] ?? [];

        $requestRowsPerPage = (int) ($tableRequestParameters['rows_per_page'] ?? $this->rowsPerPage);
        $this->rowsPerPage = $requestRowsPerPage > $this->maxRowsPerPage ? $this->maxRowsPerPage : $requestRowsPerPage;
        $this->pageNumber = (int) ($tableRequestParameters['page'] ?? 1);
        $this->totalRows = $this->dataAdapter->countTotalRows();

        $this->sortColumnName = $tableRequestParameters['sort_column'] ?? null;
        $sortToggle = null;
        if (null !== $this->sortColumnName && isset($this->columns[$this->sortColumnName])) {
            $sortColumn = $this->columns[$this->sortColumnName];
            $sortToggle = $sortColumn->getSortToggle();
        }

        $sortDirection = $tableRequestParameters['sort_dir'] ?? null;
        $this->isSortedDescending = $sortDirection === RequestAdapterInterface::SORT_DESCENDING;

        $this->rows = array_map(function ($row): array {
            return array_map(function (ColumnInterface $column) use ($row): TableCell {
                return $column->buildTableCell($row);
            }, $this->columns);
        }, $this->dataAdapter->getPage($this->pageNumber, $this->rowsPerPage, $sortToggle, $this->isSortedDescending));

        return $this;
    }

    /**
     * @codeCoverageIgnore - just an adapter
     * @param array $parameters
     * @return $this
     * @throws DataAdapterException
     * @throws NoDataAdapterException
     */
    public function handleParameters(array $parameters): self
    {
        return $this->handleRequest(ArrayRequestAdapter::withArray($parameters));
    }

    /**
     * @codeCoverageIgnore - just an adapter
     * @param Psr7RequestInterface $request
     * @return $this
     * @throws DataAdapterException
     * @throws NoDataAdapterException
     */
    public function handlePsr7Request(Psr7RequestInterface $request): self
    {
        return $this->handleRequest(Psr7Adapter::withRequest($request));
    }

    /**
     * @codeCoverageIgnore - just an adapter
     * @param SymfonyRequest $request
     * @return $this
     * @throws DataAdapterException
     * @throws NoDataAdapterException
     */
    public function handleSymfonyRequest(SymfonyRequest $request): self
    {
        return $this->handleRequest(SymfonyHttpAdapter::withRequest($request));
    }

    /**
     * @param array $merge - an array of params to merge into the table
     *     params for this table
     * @return array - the query params of the current request merged with
     *     any provided table params
     */
    public function getParams(array $merge = []): array
    {
        return array_merge($this->params, [
            $this->name => array_merge([
                'page' => $this->pageNumber,
                'rows_per_page' => $this->rowsPerPage,
                'sort_column' => $this->sortColumnName,
                'sort_dir' => $this->isSortedDescending
                    ? RequestAdapterInterface::SORT_DESCENDING
                    : null,
            ], $merge)
        ]);
    }

    /**
     * @return array<string, TableHeading>
     */
    public function getHeadings(): array
    {
        return $this->headings;
    }

    /**
     * @return array<array<string, TableCell>>
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * @return int
     */
    public function getRowsPerPage(): int
    {
        return $this->rowsPerPage;
    }

    /**
     * @return int
     */
    public function getTotalRows(): int
    {
        return $this->totalRows;
    }

    /**
     * @return int
     */
    public function getPageNumber(): int
    {
        return $this->pageNumber;
    }

    /**
     * @return int
     */
    public function getTotalPages(): int
    {
        return (int) ceil($this->totalRows / $this->rowsPerPage);
    }

    /**
     * @return string|null
     */
    public function getSortColumnName(): ?string
    {
        return $this->sortColumnName;
    }

    /**
     * @return bool
     */
    public function isSortedDescending(): bool
    {
        return $this->isSortedDescending;
    }

    /**
     * @return array<int>
     */
    public function getRowsPerPageOptions(): array
    {
        return $this->rowsPerPageOptions;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'rows_per_page' => $this->rowsPerPage,
            'max_rows_per_page' => $this->maxRowsPerPage,
            'headings' => $this->headings,
            'rows' => $this->rows,
            'total_rows' => $this->totalRows,
            'page_number' => $this->pageNumber,
            'rows_per_page_options' => $this->rowsPerPageOptions,
            'sort_column' => $this->sortColumnName,
            'sort_dir' => $this->isSortedDescending() ? 'desc' : 'asc',
        ];
    }
}
