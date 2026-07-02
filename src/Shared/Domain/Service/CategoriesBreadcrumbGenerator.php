<?php

namespace App\Shared\Domain\Service;

use App\Shared\Bus\Query\KpyQueryBus;

class CategoriesBreadcrumbGenerator
{
    private array $breadcrumbs;

    private array $categoriesByProductId;

    public function __construct(
        private readonly KpyQueryBus $queryBus,
    )
    {
        $this->breadcrumbs = [];

        $this->loadCategoriesByProduct();
    }

    private function loadCategoriesByProduct(): void
    {
        $this->categoriesByProductId = array_reduce(
            $this->queryBus->fetch('kpy.shared.query.product_categories'),
            static function (array $carry, array $row) {
                $carry[$row['id_product']] = $row['categories'];
                return $carry;
            }, []);
    }

    public function getAllCategoriesBreadcrumbByProduct(int $idProduct, string $separator = '/'): array
    {
        if (!array_key_exists($idProduct, $this->categoriesByProductId)) {
            return [];
        }

        $categoriesBreadcrumb = [];
        $categories = explode(',', $this->categoriesByProductId[$idProduct]);

        foreach ($categories as $category) {
            [$idCategory, $nleft, $nright] = explode(':', $category);

            if (array_key_exists($idCategory, $this->breadcrumbs)) {
                $categoriesBreadcrumb[] = $this->breadcrumbs[$idCategory];
                continue;
            }

            $breadcrumb = array_reduce(
                $this->queryBus->fetch('kpy.query.shared.ancestor_categories', ['nleft' => $nleft, 'nright' => $nright]),
                static function (string $carry, array $row) use ($separator) {
                    return $carry . $separator . $row['category'];
                }, '');

            if (empty($breadcrumb)) {
                continue;
            }
            
            $this->breadcrumbs[$idCategory] = $breadcrumb;

            $categoriesBreadcrumb[] = $breadcrumb;
        }

        return $categoriesBreadcrumb;
    }
}
