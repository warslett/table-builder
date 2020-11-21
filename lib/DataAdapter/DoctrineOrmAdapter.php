<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\DataAdapter;

use Doctrine\ORM;
use WArslett\TableBuilder\Exception\DataAdapterException;

final class DoctrineOrmAdapter implements DataAdapterInterface
{
    private ORM\QueryBuilder $queryBuilder;

    public function __construct(ORM\QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @param int $pageNumber
     * @param int $rowsPerPage
     * @return array
     */
    public function getPage(int $pageNumber, int $rowsPerPage): array
    {
        $queryBuilder = clone($this->queryBuilder);

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

        /** @var ORM\Query\Expr\From $from */
        $from = $fromParts[0];

        return (int) $queryBuilder
            ->resetDQLPart('select')
            ->select($this->queryBuilder->expr()->countDistinct($from->getAlias()))
            ->getQuery()
            ->getSingleScalarResult();
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
