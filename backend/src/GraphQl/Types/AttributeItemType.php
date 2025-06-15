<?php

namespace App\GraphQL\Types;

use App\GraphQL\TypesRegistry;
use GraphQL\Type\Definition\ObjectType;

// This class can serve as a base or example, but the application now uses an interface
// (AbstractAttributeItemType) with concrete implementations (Text/Swatch).
class AttributeItemType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'AttributeItem',
            'description' => 'Represents an individual attribute option (e.g., S, M, Red, Blue)',
            'fields' => fn() => [
                'id' => [
                    'type' => TypesRegistry::nonNull(TypesRegistry::string()),
                    'description' => 'ID of the attribute item'
                ],
                'displayValue' => [
                    'type' => TypesRegistry::nonNull(TypesRegistry::string()),
                    'description' => 'Display value of the attribute (e.g., Small, Green)'
                ],
                'value' => [
                    'type' => TypesRegistry::nonNull(TypesRegistry::string()),
                    'description' => 'Actual value of the attribute (e.g., S, #44FF03)'
                ],
            ]
        ];
        parent::__construct($config);
    }
}