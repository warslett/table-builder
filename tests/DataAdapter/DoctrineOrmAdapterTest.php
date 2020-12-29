<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests\DataAdapter;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use stdClass;
use WArslett\TableBuilder\DataAdapter\DoctrineOrmAdapter;
use WArslett\TableBuilder\Exception\DataAdapterException;
use WArslett\TableBuilder\Tests\TestCase;
use Mockery as m;
use Mockery\Mock;

class DoctrineOrmAdapterTest extends TestCase
{

    public function testGetPageBuildsQuery(): void
    {
        $query = $this->mockQueryWithResult();
        $entityManager = $this->mockEntityManager($query);
        $adapter = DoctrineOrmAdapter::withQueryBuilder(new QueryBuilder($entityManager));
        $pageNumber = 2;
        $rowsPerPage = 20;

        $adapter->getPage($pageNumber, $rowsPerPage);

        $query->shouldHaveReceived('setFirstResult')->once()->with(20);
        $query->shouldHaveReceived('setMaxResults')->once()->with($rowsPerPage);
    }

    public function testGetPageDoesNotAlterOriginalQuery(): void
    {
        $query = $this->mockQueryWithResult();
        $entityManager = $this->mockEntityManager($query);
        $queryBuilder = new QueryBuilder($entityManager);
        $queryBuilder->select('u')->from('User', 'u');
        $dql = $queryBuilder->getDQL();
        $adapter = DoctrineOrmAdapter::withQueryBuilder($queryBuilder)
            ->mapSortToggle('foo', 'bar');

        $adapter->getPage(1, 10, 'foo');

        $this->assertSame($dql, $queryBuilder->getDQL());
    }

    public function testGetPageWithSortToggleSetsOrderByOnQuery(): void
    {
        $query = $this->mockQueryWithResult();
        $entityManager = $this->mockEntityManager($query);
        $sortToggle = 'foo';

        $queryBuilder = new QueryBuilder($entityManager);
        $queryBuilder->select('u')->from('User', 'u');
        $adapter = DoctrineOrmAdapter::withQueryBuilder($queryBuilder)->mapSortToggle('foo', 'u.foo');
        $adapter->getPage(1, 10, $sortToggle);

        $entityManager->shouldHaveReceived('createQuery')
            ->once()
            ->with('SELECT u FROM User u ORDER BY u.foo ASC');
    }

    public function testGetPageGetsResult(): void
    {
        $query = $this->mockQueryWithResult();
        $entityManager = $this->mockEntityManager($query);
        $adapter = DoctrineOrmAdapter::withQueryBuilder(new QueryBuilder($entityManager));
        $pageNumber = 2;
        $rowsPerPage = 20;

        $adapter->getPage($pageNumber, $rowsPerPage);

        $query->shouldHaveReceived('getResult')->once();
    }

    public function testGetPageReturnsResult(): void
    {
        $result = [new stdClass()];
        $query = $this->mockQueryWithResult($result);
        $entityManager = $this->mockEntityManager($query);
        $adapter = DoctrineOrmAdapter::withQueryBuilder(new QueryBuilder($entityManager));
        $pageNumber = 2;
        $rowsPerPage = 20;

        $actual = $adapter->getPage($pageNumber, $rowsPerPage);

        $this->assertSame($result, $actual);
    }

