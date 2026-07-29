<?php

namespace App\Shared\Domain\Service;

use App\Shared\Domain\Exception\KpyException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class OrderStatusUpdater
{
    public function __construct(
        #[Autowire('%env(SHOP_URL)%')]
        private string              $host,
        #[Autowire('%env(ORDER_DISPATCHER_SECRET)%')]
        private string              $secret,
        private HttpClientInterface $client
    )
    {
    }

    /**
     * @throws KpyException
     */
    public function setCurrentState(int $orderId, int $state, int $employee = 0, ?\DateTimeImmutable $date = null): void
    {

        try {
            $expires = strtotime('+60 seconds');

            $params = [
                'id_order' => $orderId,
                'order_status' => $state,
                'expires' => $expires,
                'token' => $this->getToken($orderId, $state, $expires),
            ];

            if ($date) {
                $params['timestamp'] = $date->getTimestamp();
            }

            if ($employee > 0) {
                $params['employee'] = $employee;
            }

            $response = $this->client->request('GET', "{$this->host}/module/kpyorderdispatcher/updateorderstatus", [
                'query' => $params,
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new KpyException(sprintf('No se ha podido realizar la petición [status_code: %d]', $response->getStatusCode()));
            }

        } catch (TransportExceptionInterface|ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $ex) {
            throw new KpyException($ex->getMessage());
        }
    }

    private function getToken(int $orderId, int $status, int $expires): string
    {
        $dataToSign = sprintf('id_order=%d&order_status=%d&expires=%d', $orderId, $status, $expires);
        return hash_hmac('sha256', $dataToSign, $this->secret);
    }
}
