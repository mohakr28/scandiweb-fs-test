<?php

namespace App\GraphQL;

use App\GraphQL\Resolvers\CategoryResolver;
use App\GraphQL\Resolvers\ProductResolver;
use App\GraphQL\Resolvers\OrderResolver;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema as GraphQLSchema;
use GraphQL\Type\SchemaConfig;

class Schema
{
    private static ?GraphQLSchema $instance = null;

    public static function get(): GraphQLSchema
    {
        if (self::$instance === null) {
            self::$instance = self::buildSchema();
        }
        return self::$instance;
    }

    private static function buildSchema(): GraphQLSchema
    {
        $categoryResolver = new CategoryResolver();
        $productResolver = new ProductResolver();
        $orderResolver = new OrderResolver();

        $queryType = new ObjectType([
            'name' => 'Query',
            'fields' => [
                'categories' => [
                    'type' => TypesRegistry::nonNull(TypesRegistry::listOf(TypesRegistry::category())),
                    'description' => 'Get all available categories',
                    'resolve' => [$categoryResolver, 'resolveAllCategories']
                ],
                'category' => [
                    'type' => TypesRegistry::category(),
                    'description' => 'Get a single category by name, along with its products',
                    'args' => [
                        'name' => TypesRegistry::nonNull(TypesRegistry::string()),
                    ],
                    'resolve' => [$categoryResolver, 'resolveCategoryByName']
                ],
                'products' => [
                    'type' => TypesRegistry::nonNull(TypesRegistry::listOf(TypesRegistry::product())),
                    'description' => 'Get products, optionally filtered by category name (default "all")',
                    'args' => [
                        'category' => ['type' => TypesRegistry::string(), 'defaultValue' => 'all'],
                    ],
                    'resolve' => [$productResolver, 'resolveAllProducts']
                ],
                'product' => [
                    'type' => TypesRegistry::product(),
                    'description' => 'Get a single product by ID',
                    'args' => [
                        'id' => TypesRegistry::nonNull(TypesRegistry::string())
                    ],
                    'resolve' => [$productResolver, 'resolveProductById']
                ],
            ],
        ]);

        // Input types for the Order Mutation.
        $selectedAttributeInputType = new InputObjectType([
            'name' => 'SelectedAttributeInput',
            'fields' => [
                'attributeSetId' => ['type' => TypesRegistry::nonNull(TypesRegistry::string())],
                'attributeItemId' => ['type' => TypesRegistry::nonNull(TypesRegistry::string())],
                'displayValue' => ['type' => TypesRegistry::nonNull(TypesRegistry::string())],
                'value' => ['type' => TypesRegistry::nonNull(TypesRegistry::string())]
            ]
        ]);

        $orderItemInputType = new InputObjectType([
            'name' => 'OrderItemInput',
            'fields' => [
                'productId' => ['type' => TypesRegistry::nonNull(TypesRegistry::string())],
                'quantity' => ['type' => TypesRegistry::nonNull(TypesRegistry::int())],
                'selectedAttributes' => ['type' => TypesRegistry::listOf($selectedAttributeInputType)]
            ]
        ]);
        
        $orderInputType = new InputObjectType([
            'name' => 'OrderInput',
            'fields' => [
                'currencyLabel' => ['type' => TypesRegistry::nonNull(TypesRegistry::string()), 'description' => 'e.g. USD'],
                'items' => ['type' => TypesRegistry::nonNull(TypesRegistry::listOf(TypesRegistry::nonNull($orderItemInputType)))]
            ]
        ]);


        $mutationType = new ObjectType([
            'name' => 'Mutation',
            'fields' => [
                'createOrder' => [
                    'type' => TypesRegistry::order(),
                    'description' => 'Creates a new order',
                    'args' => [
                        'input' => TypesRegistry::nonNull($orderInputType)
                    ],
                    'resolve' => [$orderResolver, 'resolveCreateOrder']
                ],
            ],
        ]);
        
        // Include concrete types for interfaces to help GraphQL build the schema.
        $schemaConfig = (new SchemaConfig())
            ->setQuery($queryType)
            ->setMutation($mutationType)
            ->setTypes([
                TypesRegistry::textAttributeItem(),
                TypesRegistry::swatchAttributeItem(),
                TypesRegistry::abstractAttributeItem(),
            ]);

        return new GraphQLSchema($schemaConfig);
    }
}