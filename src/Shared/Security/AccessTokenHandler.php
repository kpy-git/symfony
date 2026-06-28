<?php

namespace App\Shared\Security;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(#[Autowire('%env(CRON_BEARER_TOKEN)%')] private string $cronToken)
    {
    }

    /**
     * @inheritDoc
     */
    public function getUserBadgeFrom(#[\SensitiveParameter] string $accessToken): UserBadge
    {
        if ($this->cronToken !== $accessToken) {
            throw new BadCredentialsException('Bad credentials');
        }

        return new UserBadge('cronjob');
    }
}
