<?php

namespace App\EventSubscriber;

use SecureHeaders\SecureHeaders;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SecureHeadersSubscriber implements EventSubscriberInterface
{
    private SecureHeaders $headers;

    public function __construct()
    {
        $this->headers = new SecureHeaders();
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        // Enable all security headers
        $this->headers->enableAllSecurityHeaders();

        // Apply headers to response
        $response = $event->getResponse();
        foreach ($this->headers->getHeaders() as $name => $value) {
            $response->headers->set($name, $value);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', 0],
        ];
    }
} 