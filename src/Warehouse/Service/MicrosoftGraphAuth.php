<?php

namespace App\Warehouse\Service;

use App\Shared\Domain\Exception\KpyException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class MicrosoftGraphAuth
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[Autowire('%env(TENANT_ID_INTEGRACIONES_PARSER)%')]
        private string $tenantId,
        #[Autowire('%env(CLIENT_ID_INTEGRACIONES_PARSER)%')]
        private string $clientId,
        #[Autowire('%env(SECRET_INTEGRACIONES_PARSER)%')]
        private string $clientSecret,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws KpyException
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getAccessToken(): string
    {
        $response = $this->httpClient->request(
            'POST',
            sprintf(
                'https://login.microsoftonline.com/%s/oauth2/v2.0/token',
                $this->tenantId
            ),
            [
                'body' => [
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope'         => 'https://graph.microsoft.com/.default',
                    'grant_type'    => 'client_credentials',
                ],
            ]
        );

        $data = $response->toArray();

        if (!isset($data['access_token'])) {
            throw new KpyException('No se pudo obtener el token de Graph');
        }

        return $data['access_token'];
    }
}
