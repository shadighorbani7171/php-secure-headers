<?php

namespace App\EventSubscriber;

use SecureHeaders\SecureHeaders;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Secure Headers Event Subscriber for Symfony
 * 
 * This subscriber is based on PHP Secure Headers by Shadi Ghorbani
 * @see https://github.com/shadighorbani7171/php-secure-headers
 */
class SecureHeadersSubscriber implements EventSubscriberInterface
{
    private SecureHeaders $headers;

    public function __construct()
    {
        $this->headers = new SecureHeaders();
        $this->headers->enableAllSecurityHeaders();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        
        foreach ($this->headers->getHeaders() as $name => $value) {
            $response->headers->set($name, $value);
        }
    }
} 