<?php

declare(strict_types=1);

namespace WArslett\TableBuilder;

use WArslett\TableBuilder\Column\ColumnInterface;
use WArslett\TableBuilder\DataAdapter\DataAdapterInterface;
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

    /** @var array<array<string, TableCell>> */
    private array $rows = [];

    /** @var int */
    private int $totalRows = 0;

    /** @var int */
    private int $pageNumber = 0;

    public function __construct(
        string $name,
        array $columns,
        int $defaultRowsPerPage = self::DEFAULT_ROWS_PER_PAGE,
        int $maxRowsPerPage = self::DEFAULT_MAX_ROWS_PER_PAGE
    ) {
        $this->name = $name;
        $this->columns = $columns;
        $this->headings = array_map(function (ColumnInterface $column): TableHeading {
            return $column->buildTableHeading();
        }, $this->columns);
        $this->rowsPerPage = $defaultRowsPerPage;
        $this->maxRowsPerPage = $maxRowsPerPage;
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
     */
    public function handleRequest(RequestAdapterInterface $request): self
    {
        if (null === $this->dataAdapter) {
            throw new NoDataAdapterException("Cannot handle request until data adapter has been set");
        }

        $tableRequestParameters = $request->getParameter($this->name);
        if (false === is_array($tableRequestParameters)) {
            $tableRequestParameters = null;
        }

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
}
