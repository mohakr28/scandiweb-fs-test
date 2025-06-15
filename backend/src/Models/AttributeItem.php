<?php

namespace App\Models;

use PDO;

class AttributeItem extends AbstractModel
{
    protected string $table = 'attribute_items';

    public function getFirstOrCreate(string $idFromJson, string $attributeSetId, string $displayValueFromJson, string $valueFromJson): array
    {
        // DB column names are: id, attribute_set_id, display_value, value.
        $stmt = $this->db->prepare(
            "SELECT id, attribute_set_id, display_value, value 
             FROM {$this->table} 
             WHERE id = :id AND attribute_set_id = :attribute_set_id"
        );
        $stmt->bindParam(':id', $idFromJson);
        $stmt->bindParam(':attribute_set_id', $attributeSetId);
        $stmt->execute();
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            $insertStmt = $this->db->prepare(
                "INSERT INTO {$this->table} (id, attribute_set_id, display_value, value) 
                 VALUES (:id_param, :attribute_set_id_param, :display_value_param, :value_param)"
            );
            $insertStmt->bindParam(':id_param', $idFromJson);
            $insertStmt->bindParam(':attribute_set_id_param', $attributeSetId);
            $insertStmt->bindParam(':display_value_param', $displayValueFromJson);
            $insertStmt->bindParam(':value_param', $valueFromJson);
            $insertStmt->execute();

            return [
                'id' => $idFromJson,
                'attribute_set_id' => $attributeSetId,
                'display_value' => $displayValueFromJson,
                'value' => $valueFromJson
            ];
        }
        return $item;
    }

    public function findByAttributeSetId(string $attributeSetId): array
    {
        // Alias `display_value` to `displayValue` to match the camelCase key expected by GraphQL.
        $stmt = $this->db->prepare(
            "SELECT id, 
                    display_value AS displayValue,
                    value 
             FROM {$this->table} 
             WHERE attribute_set_id = :attribute_set_id"
        );
        $stmt->bindParam(':attribute_set_id', $attributeSetId);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result ?: [];
    }
}