<?php

namespace App\GraphQL\Resolvers;

use App\Models\Order as OrderModel;
use App\Models\Currency as CurrencyModel;
use App\Models\Product as ProductModel;

class OrderResolver
{
    private OrderModel $orderModel;
    private CurrencyModel $currencyModel;
    private ProductModel $productModel;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->currencyModel = new CurrencyModel();
        $this->productModel = new ProductModel();
    }

    public function resolveCreateOrder($rootValue, array $args, $context, $info): ?array
    {
        $input = $args['input'];
        $orderItemsInput = $input['items']; // This should be an array of OrderItemInput
        $currencyLabel = $input['currencyLabel']; // e.g. "USD"

        $currency = $this->currencyModel->findByLabel($currencyLabel);
        if (!$currency) {
            throw new \Exception("Currency '{$currencyLabel}' not found.");
        }
        $currencyId = $currency['id'];

        $processedItems = [];
        $totalOrderAmount = 0.0;

        if (!is_array($orderItemsInput) || empty($orderItemsInput)) {
            throw new \Exception("Order items are missing or invalid.");
        }

        foreach ($orderItemsInput as $itemInput) {
            if (!isset($itemInput['productId']) || !isset($itemInput['quantity'])) {
                throw new \Exception("Invalid item input: missing productId or quantity.");
            }

            $productDetails = $this->productModel->findById($itemInput['productId']);
            if (!$productDetails) {
                throw new \Exception("Product with ID '{$itemInput['productId']}' not found.");
            }
            
            // Check stock status.
            if (!isset($productDetails['inStock']) || !$productDetails['inStock']) { 
                 throw new \Exception("Product '{$productDetails['name']}' (ID: {$itemInput['productId']}) is out of stock.");
            }

            // Get the current price for the product in the specified currency.
            $prices = $this->productModel->getPrices($itemInput['productId']);
            $currentPriceAmount = null;
            if (is_array($prices)) {
                foreach($prices as $p) {
                    if (isset($p['currency_label']) && $p['currency_label'] === $currencyLabel) {
                        $currentPriceAmount = $p['amount'];
                        break;
                    }
                }
            }
            
            if ($currentPriceAmount === null) {
                throw new \Exception("Price for product '{$productDetails['name']}' (ID: {$itemInput['productId']}) in currency '{$currencyLabel}' not found.");
            }

            // Validate that quantity is a positive integer.
            $quantity = filter_var($itemInput['quantity'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($quantity === false) {
                 throw new \Exception("Invalid quantity for product '{$productDetails['name']}' (ID: {$itemInput['productId']}). Quantity must be a positive integer.");
            }

            $processedItems[] = [
                'productId' => $itemInput['productId'],
                'quantity' => $quantity,
                'pricePerUnit' => (float)$currentPriceAmount,
                // Ensure selectedAttributes is an array, even if empty.
                'selectedAttributes' => isset($itemInput['selectedAttributes']) && is_array($itemInput['selectedAttributes']) ? $itemInput['selectedAttributes'] : [] 
            ];
            $totalOrderAmount += (float)$currentPriceAmount * $quantity;
        }
        
        if (empty($processedItems)) {
             throw new \Exception("No valid items to process for the order.");
        }

        $orderId = $this->orderModel->createOrder($totalOrderAmount, $currencyId, $processedItems);

        if ($orderId) {
            // The returned data must match the fields of OrderType in the GraphQL schema.
            return [
                'id' => (string)$orderId, // Cast to string if GraphQL ID is String! or ID!
                'message' => 'Order placed successfully!' 
                // If OrderType had more fields, they would be constructed here.
            ];
        }

        // Handle unexpected failure in the model.
        throw new \Exception('Failed to create order in the database.');
    }
}