<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ReviewRepository;
use App\Validator\Constraints\NoHtml;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ORM\Table(name: 'review')]
#[ORM\UniqueConstraint(name: 'review_company_author_uniq', columns: ['company_id', 'author_email'])]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private Company $company;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull]
    #[Assert\Range(min: 1, max: 5)]
    private int $rating;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 2000, minMessage: 'review.too_short', maxMessage: 'review.too_long')]
    #[NoHtml]
    private string $reviewText;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    private string $authorEmail;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'update')]
    private \DateTimeImmutable $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function setCompany(Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    public function getReviewText(): string
    {
        return $this->reviewText;
    }

    public function setReviewText(string $reviewText): self
    {
        $this->reviewText = $reviewText;

        return $this;
    }

    public function getAuthorEmail(): string
    {
        return $this->authorEmail;
    }

    public function setAuthorEmail(string $authorEmail): self
    {
        $this->authorEmail = strtolower(trim($authorEmail));

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
