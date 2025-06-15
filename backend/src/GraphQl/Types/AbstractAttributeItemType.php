<?php

namespace App\GraphQL\Types;

use App\GraphQL\TypesRegistry;
use GraphQL\Type\Definition\InterfaceType;

class AbstractAttributeItemType extends InterfaceType
{
    public function __construct()
    {
        $config = [
            'name' => 'AbstractAttributeItem',
            'description' => 'Interface for different types of attribute items like text or swatch.',
            'fields' => [
                'id' => ['type' => TypesRegistry::nonNull(TypesRegistry::string())],
                'displayValue' => ['type' => TypesRegistry::nonNull(TypesRegistry::string())],
                'value' => ['type' => TypesRegistry::nonNull(TypesRegistry::string())],
            ],
            // resolveType is no longer needed here.
            // The resolver now provides a __typename field, which GraphQL uses automatically.
        ];
        parent::__construct($config);
    }
}