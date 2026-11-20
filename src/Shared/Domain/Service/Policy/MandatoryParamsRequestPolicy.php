<?php

namespace App\Shared\Domain\Service\Policy;

use App\Shared\Domain\Exception\KpyMissingMandatoryParamsPolicyException;
use App\Shared\Domain\Service\Policy\Context\RequestPolicyContext;
use Symfony\Component\HttpFoundation\Response;

class MandatoryParamsRequestPolicy extends RequestPolicy
{
    private array $missingMandatoryParams;

    public function __construct()
    {
        $this->missingMandatoryParams = [];
    }

    /**
     * @throws KpyMissingMandatoryParamsPolicyException
     */
    public function apply(object $context): void
    {
        if (!$context instanceof RequestPolicyContext) {
            throw new \InvalidArgumentException('Invalid policy context');
        }

        try {
            $requestParams = $context->request->getContentTypeFormat() === 'json'
                ? array_keys(json_decode($context->request->getContent(), true, 512, JSON_THROW_ON_ERROR))
                : $context->request->request->keys();

            $this->missingMandatoryParams = array_diff($context->options[parent::MANDATORY_PARAMS], $requestParams);

            if (!empty($this->missingMandatoryParams)) {
                throw new KpyMissingMandatoryParamsPolicyException(
                    sprintf("No se puede realizar la petición, faltan parámetros obligatorios: [%s]", $this->getMissingMandatoryParamsAsString()),
                    Response::HTTP_BAD_REQUEST);
            }

        } catch (\JsonException $exception) {
            throw new KpyMissingMandatoryParamsPolicyException($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function getMissingMandatoryParamsAsString(string $separator = ','): string
    {
        return implode($separator, $this->missingMandatoryParams);
    }
}
