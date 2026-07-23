<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\EventSubscriber\LocaleSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class LocaleSubscriberTest extends TestCase
{
    public function testSubRequestIsIgnored(): void
    {
        $subscriber = new LocaleSubscriber(
            $this->createStub(UrlGeneratorInterface::class),
            'hu',
        );
        $request = Request::create('/?lang=en');
        $event = new RequestEvent(
            $this->createStub(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::SUB_REQUEST,
        );

        $subscriber->onKernelRequest($event);

        $this->assertFalse($event->hasResponse());
        $this->assertSame('en', $request->getLocale());
    }

    public function testLocaleSwitchWithoutRouteDoesNotRedirect(): void
    {
        $subscriber = new LocaleSubscriber(
            $this->createStub(UrlGeneratorInterface::class),
            'hu',
        );
        $request = Request::create('/?lang=en');
        $request->setSession(new Session(new MockArraySessionStorage()));
        $event = new RequestEvent(
            $this->createStub(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        );

        $subscriber->onKernelRequest($event);

        $this->assertFalse($event->hasResponse());
        $this->assertSame('en', $request->getLocale());
        $this->assertSame('en', $request->getSession()->get('_locale'));
    }

    public function testSubscribedEventsRegisterTheRequestListener(): void
    {
        $this->assertSame(
            [KernelEvents::REQUEST => ['onKernelRequest', 15]],
            LocaleSubscriber::getSubscribedEvents(),
        );
    }
}
