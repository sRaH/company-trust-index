<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class LocaleSubscriberTest extends WebTestCase
{
    public function testDefaultLocaleIsHungarian(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('nav', 'Cégek');
        $this->assertSelectorTextContains('nav', 'Új vélemény');
    }

    public function testLangQuerySwitchesLocaleAndRedirectsCleanly(): void
    {
        $client = static::createClient();
        $client->request('GET', '/?lang=en');

        $this->assertResponseRedirects('/');
        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('nav', 'Company statistics');
        $this->assertSelectorTextContains('nav', 'Write a review');
    }

    public function testSessionPersistsEnglishLocaleAcrossRequests(): void
    {
        $client = static::createClient();

        $client->request('GET', '/?lang=en');
        $client->followRedirect();

        $client->request('GET', '/review/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Write a review');
    }

    public function testInvalidLocaleFallsBackToDefault(): void
    {
        $client = static::createClient();
        $client->request('GET', '/?lang=de');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('nav', 'Cégek');
    }
}
