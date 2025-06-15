<?php

namespace App\GraphQL\Types;

use App\GraphQL\Resolvers\AttributeResolver;
use App\GraphQL\TypesRegistry;
use GraphQL\Type\Definition\ObjectType;

class AttributeSetType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'AttributeSet',
            'description' => 'Represents a set of attributes for a product (e.g., Size, Color)',
            'fields' => fn() => [
                'id' => [
                    'type' => TypesRegistry::nonNull(TypesRegistry::string()),
                    'description' => 'ID of the attribute set'
                ],
                'name' => [
                    'type' => TypesRegistry::nonNull(TypesRegistry::string()),
                    'description' => 'Name of the attribute set (e.g., Size)'
                ],
                'type' => [
                    'type' => TypesRegistry::nonNull(TypesRegistry::string()),
                    'description' => 'Type of attribute set (e.g., text, swatch)'
                ],
                'items' => [
                    'type' => TypesRegistry::listOf(TypesRegistry::abstractAttributeItem()),
                    'description' => 'Available options for this attribute set',
                    'resolve' => function ($attributeSet, $args, $context, $info) {
                        $resolver = new AttributeResolver();
                        return $resolver->resolveAttributeItemsForSet($attributeSet, $args, $context, $info);
                    }
                ],
            ],
        ];
        parent::__construct($config);
    }
}