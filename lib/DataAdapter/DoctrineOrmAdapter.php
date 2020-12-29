<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\DataAdapter;

use Doctrine\ORM;
use WArslett\TableBuilder\Exception\DataAdapterException;

final class DoctrineOrmAdapter implements DataAdapterInterface
{
    /** @var ORM\QueryBuilder */
    private ORM\QueryBuilder $queryBuilder;

    /** @var array<string, string> */
    private array $sortToggleMapping = [];

    public function __construct(ORM\QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @param int $pageNumber
     * @param int $rowsPerPage
     * @param string|null $sortToggle
     * @param bool $isSortedDescending
     * @return array<mixed>
     */
    public function getPage(
        int $pageNumber,
        int $rowsPerPage,
        ?string $sortToggle = null,
        bool $isSortedDescending = false
    ): array {
        $queryBuilder = clone($this->queryBuilder);

        if (isset($this->sortToggleMapping[$sortToggle])) {
            $queryBuilder->orderBy($this->sortToggleMapping[$sortToggle], $isSortedDescending ? 'DESC' : 'ASC');
        }

        return $queryBuilder
            ->setFirstResult(($pageNumber - 1) * $rowsPerPage)
            ->setMaxResults($rowsPerPage)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return int
     * @throws ORM\NoResultException
     * @throws ORM\NonUniqueResultException
     * @throws DataAdapterException
     */
    public function countTotalRows(): int
    {
        $queryBuilder = clone($this->queryBuilder);

        /** @var array<ORM\Query\Expr\From> $fromParts */
        $fromParts = $queryBuilder->getDQLPart('from');

        if (count($fromParts) !== 1) {
            throw new DataAdapterException("Query Builder should have exactly one from part");
        }

        $from = $fromParts[0];

        return (int) $queryBuilder
            ->resetDQLPart('select')
            ->select($this->queryBuilder->expr()->countDistinct($from->getAlias()))
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param string $sortToggle
     * @return bool
     */
    public function canSort(string $sortToggle): bool
    {
        return isset($this->sortToggleMapping[$sortToggle]);
    }

    /**
     * @param string $sortToggle
     * @param string $orderBy
     * @return $this
     */
    public function mapSortToggle(string $sortToggle, string $orderBy): self
    {
        $this->sortToggleMapping[$sortToggle] = $orderBy;
        return $this;
    }

    /**
     * @param ORM\QueryBuilder $queryBuilder
     * @return static
     */
    public static function withQueryBuilder(ORM\QueryBuilder $queryBuilder): self
    {
        return new self($queryBuilder);
    }
}
