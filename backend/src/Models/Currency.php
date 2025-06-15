<?php

namespace App\Models;

use PDO;

class Currency extends AbstractModel
{
    protected string $table = 'currencies';

    public function findByLabel(string $label): ?array
    {
        $stmt = $this->db->prepare("SELECT id, label, symbol FROM {$this->table} WHERE label = :label");
        $stmt->bindParam(':label', $label);
        $stmt->execute();
        $currency = $stmt->fetch(PDO::FETCH_ASSOC);
        return $currency ?: null;
    }

    public function findById(int $id): ?array
    {
         $stmt = $this->db->prepare("SELECT id, label, symbol FROM {$this->table} WHERE id = :id");
         $stmt->bindParam(':id', $id, PDO::PARAM_INT);
         $stmt->execute();
         $currency = $stmt->fetch(PDO::FETCH_ASSOC);
         return $currency ?: null;
    }

    public function create(string $label, string $symbol): int
    {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (label, symbol) VALUES (:label, :symbol)");
        $stmt->bindParam(':label', $label);
        $stmt->bindParam(':symbol', $symbol);
        $stmt->execute();
        return (int)$this->db->lastInsertId();
    }

    public function getFirstOrCreate(string $label, string $symbol): array
    {
        $currency = $this->findByLabel($label);
        if (!$currency) {
            $id = $this->create($label, $symbol);
            return ['id' => $id, 'label' => $label, 'symbol' => $symbol];
        }
        return $currency;
    }
}