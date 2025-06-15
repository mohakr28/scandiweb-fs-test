<?php

namespace App\Models;

use PDO;

class AttributeSet extends AbstractModel
{
    protected string $table = 'attribute_sets';

    public function getFirstOrCreate(string $id, string $name, string $type): array
    {
        $stmt = $this->db->prepare("SELECT id, name, type FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $attributeSet = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$attributeSet) {
            $insertStmt = $this->db->prepare("INSERT INTO {$this->table} (id, name, type) VALUES (:id, :name, :type)");
            $insertStmt->bindParam(':id', $id);
            $insertStmt->bindParam(':name', $name);
            $insertStmt->bindParam(':type', $type);
            $insertStmt->execute();
            return ['id' => $id, 'name' => $name, 'type' => $type];
        }
        return $attributeSet;
    }

    public function findByProductId(string $productId): array
    {
        $query = "SELECT aset.id, aset.name, aset.type
                  FROM {$this->table} aset
                  JOIN product_attribute_sets_pivot pasp ON aset.id = pasp.attribute_set_id
                  WHERE pasp.product_id = :product_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function linkProductToAttributeSet(string $productId, string $attributeSetId): void
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO product_attribute_sets_pivot (product_id, attribute_set_id) VALUES (:product_id, :attribute_set_id)");
            $stmt->bindParam(':product_id', $productId);
            $stmt->bindParam(':attribute_set_id', $attributeSetId);
            $stmt->execute();
        } catch (\PDOException $e) {
            // Ignore duplicate entry errors if the script is run multiple times.
            if ($e->getCode() != 23000) {
                throw $e; // Re-throw other errors.
            }
        }
    }
}