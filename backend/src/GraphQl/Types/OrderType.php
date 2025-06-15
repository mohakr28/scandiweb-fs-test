<?php

namespace App\GraphQL\Types;

use App\GraphQL\TypesRegistry;
use GraphQL\Type\Definition\ObjectType;

class OrderType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Order',
            'description' => 'Represents a placed order',
            'fields' => fn() => [
                'id' => ['type' => TypesRegistry::nonNull(TypesRegistry::id())],
                'message' => ['type' => TypesRegistry::string()], // e.g., "Order placed successfully"
                // More fields like totalAmount, items, etc., could be added if needed by the frontend.
            ],
        ];
        parent::__construct($config);
    }
}