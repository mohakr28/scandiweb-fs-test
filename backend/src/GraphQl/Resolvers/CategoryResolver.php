<?php

namespace App\GraphQL\Resolvers;

use App\Models\Category as CategoryModel;

class CategoryResolver
{
    private CategoryModel $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
    }

    public function resolveAllCategories($rootValue, array $args, $context, $info): array
    {
        return $this->categoryModel->getAll();
    }

    public function resolveCategoryByName($rootValue, array $args, $context, $info): ?array
    {
        $name = $args['name'] ?? null;
        if (!$name) {
            return null;
        }
        return $this->categoryModel->findByName($name);
    }
}