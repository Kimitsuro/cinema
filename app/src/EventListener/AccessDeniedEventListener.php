<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AccessDeniedEventListener
{
    private $tokenStorage;
    private $authorizationChecker;
    private $urlGenerator;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->urlGenerator = $urlGenerator;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof AccessDeniedHttpException) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        $user = $token ? $token->getUser() : null;

        if (!$user) {
            $response = new RedirectResponse($this->urlGenerator->generate('app_login'));
            $event->setResponse($response);
            return;
        }

        $redirectRoute = 'app_home';

        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $redirectRoute = 'app_admin';
        } elseif ($this->authorizationChecker->isGranted('ROLE_GOER')) {
            $redirectRoute = 'goer_account';
        } elseif ($this->authorizationChecker->isGranted('ROLE_EMPLOYEE')) {
            $redirectRoute = 'app_employee';
        }

        $response = new RedirectResponse($this->urlGenerator->generate($redirectRoute));
        $event->setResponse($response);
    }
}