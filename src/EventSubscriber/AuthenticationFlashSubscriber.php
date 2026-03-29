<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AuthenticationFlashSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => ['onLoginSuccess', 0],
            LoginFailureEvent::class => ['onLoginFailure', 0],
            LogoutEvent::class => ['onLogoutRedirect', 0],
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if ($user instanceof User) {
            $name = $user->getFullName() ?: $user->getUserIdentifier();
            $event->getRequest()->getSession()->getFlashBag()->add('success', sprintf('Welcome back, %s.', $name));

            return;
        }

        $event->getRequest()->getSession()->getFlashBag()->add('success', 'You are now signed in.');
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->hasSession()) {
            return;
        }

        $exception = $event->getException();
        if ($exception instanceof AuthenticationException) {
            $message = $this->translator->trans(
                $exception->getMessageKey(),
                $exception->getMessageData(),
                'security'
            );
        } else {
            $message = $exception->getMessage();
        }

        if ($message === '') {
            $message = 'Invalid credentials. Please try again.';
        }

        $request->getSession()->getFlashBag()->add('danger', $message);
    }

    /**
     * Redirect so HomeController can add a flash on a fresh session (logout invalidates the old session).
     */
    public function onLogoutRedirect(LogoutEvent $event): void
    {
        $event->setResponse(new RedirectResponse(
            $this->urlGenerator->generate('app_home', ['signed_out' => '1'])
        ));
    }
}
