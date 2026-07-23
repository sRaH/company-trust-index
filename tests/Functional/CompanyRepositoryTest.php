<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Company;
use App\Entity\Review;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CompanyRepositoryTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $c1 = new Company();
        $c1->setName('TestCorp');
        $em->persist($c1);

        $c2 = new Company();
        $c2->setName('Example Ltd');
        $em->persist($c2);

        $r1 = new Review();
        $r1->setCompany($c1);
        $r1->setRating(5);
        $r1->setReviewText('Kiváló szolgáltatás.');
        $r1->setAuthorEmail('alice@example.com');
        $em->persist($r1);

        $r2 = new Review();
        $r2->setCompany($c1);
        $r2->setRating(3);
        $r2->setReviewText('Közepes tapasztalat.');
        $r2->setAuthorEmail('bob@example.com');
        $em->persist($r2);

        $r3 = new Review();
        $r3->setCompany($c2);
        $r3->setRating(2);
        $r3->setReviewText('Jó cég, ajánlom.');
        $r3->setAuthorEmail('charlie@example.com');
        $em->persist($r3);

        $em->flush();
    }

    public function testFindWithStatsReturnsRowsOrderedByAvgRatingDesc(): void
    {
        /** @var CompanyRepository $repo */
        $repo = static::getContainer()->get(CompanyRepository::class);

        $stats = $repo->findWithStats();

        $this->assertCount(2, $stats);
        $this->assertSame('TestCorp', $stats[0]['name']);
        $this->assertSame('Example Ltd', $stats[1]['name']);

        $names = array_column($stats, 'name');
        $this->assertContains('TestCorp', $names);
        $this->assertContains('Example Ltd', $names);

        foreach ($stats as $row) {
            match ($row['name']) {
                'TestCorp' => $this->assertSame(2, $row['reviewCount']),
                'Example Ltd' => $this->assertSame(1, $row['reviewCount']),
                default => $this->fail('Unexpected company: '.$row['name']),
            };
            $expectedAverage = 'TestCorp' === $row['name'] ? 4.0 : 2.0;
            $this->assertEqualsWithDelta($expectedAverage, (float) $row['averageRating'], 0.1);
        }
    }

    public function testFindOrCreateByNameCreatesNewCompany(): void
    {
        /** @var CompanyRepository $repo */
        $repo = static::getContainer()->get(CompanyRepository::class);

        $company = $repo->findOrCreateByName('BrandNewCo');

        $this->assertInstanceOf(Company::class, $company);
        $this->assertSame('BrandNewCo', $company->getName());
        $this->assertNull($company->getId(), 'New company should not be persisted by findOrCreateByName');

        // Verify existing company is found
        $existing = $repo->findOrCreateByName('TestCorp');
        $this->assertNotNull($existing->getId(), 'Existing company should have an ID');
    }

    public function testFindNamesBySearchReturnsMatchesInAscendingOrder(): void
    {
        /** @var CompanyRepository $repo */
        $repo = static::getContainer()->get(CompanyRepository::class);

        $names = $repo->findNamesBySearch('ex');

        $this->assertSame(['Example Ltd'], $names);
    }

    public function testFindNamesBySearchIsCaseInsensitive(): void
    {
        /** @var CompanyRepository $repo */
        $repo = static::getContainer()->get(CompanyRepository::class);

        $names = $repo->findNamesBySearch('TEST');

        $this->assertSame(['TestCorp'], $names);
    }

    public function testFindNamesBySearchExcludesNonMatches(): void
    {
        /** @var CompanyRepository $repo */
        $repo = static::getContainer()->get(CompanyRepository::class);

        $names = $repo->findNamesBySearch('zzzz');

        $this->assertSame([], $names);
    }

    public function testFindNamesBySearchRespectsLimit(): void
    {
        /** @var CompanyRepository $repo */
        $repo = static::getContainer()->get(CompanyRepository::class);

        $names = $repo->findNamesBySearch('e', 1);

        $this->assertCount(1, $names);
    }
}
