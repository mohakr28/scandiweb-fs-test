<?php

namespace App\GraphQL\Types;

use App\GraphQL\TypesRegistry;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class CurrencyType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Currency',
            'description' => 'Represents a currency',
            'fields' => fn() => [
                'label' => [
                    'type' => TypesRegistry::nonNull(TypesRegistry::string()),
                    'description' => 'Currency label (e.g., USD, EUR)'
                ],
                'symbol' => [
                    'type' => TypesRegistry::nonNull(TypesRegistry::string()),
                    'description' => 'Currency symbol (e.g., $, €)'
                ],
            ],
        ];
        parent::__construct($config);
    }
}