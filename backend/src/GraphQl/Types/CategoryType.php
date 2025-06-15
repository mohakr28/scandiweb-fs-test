<?php

namespace App\GraphQL\Types;

use App\GraphQL\Resolvers\ProductResolver;
use App\GraphQL\TypesRegistry;
use GraphQL\Type\Definition\ObjectType;

class CategoryType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Category',
            'description' => 'Represents a product category',
            'fields' => fn() => [
                'name' => [
                    'type' => TypesRegistry::nonNull(TypesRegistry::string())
                ],
                'products' => [
                    'type' => TypesRegistry::listOf(TypesRegistry::product()),
                    'description' => 'Products within this category',
                    'resolve' => function ($category, $args, $context, $info) {
                        $productResolver = new ProductResolver();
                        return $productResolver->resolveByCategory($category, $args, $context, $info);
                    }
                ],
            ],
        ];
        parent::__construct($config);
    }
}