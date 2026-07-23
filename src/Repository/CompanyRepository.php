<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    /**
     * Finds an existing Company by name, or creates a new (non-persisted) one.
     * The caller is responsible for persisting the returned entity.
     */
    public function findOrCreateByName(string $name): Company
    {
        $company = $this->findOneBy(['name' => $name]);

        if (null === $company) {
            $company = (new Company())->setName($name);
        }

        return $company;
    }

    /**
     * @return list<string>
     */
    public function findNamesBySearch(string $query, int $limit = 10): array
    {
        $rows = $this->createQueryBuilder('c')
            ->select('c.name')
            ->where('LOWER(c.name) LIKE :query')
            ->setParameter('query', '%'.mb_strtolower($query).'%')
            ->orderBy('c.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return array_column($rows, 'name');
    }

    /**
     * Per-company review count and average rating, ordered by average rating DESC.
     *
     * @return array<int, array{name: string, reviewCount: int, averageRating: float}>
     */
    public function findWithStats(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.name', 'COUNT(r.id) AS reviewCount', 'AVG(r.rating) AS averageRating')
            ->leftJoin('c.reviews', 'r')
            ->groupBy('c.id', 'c.name')
            ->orderBy('averageRating', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }
}
