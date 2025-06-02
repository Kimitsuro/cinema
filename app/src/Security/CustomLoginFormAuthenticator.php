<?php

namespace App\Security;

use App\Entity\Admin;
use App\Entity\Employee;
use App\Entity\Goer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class CustomLoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    private $entityManager;
    private $urlGenerator;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate('login');
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('_username');
        $password = $request->request->get('_password');
        $role = $request->request->get('role');

        // Отладка для проверки отправленных данных
        //dump($username, $password, $role);

        // Определяем, какой класс сущности использовать на основе роли
        $userClass = match ($role) {
            'goer' => Goer::class,
            'employee' => Employee::class,
            'admin' => Admin::class,
            default => throw new AuthenticationException('Invalid role'),
        };

        // Создаем UserBadge с кастомной логикой загрузки пользователя
        $userBadge = new UserBadge($username, function ($userIdentifier) use ($userClass, $role) {
            $repository = $this->entityManager->getRepository($userClass);
            $user = $repository->findOneBy([
                $role === 'goer' ? 'goerEmail' : ($role === 'employee' ? 'employeeLogin' : 'adminLogin') => $userIdentifier,
            ]);

            if (!$user) {
                throw new AuthenticationException('User not found');
            }

            return $user;
        });

        return new Passport(
            $userBadge,
            new PasswordCredentials($password)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }
}