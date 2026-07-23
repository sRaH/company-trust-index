<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Company;
use App\Entity\Review;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ReviewRepositoryTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $company = new Company();
        $company->setName('TestCorp');
        $em->persist($company);

        foreach (range(1, 12) as $i) {
            $review = new Review();
            $review->setCompany($company);
            $review->setRating(($i % 5) + 1);
            $review->setReviewText(sprintf('Review content %d.', $i));
            $review->setAuthorEmail(sprintf('user%d@example.com', $i));
            $review->setCreatedAt(new \DateTimeImmutable(sprintf('-%d hours', 13 - $i)));
            $em->persist($review);
        }

        $em->flush();
    }

    public function testFindLatestOrdersByCreatedAtDescending(): void
    {
        /** @var ReviewRepository $repo */
        $repo = static::getContainer()->get(ReviewRepository::class);

        $reviews = $repo->findLatest();

        $this->assertCount(10, $reviews);

        $previous = null;
        foreach ($reviews as $review) {
            if (null !== $previous) {
                $this->assertTrue(
                    $previous->getCreatedAt() >= $review->getCreatedAt(),
                    'Reviews must be ordered from newest to oldest.'
                );
            }
            $previous = $review;
        }
    }

    public function testFindLatestWithOffsetReturnsSecondPage(): void
    {
        /** @var ReviewRepository $repo */
        $repo = static::getContainer()->get(ReviewRepository::class);

        $firstPage = $repo->findLatest(10, 0);
        $secondPage = $repo->findLatest(10, 10);

        $this->assertCount(10, $firstPage);
        $this->assertCount(2, $secondPage);

        $firstIds = array_map(static fn (Review $r): ?int => $r->getId(), $firstPage);
        $secondIds = array_map(static fn (Review $r): ?int => $r->getId(), $secondPage);

        $this->assertSame([], array_intersect($firstIds, $secondIds));
    }

    public function testFindLatestReturnsEmptyBeyondTotal(): void
    {
        /** @var ReviewRepository $repo */
        $repo = static::getContainer()->get(ReviewRepository::class);

        $this->assertSame([], $repo->findLatest(10, 100));
    }
}
