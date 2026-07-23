<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Company;
use App\Entity\Review;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ReviewControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $c1 = new Company();
        $c1->setName('TestCorp');
        $em->persist($c1);

        $c2 = new Company();
        $c2->setName('Example Ltd');
        $em->persist($c2);

        $c3 = new Company();
        $c3->setName('Acme Kft');
        $em->persist($c3);

        $companies = [$c1, $c2, $c3];
        $texts = ['Kiváló szolgáltatás.', 'Közepes tapasztalat.', 'Jó cég, ajánlom.', 'Lassú ügyfélszolgálat.', 'Tökéletes kommunikáció.', 'Nem ajánlom.', 'Megbízható partner.', 'A vártnál gyengébb.', 'Kiváló ár-érték.', 'Átlagos élmény.', 'Jó termékek.', 'Korrekt kiszolgálás.'];

        foreach (range(1, 12) as $i) {
            $review = new Review();
            $review->setCompany($companies[$i % 3]);
            $review->setRating(($i % 5) + 1);
            $review->setReviewText($texts[$i - 1]);
            $review->setAuthorEmail(sprintf('user%d@example.com', $i));
            $review->setCreatedAt(new \DateTimeImmutable(sprintf('-%d hours', 13 - $i)));
            $em->persist($review);
        }

        $em->flush();
    }

    public function testHomepageShowsSeededCompanies(): void
    {
        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'TestCorp');
        $this->assertSelectorTextContains('body', 'Example Ltd');
    }

    public function testCompaniesPageContainsSearchAndTableRows(): void
    {
        $crawler = $this->client->request('GET', '/companies');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#company-search');
        $this->assertSelectorExists('[data-controller="company-autocomplete"]');
        $this->assertSelectorExists('[data-controller="company-stats-filter"]');
        $this->assertCount(3, $crawler->filter('[data-company-stats-filter-target="row"]'));
        $this->assertSelectorExists('[data-company-stats-filter-target="empty"][hidden]');
    }

    public function testHomepagePaginationShowsTenItemsOnFirstPage(): void
    {
        $crawler = $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertCount(10, $crawler->filter('.review-card'));
        $this->assertSelectorExists('.cti-pagination');
        $this->assertSelectorTextContains('.cti-pagination__count', '12');
        $this->assertSelectorExists('.page-item.active .page-link');
    }

    public function testReviewCardsExposeStimulusExpandControllerWithAriaWiring(): void
    {
        $crawler = $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        // Every review card is wired to the expand controller so the full text
        // is preserved and the controller can reveal the toggle per card.
        $this->assertGreaterThan(0, $crawler->filter('.review-card[data-controller="review-expand"]')->count());
        // The text region carries a unique id the toggle points at via aria-controls.
        $firstCard = $crawler->filter('.review-card[data-controller="review-expand"]')->first();
        $textId = $firstCard->filter('.review-card__text')->attr('id');
        $this->assertNotNull($textId);
        $this->assertStringStartsWith('review-text-', $textId);
        $toggle = $firstCard->filter('.review-card__expand');
        $this->assertSame('false', $toggle->attr('aria-expanded'));
        $this->assertSame($textId, $toggle->attr('aria-controls'));
        $this->assertSame('Bővebben', trim($toggle->text(null, false)));
        // The toggle starts hidden server-side (boolean attribute); the controller
        // reveals it when the clamped preview actually overflows.
        $this->assertNotNull($toggle->attr('hidden'));
    }

    public function testLongReviewRendersFullTextWithoutServerSideTruncation(): void
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $company = new Company();
        $company->setName('LongTextCorp');
        $em->persist($company);

        // Well over the previous 200-char truncation limit (entity allows up to 2000).
        $longText = str_repeat('Részletes visszajelzés a szolgáltatásról és a kommunikációról. ', 7);
        $review = new Review();
        $review->setCompany($company);
        $review->setRating(4);
        $review->setReviewText($longText);
        $review->setAuthorEmail('long@example.com');
        $em->persist($review);
        $em->flush();

        $crawler = $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();

        $card = $crawler->filter('.review-card')->reduce(
            static fn (\Symfony\Component\DomCrawler\Crawler $node): bool => str_contains($node->text(null, false), 'LongTextCorp'),
        )->first();

        // The full text is present in the DOM — no ellipsis truncation.
        $renderedText = $card->filter('.review-card__text')->text(null, false);
        $this->assertStringContainsString($longText, $renderedText);
        $this->assertStringNotContainsString('…', $renderedText);

        // The toggle's aria-controls resolves to this review's text region.
        $expectedId = 'review-text-'.$review->getId();
        $this->assertSame(1, $card->filter('#'.$expectedId)->count());
        $this->assertSame($expectedId, $card->filter('.review-card__expand')->attr('aria-controls'));
    }

    public function testShortReviewRendersFullTextWithNoTruncation(): void
    {
        $crawler = $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        // The newest seeded review sorts first on the homepage; its short text
        // is shown whole (not cut by the old u.truncate()).
        $this->assertSelectorTextContains('.review-card__text', 'Korrekt kiszolgálás.');
        // No review text carries the server-side ellipsis any more.
        foreach ($crawler->filter('.review-card__text') as $node) {
            $this->assertStringNotContainsString('…', $node->textContent);
        }
    }

    public function testPaginationSecondPageShowsRemainingReviews(): void
    {
        $crawler = $this->client->request('GET', '/?page=2');

        $this->assertResponseIsSuccessful();
        $this->assertCount(2, $crawler->filter('.review-card'));
        $this->assertSelectorExists('.page-item.active');
    }

    public function testExcessivePageIsClampedToLastPage(): void
    {
        $this->client->request('GET', '/?page=99');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.page-item.active .page-link');
        $this->assertSelectorTextContains('.page-item.active .page-link', '2');
    }

    public function testReviewDetailsPageShowsTheSelectedReview(): void
    {
        $crawler = $this->client->request('GET', '/');
        $reviewLink = $crawler->filter('.review-card__title a')->first()->link();

        $this->client->click($reviewLink);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'TestCorp');
        $this->assertSelectorTextContains('.card-text', 'Korrekt kiszolgálás.');
        $this->assertSelectorTextContains('.chip', 'user12@example.com');
    }

    public function testSubmitValidReviewRedirectsAndPersists(): void
    {
        $crawler = $this->client->request('GET', '/review/new');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Küldés')->form();

        $this->client->submit($form, [
            'review[companyName]' => 'NewCo',
            'review[rating]' => '4',
            'review[reviewText]' => 'Nagyon jó cég!',
            'review[authorEmail]' => 'test@newco.com',
        ]);

        $this->assertResponseRedirects('/');
        $crawler = $this->client->followRedirect();

        $this->assertSelectorTextContains('.alert-success', 'Köszönjük a véleményed!');
        $this->assertSelectorTextContains('body', 'NewCo');
    }

    public function testRejectsSecondReviewForCompanyAndEmail(): void
    {
        $this->assertFormRejected([
            'review[companyName]' => 'Example Ltd',
            'review[authorEmail]' => 'USER1@EXAMPLE.COM',
        ]);

        $this->assertStringContainsString(
            'Ezzel az e-mail-címmel már értékelted ezt a céget.',
            (string) $this->client->getResponse()->getContent(),
        );
    }

    /** @param array<string, string> $overrides */
    private function assertFormRejected(array $overrides): void
    {
        $crawler = $this->client->request('GET', '/review/new');
        $form = $crawler->selectButton('Küldés')->form();

        $this->client->submit($form, array_merge([
            'review[companyName]' => 'NewCo',
            'review[rating]' => '4',
            'review[reviewText]' => 'Nagyon jó cég, ajánlom!',
            'review[authorEmail]' => 'test@newco.com',
        ], $overrides));

        $this->assertResponseStatusCodeSame(422);
        $this->assertFalse($this->client->getResponse()->isRedirect(), 'Rejected form must not redirect.');
    }

    public function testRejectsHtmlInCompanyName(): void
    {
        $this->assertFormRejected([
            'review[companyName]' => '<script>alert(1)</script> Evil Co',
        ]);
    }

    public function testRejectsHtmlInReviewText(): void
    {
        $this->assertFormRejected([
            'review[reviewText]' => 'Kiváló <b>szolgáltatás</b> és <img src=x onerror=alert(1)>',
        ]);
    }

    public function testRejectsInvalidEmail(): void
    {
        $this->assertFormRejected([
            'review[authorEmail]' => 'not-an-email',
        ]);
    }

    public function testRejectsRatingOutOfRange(): void
    {
        $this->assertFormRejected([
            'review[rating]' => '9',
        ]);
    }

    public function testRejectsBlankReviewText(): void
    {
        $this->assertFormRejected([
            'review[reviewText]' => '',
        ]);
    }

    public function testRejectsBlankCompanyName(): void
    {
        $this->assertFormRejected([
            'review[companyName]' => '',
        ]);
    }

    public function testCompanySearchEndpointReturnsMatchingNames(): void
    {
        $this->client->request('GET', '/companies/search?q=Test');

        $this->assertResponseIsSuccessful();
        $names = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertIsArray($names);
        $this->assertContains('TestCorp', $names);
        $this->assertNotContains('Example Ltd', $names);
    }

    public function testCompanySearchEndpointReturnsEmptyForBlankQuery(): void
    {
        $this->client->request('GET', '/companies/search?q=');

        $this->assertResponseIsSuccessful();
        $this->assertSame([], json_decode((string) $this->client->getResponse()->getContent(), true));
    }
}
