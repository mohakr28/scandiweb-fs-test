<?php

namespace App\GraphQL\Types;

use App\GraphQL\Resolvers\AttributeResolver;
use App\GraphQL\Resolvers\ProductResolver;
use App\GraphQL\TypesRegistry;
use GraphQL\Type\Definition\ObjectType;

class ProductType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Product',
            'description' => 'Represents a product',
            'fields' => fn() => [
                'id' => ['type' => TypesRegistry::nonNull(TypesRegistry::string())],
                'name' => ['type' => TypesRegistry::nonNull(TypesRegistry::string())],
                'inStock' => ['type' => TypesRegistry::boolean()],
                'gallery' => [
                    'type' => TypesRegistry::listOf(TypesRegistry::string()),
                    'resolve' => function($product, $args, $context, $info) {
                        $resolver = new ProductResolver();
                        return $resolver->resolveGallery($product, $args, $context, $info);
                    }
                ],
                'description' => ['type' => TypesRegistry::nonNull(TypesRegistry::string())],
                'category' => [ // Resolves to the category name string.
                    'type' => TypesRegistry::nonNull(TypesRegistry::string()),
                    'resolve' => function($product, $args, $context, $info) {
                        $resolver = new ProductResolver();
                        return $resolver->resolveCategoryName($product, $args, $context, $info);
                    }
                ],
                'attributes' => [
                    'type' => TypesRegistry::listOf(TypesRegistry::attributeSet()),
                    'description' => 'Configurable attributes of the product',
                    'resolve' => function($product, $args, $context, $info) {
                        $resolver = new AttributeResolver();
                        return $resolver->resolveAttributesForProduct($product, $args, $context, $info);
                    }
                ],
                'prices' => [
                    'type' => TypesRegistry::nonNull(TypesRegistry::listOf(TypesRegistry::price())),
                    'resolve' => function($product, $args, $context, $info) {
                        $resolver = new ProductResolver();
                        return $resolver->resolvePrices($product, $args, $context, $info);
                    }
                ],
                'brand' => ['type' => TypesRegistry::string()],
            ],
        ];
        parent::__construct($config);
    }
}