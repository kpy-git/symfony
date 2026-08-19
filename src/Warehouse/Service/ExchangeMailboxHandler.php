<?php

namespace App\Warehouse\Service;

use App\Shared\Domain\Exception\KpyException;
use App\Warehouse\Domain\ValueObject\ExchangeMail;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ExchangeMailboxHandler
{
    private ?string $token = null;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    )
    {
    }

    public function setToken(string $accessToken): void
    {
        $this->token = $accessToken;
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws KpyException
     * @throws ServerExceptionInterface
     */
    public function getUnreadMails(string $mailbox, string $folderId): array
    {
        if (!$this->token) {
            throw new KpyException("Token is not set");
        }

        $response = $this->httpClient->request(
            'GET',
            sprintf('https://graph.microsoft.com/v1.0/users/%s/mailFolders/%s/messages', $mailbox, $folderId),
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
                'query' => [
                    '$filter' => 'isRead eq false',
                    '$orderby' => 'receivedDateTime asc',
                    '$select' => 'id,subject,from,receivedDateTime,isRead,bodyPreview',
                ],
            ]
        );

        if ($response->getStatusCode() !== 200) {
            throw new KpyException($response->getStatusCode() . ": " . $response->getContent());
        }

        $data = $response->toArray();

        return empty($data)
            ? []
            : array_map(static function (array $rawMessage): ExchangeMail {
                return new ExchangeMail(
                    $rawMessage['id'],
                    $rawMessage['subject'],
                    $rawMessage['bodyPreview'],
                    \DateTimeImmutable::createFromFormat(DATE_ATOM, $rawMessage['receivedDateTime'])
                );
            }, $data['value']);
    }

    public function markAsRead(string $mailbox, string $messageId): void
    {
        if (!$this->token) {
            throw new KpyException("Token is not set");
        }

        $this->httpClient->request(
            'PATCH',
            sprintf('https://graph.microsoft.com/v1.0/users/%s/messages/%s', $mailbox, $messageId),
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'isRead' => true,
                ],
            ]
        );
    }

}
