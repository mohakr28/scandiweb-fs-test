<?php

namespace App\GraphQL\Types;

use App\GraphQL\TypesRegistry;
use GraphQL\Type\Definition\ObjectType;

class SwatchAttributeItemType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'SwatchAttributeItem',
            'description' => 'Represents an attribute item that is a color swatch.',
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