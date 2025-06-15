<?php

namespace App\GraphQL\Resolvers;

use App\Models\AttributeSet as AttributeSetModel;
use App\Models\AttributeItem as AttributeItemModel;

class AttributeResolver
{
    private AttributeSetModel $attributeSetModel;
    private AttributeItemModel $attributeItemModel;

    public function __construct()
    {
        $this->attributeSetModel = new AttributeSetModel();
        $this->attributeItemModel = new AttributeItemModel();
    }

    public function resolveAttributesForProduct($product, array $args, $context, $info): array
    {
        return $this->attributeSetModel->findByProductId($product['id']);
    }

    public function resolveAttributeItemsForSet($attributeSet, array $args, $context, $info): array
    {
        // The AttributeItemModel::findByAttributeSetId method returns an array
        // where each item has 'id', 'displayValue', and 'value' keys.
        $items = $this->attributeItemModel->findByAttributeSetId($attributeSet['id']);
        
        $typename = null;
        if ($attributeSet['type'] === 'swatch') {
            $typename = 'SwatchAttributeItem';
        } elseif ($attributeSet['type'] === 'text') {
            $typename = 'TextAttributeItem';
        }

        if ($typename) {
            foreach ($items as &$item) {
                // Add __typename so GraphQL's resolver can identify the concrete type.
                $item['__typename'] = $typename;
            }
            unset($item); // Break the reference after the loop.
        }

        return $items;
    }
}