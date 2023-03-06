<?php

namespace App\Event;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

#[AsEventListener]
final readonly class LoginFailureEventListener
{
    public function __invoke(LoginFailureEvent $event): void
    {
        $event->getRequest()->getSession()->set('target_path', $event->getRequest()->get('target_path', '/'));
    }
}