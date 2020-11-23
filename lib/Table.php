<?php

declare(strict_types=1);

namespace WArslett\TableBuilder;

use WArslett\TableBuilder\Column\ColumnInterface;
use WArslett\TableBuilder\DataAdapter\DataAdapterInterface;
use WArslett\TableBuilder\Exception\DataAdapterException;
use WArslett\TableBuilder\Exception\NoDataAdapterException;
use WArslett\TableBuilder\RequestAdapter\RequestAdapterInterface;

class Table
{
    public const DEFAULT_ROWS_PER_PAGE = 20;
    public const DEFAULT_MAX_ROWS_PER_PAGE = 100;

    /** @var string */
    private string $name;

    /** @var array<string, ColumnInterface> */
    private array $columns;

    /** @var int */
    private int $rowsPerPage;

    /** @var int */
    private int $maxRowsPerPage;

    /** @var array<string, TableHeading> */
    private array $headings = [];

    /** @var DataAdapterInterface|null  */
    private ?DataAdapterInterface $dataAdapter = null;

    /** @var array - the query params of the handled request */
    private array $params = [];

    /** @var array<array<string, TableCell>> */
    private array $rows = [];

    /** @var int */
    private int $totalRows = 0;

    /** @var int */
    private int $pageNumber = 0;

    /** @var array<int> */
    private array $rowsPerPageOptions;

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
     */
    public function setDataAdapter(DataAdapterInterface $dataAdapter): self
    {
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

        $this->rows = array_map(function ($row): array {
            return array_map(function (ColumnInterface $column) use ($row): TableCell {
                return $column->buildTableCell($row);
            }, $this->columns);
        }, $this->dataAdapter->getPage($this->pageNumber, $this->rowsPerPage));

        return $this;
    }

    /**
     * @param array $merge - an array of params to merge into the table params for this table
     * @return array - the query params of the current request merged with any provided table params
     */
    public function getParams(array $merge = []): array
    {
        return array_merge($this->params, [
            $this->name => array_merge([
                'page' => $this->pageNumber,
                'rows_per_page' => $this->rowsPerPage
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
     * @return array<int>
     */
    public function getRowsPerPageOptions(): array
    {
        return $this->rowsPerPageOptions;
    }
}
