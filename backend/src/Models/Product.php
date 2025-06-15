<?php

namespace App\Models;

use PDO;

class Product extends AbstractModel
{
    protected string $table = 'products';

    private function processProductDataAfterFetch(array $productData): array
    {
        // Convert `in_stock` from the DB (0 or 1) to a boolean `inStock`
        // for the GraphQL response.
        if (array_key_exists('in_stock', $productData)) {
            $productData['inStock'] = (bool)$productData['in_stock'];
            if ('in_stock' !== 'inStock') {
                 unset($productData['in_stock']);
            }
        } else {
            // Default to false if the key is missing for some reason.
            $productData['inStock'] = false;
        }
        return $productData;
    }

    public function findById(string $id): ?array
    {
        $query = "SELECT p.id, p.name, p.in_stock, p.description, p.brand, c.name as category_name
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $product ? $this->processProductDataAfterFetch($product) : null;
    }

    public function findByCategoryName(string $categoryName): array
    {
        $baseSelect = "SELECT p.id, p.name, p.in_stock, p.brand"; 

        if (strtolower($categoryName) === 'all') {
             $query = "{$baseSelect} FROM {$this->table} p";
             $stmt = $this->db->prepare($query);
        } else {
            $query = "{$baseSelect}, c.name as category_name 
                      FROM {$this->table} p
                      JOIN categories c ON p.category_id = c.id
                      WHERE c.name = :category_name";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':category_name', $categoryName);
        }
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$products) {
            return [];
        }

        return array_map([$this, 'processProductDataAfterFetch'], $products);
    }

    public function create(array $data): string
    {
        $sql = "INSERT INTO {$this->table} (id, name, in_stock, description, category_id, brand)
                VALUES (:id, :name, :in_stock, :description, :category_id, :brand)";
        $stmt = $this->db->prepare($sql);

        // Convert boolean `in_stock` to integer (0 or 1) for the DB.
        $inStockForDb = 0; 
        if (isset($data['in_stock'])) {
            if (is_string($data['in_stock'])) {
                if (strtolower($data['in_stock']) === 'true') {
                    $inStockForDb = 1;
                } else {
                    $inStockForDb = 0;
                }
            } else {
                 $inStockForDb = (int)(bool)$data['in_stock'];
            }
        }
        
        $stmt->bindParam(':id', $data['id']);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':in_stock', $inStockForDb, PDO::PARAM_INT); 
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':category_id', $data['category_id'], PDO::PARAM_INT);
        $stmt->bindParam(':brand', $data['brand']);
        $stmt->execute();
        return $data['id'];
    }

    public function getGalleryImages(string $productId): array
    {
        $stmt = $this->db->prepare("SELECT image_url FROM product_gallery_images WHERE product_id = :product_id ORDER BY sort_order ASC");
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    public function getPrices(string $productId): array
    {
        $query = "SELECT pr.amount, cu.label as currency_label, cu.symbol as currency_symbol
                  FROM prices pr
                  JOIN currencies cu ON pr.currency_id = cu.id
                  WHERE pr.product_id = :product_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    public function addGalleryImage(string $productId, string $imageUrl, int $order): void
    {
        $stmt = $this->db->prepare("INSERT INTO product_gallery_images (product_id, image_url, sort_order) VALUES (:product_id, :image_url, :sort_order)");
        $stmt->bindParam(':product_id', $productId);
        $stmt->bindParam(':image_url', $imageUrl);
        $stmt->bindParam(':sort_order', $order, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function addPrice(string $productId, int $currencyId, float $amount): void
    {
        $stmt = $this->db->prepare("INSERT INTO prices (product_id, currency_id, amount) VALUES (:product_id, :currency_id, :amount)");
        $stmt->bindParam(':product_id', $productId);
        $stmt->bindParam(':currency_id', $currencyId, PDO::PARAM_INT);
        $stmt->bindParam(':amount', $amount);
        $stmt->execute();
    }
}