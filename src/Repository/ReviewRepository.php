<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     * @return Review[]
     */
    public function findLatest(int $limit = 10, int $offset = 0): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function existsByCompanyAndAuthorEmail(Company $company, string $authorEmail): bool
    {
        if (null === $company->getId()) {
            return false;
        }

        return null !== $this->createQueryBuilder('r')
            ->select('1')
            ->andWhere('r.company = :company')
            ->andWhere('r.authorEmail = :authorEmail')
            ->setParameter('company', $company)
            ->setParameter('authorEmail', strtolower(trim($authorEmail)))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
