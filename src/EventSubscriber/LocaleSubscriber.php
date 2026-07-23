<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class LocaleSubscriber implements EventSubscriberInterface
{
    /**
     * @param array<int, string> $allowedLocales
     */
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $defaultLocale,
        private readonly array $allowedLocales = ['hu', 'en'],
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->hasSession() ? $request->getSession() : null;

        $switchLocale = $request->query->get('lang');
        if (\is_string($switchLocale) && \in_array($switchLocale, $this->allowedLocales, true)) {
            $session?->set('_locale', $switchLocale);
            $request->setLocale($switchLocale);

            $this->redirectToCleanUrl($event, $request, $switchLocale);

            return;
        }

        $remembered = $session?->get('_locale');
        if (\is_string($remembered) && \in_array($remembered, $this->allowedLocales, true)) {
            $request->setLocale($remembered);

            return;
        }

        $request->setLocale($this->defaultLocale);
    }

    private function redirectToCleanUrl(RequestEvent $event, Request $request, string $locale): void
    {
        $route = $request->attributes->get('_route');
        if (!\is_string($route)) {
            return;
        }

        $params = array_merge(
            (array) $request->attributes->get('_route_params', []),
            $request->query->all(),
        );
        unset($params['lang']);

        $event->setResponse(new RedirectResponse(
            $this->urlGenerator->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_PATH),
            302,
            ['Vary' => 'Accept-Language'],
        ));
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 15]];
    }
}
