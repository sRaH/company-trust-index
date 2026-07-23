<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Company;
use App\Entity\Review;
use PHPUnit\Framework\TestCase;

final class EntityTest extends TestCase
{
    public function testCompanyAccessorsExposeAssignedState(): void
    {
        $createdAt = new \DateTimeImmutable('2026-07-20 10:00:00');
        $updatedAt = new \DateTimeImmutable('2026-07-21 11:00:00');
        $company = new Company();

        $this->assertNull($company->getId());
        $this->assertSame($company, $company->setName('TestCorp'));
        $this->assertSame($company, $company->setCreatedAt($createdAt));
        $this->assertSame($company, $company->setUpdatedAt($updatedAt));
        $this->assertSame('TestCorp', $company->getName());
        $this->assertCount(0, $company->getReviews());
        $this->assertSame($createdAt, $company->getCreatedAt());
        $this->assertSame($updatedAt, $company->getUpdatedAt());
    }

    public function testReviewAccessorsExposeAssignedState(): void
    {
        $company = (new Company())->setName('Example Ltd');
        $createdAt = new \DateTimeImmutable('2026-07-20 10:00:00');
        $updatedAt = new \DateTimeImmutable('2026-07-21 11:00:00');
        $review = new Review();

        $this->assertNull($review->getId());
        $this->assertSame($review, $review->setCompany($company));
        $this->assertSame($review, $review->setRating(4));
        $this->assertSame($review, $review->setReviewText('Reliable service.'));
        $this->assertSame($review, $review->setAuthorEmail('author@example.com'));
        $this->assertSame($review, $review->setCreatedAt($createdAt));
        $this->assertSame($review, $review->setUpdatedAt($updatedAt));
        $this->assertSame($company, $review->getCompany());
        $this->assertSame(4, $review->getRating());
        $this->assertSame('Reliable service.', $review->getReviewText());
        $this->assertSame('author@example.com', $review->getAuthorEmail());
        $this->assertSame($createdAt, $review->getCreatedAt());
        $this->assertSame($updatedAt, $review->getUpdatedAt());
    }
}
