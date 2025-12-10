<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class JWTApiAccessSubscriber implements EventSubscriberInterface
{
    public function checkApiAccess(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();

        if (!in_array('ROLE_API_ACCESS', $user->getRoles(), true)) {
            throw new AccessDeniedHttpException('Accès API non activé');
        }
    }
    public static function getSubscribedEvents(): array
    {
        return [
            Events::JWT_CREATED => 'checkApiAccess',
        ];
    }
}
