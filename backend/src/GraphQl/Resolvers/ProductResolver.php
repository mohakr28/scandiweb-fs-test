<?php

namespace App\GraphQL\Resolvers;

use App\Models\Product as ProductModel;

class ProductResolver
{
    private ProductModel $productModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
    }

    public function resolveByCategory($category, array $args, $context, $info): array
    {
        // $category is the parent resolved Category object (array from DB).
        $categoryName = $category['name'] ?? 'all'; 
        return $this->productModel->findByCategoryName($categoryName);
    }
    
    public function resolveAllProducts($rootValue, array $args, $context, $info): array
    {
        $categoryName = $args['category'] ?? 'all';
        return $this->productModel->findByCategoryName($categoryName);
    }

    public function resolveProductById($rootValue, array $args, $context, $info): ?array
    {
        $id = $args['id'] ?? null;
        if (!$id) {
            return null;
        }
        return $this->productModel->findById($id);
    }

    public function resolveGallery($product, array $args, $context, $info): array
    {
        // $product is the parent resolved Product object.
        return $this->productModel->getGalleryImages($product['id']);
    }

    public function resolvePrices($product, array $args, $context, $info): array
    {
        return $this->productModel->getPrices($product['id']);
    }

    public function resolveCategoryName($product, array $args, $context, $info): string
    {
        // $product should have 'category_name' from the DB join.
        return $product['category_name'] ?? 'unknown';
    }
}