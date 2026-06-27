<?php

namespace App\Shared\Domain\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class JsonResponseGenerator
{
    public function success(array $data = [], int $status = Response::HTTP_OK): JsonResponse
    {
        return new JsonResponse([
            'status' => $status,
            'timestamp' => time(),
            ...$data,
        ]);
    }

    public function error(string $message, int $status = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        return new JsonResponse([
            'status' => $status,
            'timestamp' => time(),
            'message' => $message,
        ]);
    }

    public function fromException(\Exception $exception, int $status = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        return $this->error($exception->getMessage(), $status);
    }
}
