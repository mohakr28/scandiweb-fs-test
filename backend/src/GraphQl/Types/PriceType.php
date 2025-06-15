<?php

namespace App\GraphQL\Types;

use App\GraphQL\TypesRegistry;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class PriceType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Price',
            'description' => 'Represents a price with its currency and amount',
            'fields' => fn() => [
                'currency' => [
                    'type' => TypesRegistry::nonNull(TypesRegistry::currency()),
                    'resolve' => function ($priceData) {
                        // Assumes $priceData from DB has currency_label and currency_symbol.
                        return [
                            'label' => $priceData['currency_label'],
                            'symbol' => $priceData['currency_symbol']
                        ];
                    }
                ],
                'amount' => [
                    'type' => TypesRegistry::nonNull(TypesRegistry::float())
                ],
            ],
        ];
        parent::__construct($config);
    }
}