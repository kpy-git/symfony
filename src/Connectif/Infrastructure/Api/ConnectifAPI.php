<?php

namespace App\Connectif\Infrastructure\Api;

use App\Connectif\Product;
use App\Connectif\Purchase;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ConnectifAPI
{
    private string $url;

    private string $requestBody;

    private string $response;

    private \CurlHandle $curl;

    private array $responseHeader = [];

    private int $rateLimitRemaining = 10;

    private int $rateLimitReset = 0;

    private string $statusCode;

    public function __construct(
        #[Autowire('%env(CONNECTIF_API_KEY)%')] private readonly string       $apiKey,
        #[Autowire('%env(CONNECTIF_PRODUCTS_URL)%')] private readonly string  $productsUrl,
        #[Autowire('%env(CONNECTIF_CONTACTS_URL)%')] private readonly string  $contactsUrl,
        #[Autowire('%env(CONNECTIF_PURCHASES_URL)%')] private readonly string $purchasesUrl,
    )
    {
    }

    public function updateContact(string $email, array $body): bool
    {
        $this->url = $this->contactsUrl . $email;
        $this->encondeBodyForRequest($body);

        return $this->executeRequest();
    }

    public function deleteContact(string $email): bool
    {
        $this->url = $this->contactsUrl . $email;

        return $this->executeRequest("DELETE");
    }

    public function updateProduct(Product $product): bool
    {
        $this->url = $this->productsUrl . $product->getSku();

        $this->requestBody = json_encode($product, JSON_THROW_ON_ERROR);

        return $this->executeRequest();
    }

    public function createPurchase(Purchase $purchase): bool
    {
        $this->url = $this->purchasesUrl;

        $this->requestBody = json_encode($purchase, JSON_THROW_ON_ERROR);

        return $this->executeRequest("POST");
    }

    private function encondeBodyForRequest(array $body): void
    {
        $this->requestBody = json_encode($body);
    }

    private function executeRequest(string $method = "PATCH"): bool
    {
        if ($this->rateLimitRemaining < 1) {
            // echo "Esperando para no sobrepasar el límite de peticiones por segundo\n";
            sleep($this->rateLimitReset + 1);
        }

        $this->curl = curl_init();
        $this->setCurlOptions($method);

        $body = curl_exec($this->curl);
        $headerSize = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);

        $this->parseResponseHeader(substr($body, 0, $headerSize));
        $this->response = substr($body, $headerSize);

        return !empty($this->response);
    }

    private function parseResponseHeader(string $header): void
    {
        $this->responseHeader = [];
        $pairs = explode("\r\n", $header);
        // el primer elemento no nos interesa: HTTP1.1 200 ...
        $this->statusCode = array_shift($pairs);

        foreach ($pairs as $pair) {
            if (!str_contains($pair, ':')) {
                continue;
            }
            [$key, $value] = explode(':', $pair);
            $this->responseHeader[$key] = trim($value);
        }

        if (isset($this->responseHeader['RateLimit-Remaining'])) {
            $this->rateLimitRemaining = (int)$this->responseHeader['RateLimit-Remaining'];
            $this->rateLimitReset = (int)$this->responseHeader['RateLimit-Reset'];
        }
    }

    private function setCurlOptions(string $method): void
    {
        curl_setopt($this->curl, CURLOPT_URL, $this->url);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

        $header = ['Authorization: apiKey ' . $this->apiKey];

        if ($method !== "DELETE") {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->requestBody);
            $header[] = 'Content-Type: application/json';
        }

        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($this->curl, CURLOPT_HEADER, true);
    }

    public function getRequestsDetails(): array
    {
        return [
            'url' => $this->url,
            'request' => $this->requestBody,
            'response_header' => $this->responseHeader,
            'response' => $this->response,
            'rate_limit_remaining' => $this->rateLimitRemaining,
            'rate_limit_reset' => $this->rateLimitReset,
        ];
    }

    public function getResponse(): string
    {
        return $this->response;
    }
}