    /**
     * @return void
     * @throws DataAdapterException
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function testCountDoesNotAlterOriginalQuery(): void
    {
        $entityManager = $this->mockEntityManager(
            $this->mockQueryWithSingleScalarResult('2'),
            $this->mockExpressionBuilder('count(u)')
        );
        $queryBuilder = new QueryBuilder($entityManager);
        $queryBuilder->select('u')->from('User', 'u');
        $dql = $queryBuilder->getDQL();
        $adapter = DoctrineOrmAdapter::withQueryBuilder($queryBuilder)
            ->mapSortToggle('foo', 'bar');

        $adapter->countTotalRows();

        $this->assertSame($dql, $queryBuilder->getDQL());
    }

    /**
     * @return void
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function testCountNoFromPartsThrowsException(): void
    {
        $expression = 'count(u)';
        $entityManager = $this->mockEntityManager(
            $this->mockQueryWithSingleScalarResult('2'),
            $this->mockExpressionBuilder($expression)
        );
        $queryBuilder = new QueryBuilder($entityManager);
        $queryBuilder->select('u');
        $adapter = DoctrineOrmAdapter::withQueryBuilder($queryBuilder);

        $this->expectException(DataAdapterException::class);

        $adapter->countTotalRows();
    }

    /**
     * @return void
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function testCountResetsSelect(): void
    {
        $expression = 'count(u)';
        $entityManager = $this->mockEntityManager(
            $this->mockQueryWithSingleScalarResult('2'),
            $this->mockExpressionBuilder($expression)
        );
        $queryBuilder = new QueryBuilder($entityManager);
        $queryBuilder->select('u')->from('User', 'u');
        $adapter = DoctrineOrmAdapter::withQueryBuilder($queryBuilder);

        $adapter->countTotalRows();

        $entityManager->shouldHaveReceived('createQuery')->once()->with('SELECT count(u) FROM User u');
    }

    /**
     * @return void
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function testCountReturnsResult(): void
    {
        $result = 5;
        $queryBuilder = new QueryBuilder($this->mockEntityManager(
            $this->mockQueryWithSingleScalarResult($result),
            $this->mockExpressionBuilder('count(u)')
        ));
        $queryBuilder->select('u')->from('User', 'u');
        $adapter = DoctrineOrmAdapter::withQueryBuilder($queryBuilder);

        $actual = $adapter->countTotalRows();

        $this->assertSame($result, $actual);
    }

    /**
     * @return void
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws DataAdapterException
     */
    public function testCountGetsResult(): void
    {
        $query = $this->mockQueryWithSingleScalarResult('2');
        $queryBuilder = new QueryBuilder($this->mockEntityManager(
            $query,
            $this->mockExpressionBuilder('count(u)')
        ));
        $queryBuilder->select('u')->from('User', 'u');
        $adapter = DoctrineOrmAdapter::withQueryBuilder($queryBuilder);

        $adapter->countTotalRows();

        $query->shouldHaveReceived('getSingleScalarResult')->once();
    }

    /**
     * @return void
     */
    public function testCanSortWithUnMappedToggleReturnsFalse(): void
    {
        $query = $this->mockQueryWithSingleScalarResult('2');
        $adapter = DoctrineOrmAdapter::withQueryBuilder(new QueryBuilder($this->mockEntityManager($query)));

        $this->assertFalse($adapter->canSort('foo'));
    }

    /**
     * @return void
     */
    public function testCanSortWithMappedToggleReturnsTrue(): void
    {
        $query = $this->mockQueryWithSingleScalarResult('2');
        $adapter = DoctrineOrmAdapter::withQueryBuilder(new QueryBuilder($this->mockEntityManager($query)));
        $adapter->mapSortToggle('foo', 'e.foo');

        $this->assertTrue($adapter->canSort('foo'));
    }

    /**
     * @param AbstractQuery $query
     * @param Expr|null $expressionBuilder
     * @return EntityManagerInterface&Mock
     */
    private function mockEntityManager(AbstractQuery $query, ?Expr $expressionBuilder = null): EntityManagerInterface
    {
        $entityManager = m::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('createQuery')->andReturn($query);
        if (null !== $expressionBuilder) {
            $entityManager->shouldReceive('getExpressionBuilder')->andReturn($expressionBuilder);
        }
        return $entityManager;
    }

    /**
     * @return AbstractQuery&Mock
     */
    private function mockQuery(): AbstractQuery
    {
        $query = m::mock(AbstractQuery::class);
        $query->shouldReceive('setParameters')->andReturnSelf();
        $query->shouldReceive('setFirstResult')->andReturnSelf();
        $query->shouldReceive('setMaxResults')->andReturnSelf();
        return $query;
    }

    /**
     * @param array $result
     * @return AbstractQuery&Mock
     */
    private function mockQueryWithResult(array $result = []): AbstractQuery
    {
        $query = $this->mockQuery();
        $query->shouldReceive('getResult')->andReturn($result);
        return $query;
    }

    /**
     * @param mixed $result
     * @return AbstractQuery&Mock
     */
    private function mockQueryWithSingleScalarResult($result): AbstractQuery
    {
        $query = $this->mockQuery();
        $query->shouldReceive('getSingleScalarResult')->andReturn($result);
        return $query;
    }

    /**
     * @param string $expression
     * @return Expr&Mock
     */
    private function mockExpressionBuilder(string $expression): Expr
    {
        $expressionBuilder = m::mock(Expr::class);
        $expressionBuilder->shouldReceive('countDistinct')->andReturn($expression);
        return $expressionBuilder;
    }
}
