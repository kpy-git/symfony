<?php

namespace App\Identity\Domain\Service;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

readonly class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(private UrlGeneratorInterface $router)
    {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        /*if ($token->getUser()->isNecesarySetPassword()) {
            return new RedirectResponse($this->router->generate('user_password'));
        }*/

        return new RedirectResponse($request->getSession()->get('_security.main.target_path') ?? $this->router->generate('app_index'));
    }
}
