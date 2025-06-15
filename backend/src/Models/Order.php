<?php

namespace App\Models;

use PDO;

class Order extends AbstractModel
{
    protected string $table = 'orders';

    public function createOrder(float $totalAmount, int $currencyId, array $items): ?int
    {
        $this->db->beginTransaction();
        try {
            // Create the main order record.
            $orderSql = "INSERT INTO {$this->table} (total_amount, currency_id) VALUES (:total_amount, :currency_id)";
            $orderStmt = $this->db->prepare($orderSql);
            $orderStmt->bindParam(':total_amount', $totalAmount);
            $orderStmt->bindParam(':currency_id', $currencyId, PDO::PARAM_INT);
            $orderStmt->execute();
            $orderId = (int)$this->db->lastInsertId();

            // Insert order items.
            $itemSql = "INSERT INTO order_items (order_id, product_id, quantity, price_per_unit)
                        VALUES (:order_id, :product_id, :quantity, :price_per_unit)";
            $itemStmt = $this->db->prepare($itemSql);

            // Insert selected attributes for each item.
            $attrSql = "INSERT INTO order_item_selected_attributes
                            (order_item_id, attribute_set_id, attribute_item_id, attribute_item_display_value, attribute_item_value)
                        VALUES (:order_item_id, :attribute_set_id, :attribute_item_id, :attribute_item_display_value, :attribute_item_value)";
            $attrStmt = $this->db->prepare($attrSql);

            foreach ($items as $item) {
                $itemStmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
                $itemStmt->bindParam(':product_id', $item['productId']);
                $itemStmt->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
                $itemStmt->bindParam(':price_per_unit', $item['pricePerUnit']);
                $itemStmt->execute();
                $orderItemId = (int)$this->db->lastInsertId();

                if (!empty($item['selectedAttributes'])) {
                    foreach ($item['selectedAttributes'] as $attribute) {
                        $attrStmt->bindParam(':order_item_id', $orderItemId, PDO::PARAM_INT);
                        $attrStmt->bindParam(':attribute_set_id', $attribute['attributeSetId']);
                        $attrStmt->bindParam(':attribute_item_id', $attribute['attributeItemId']);
                        $attrStmt->bindParam(':attribute_item_display_value', $attribute['displayValue']);
                        $attrStmt->bindParam(':attribute_item_value', $attribute['value']);
                        $attrStmt->execute();
                    }
                }
            }

            $this->db->commit();
            return $orderId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Order creation failed: " . $e->getMessage());
            return null;
        }
    }
}