<?php

namespace App\Google\Domain\Query;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ProductWithAlternativeSkusQuery implements QueryInterface
{
    public function __construct(#[Autowire('%kpy.google_dir%')] private string $path)
    {
    }

    public function getName(): string
    {
        return 'kpy.query.google.products_with_alternative_skus';
    }

    public function fetch(array $params = []): mixed
    {
        $filename = $this->path . '/skus_with_code_alternative.json';
        return is_readable($filename)
            ? json_decode(file_get_contents($filename), true)
            : [];
    }
}
