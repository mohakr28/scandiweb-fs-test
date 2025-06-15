<?php

namespace App\GraphQL\Types;

use App\GraphQL\TypesRegistry;
use GraphQL\Type\Definition\ObjectType;

class TextAttributeItemType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'TextAttributeItem',
            'description' => 'Represents a text-based attribute item (e.g., S, M, L).',
            'fields' => fn() => [ 
                'id' => ['type' => TypesRegistry::nonNull(TypesRegistry::string())],
                'displayValue' => ['type' => TypesRegistry::nonNull(TypesRegistry::string())],
                'value' => ['type' => TypesRegistry::nonNull(TypesRegistry::string())],
            ],
            'interfaces' => [ 
                TypesRegistry::abstractAttributeItem()
            ]
        ];
        parent::__construct($config);
    }
}