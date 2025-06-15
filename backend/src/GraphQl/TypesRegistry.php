<?php

namespace App\GraphQL;

use App\GraphQL\Types\CategoryType;
use App\GraphQL\Types\ProductType;
use App\GraphQL\Types\CurrencyType;
use App\GraphQL\Types\PriceType;
use App\GraphQL\Types\AttributeSetType;
use App\GraphQL\Types\AttributeItemType;
use App\GraphQL\Types\TextAttributeItemType;
use App\GraphQL\Types\SwatchAttributeItemType;
use App\GraphQL\Types\AbstractAttributeItemType;
use App\GraphQL\Types\OrderType;
use GraphQL\Type\Definition\Type;

class TypesRegistry
{
    private static array $types = [];

    public static function get(string $className): Type
    {
        if (!isset(self::$types[$className])) {
            self::$types[$className] = new $className();
        }
        return self::$types[$className];
    }

    public static function category(): CategoryType
    {
        return self::get(CategoryType::class);
    }

    public static function product(): ProductType
    {
        return self::get(ProductType::class);
    }

    public static function currency(): CurrencyType
    {
        return self::get(CurrencyType::class);
    }

    public static function price(): PriceType
    {
        return self::get(PriceType::class);
    }

    public static function attributeSet(): AttributeSetType
    {
        return self::get(AttributeSetType::class);
    }
    
    public static function attributeItem(): AttributeItemType
    {
         return self::get(AttributeItemType::class);
    }

    public static function textAttributeItem(): TextAttributeItemType
    {
        return self::get(TextAttributeItemType::class);
    }

    public static function swatchAttributeItem(): SwatchAttributeItemType
    {
        return self::get(SwatchAttributeItemType::class);
    }
    
    // This interface resolves to either TextAttributeItemType or SwatchAttributeItemType.
    public static function abstractAttributeItem(): AbstractAttributeItemType
    {
        return self::get(AbstractAttributeItemType::class);
    }

    public static function order(): OrderType
    {
        return self::get(OrderType::class);
    }

    // Standard GraphQL type helpers
    public static function string(): Type { return Type::string(); }
    public static function int(): Type { return Type::int(); }
    public static function float(): Type { return Type::float(); }
    public static function boolean(): Type { return Type::boolean(); }
    public static function id(): Type { return Type::id(); }
    public static function listOf(Type $type): Type { return Type::listOf($type); }
    public static function nonNull(Type $type): Type { return Type::nonNull($type); }
}