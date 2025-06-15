<?php

namespace App\Models;

use PDO;

class Category extends AbstractModel
{
    protected string $table = 'categories';

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT id, name FROM {$this->table}");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findByName(string $name): ?array
    {
        $stmt = $this->db->prepare("SELECT id, name FROM {$this->table} WHERE name = :name");
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        return $category ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT id, name FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        return $category ?: null;
    }

    public function create(string $name): int
    {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (name) VALUES (:name)");
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        return (int)$this->db->lastInsertId();
    }
}