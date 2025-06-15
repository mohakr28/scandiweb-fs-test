<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Product;
use App\Models\AttributeSet;
use App\Models\AttributeItem;

try {
    $db = Database::getInstance();
    echo "Database connection successful.\n";
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

$jsonPath = __DIR__ . '/../data.json';
if (!file_exists($jsonPath)) {
    die("Error: data.json not found at {$jsonPath}\n");
}

$jsonData = file_get_contents($jsonPath);
$data = json_decode($jsonData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("Error decoding JSON: " . json_last_error_msg() . "\n");
}

if (!isset($data['data']['categories']) || !isset($data['data']['products'])) {
    die("Error: Malformed JSON data. Missing 'categories' or 'products'.\n");
}

$categoryModel = new Category();
$currencyModel = new Currency();
$productModel = new Product();
$attributeSetModel = new AttributeSet();
$attributeItemModel = new AttributeItem();

echo "Truncating tables...\n";
$db->exec('SET FOREIGN_KEY_CHECKS = 0;');
 $db->exec('TRUNCATE TABLE order_item_selected_attributes;');
 $db->exec('TRUNCATE TABLE order_items;');
 $db->exec('TRUNCATE TABLE orders;');
 $db->exec('TRUNCATE TABLE product_attribute_sets_pivot;');
 $db->exec('TRUNCATE TABLE attribute_items;');
 $db->exec('TRUNCATE TABLE attribute_sets;');
 $db->exec('TRUNCATE TABLE prices;');
 $db->exec('TRUNCATE TABLE product_gallery_images;');
 $db->exec('TRUNCATE TABLE products;');
 $db->exec('TRUNCATE TABLE currencies;');
 $db->exec('TRUNCATE TABLE categories;');
$db->exec('SET FOREIGN_KEY_CHECKS = 1;');
echo "Tables truncated.\n";

echo "Populating categories...\n";
$categoryMap = [];
foreach ($data['data']['categories'] as $cat) {
    try {
        $existingCategory = $categoryModel->findByName($cat['name']);
        if ($existingCategory) {
            $categoryId = $existingCategory['id'];
        } else {
            $categoryId = $categoryModel->create($cat['name']);
        }
        $categoryMap[$cat['name']] = $categoryId;
    } catch (\Exception $e) {
        echo "Error creating category '{$cat['name']}': " . $e->getMessage() . "\n";
    }
}
echo "Categories populated.\n";

echo "Populating currencies...\n";
$currencyMap = [];
$usd = $currencyModel->getFirstOrCreate('USD', '$');
$currencyMap['USD'] = $usd['id'];
echo "Currencies populated.\n";

echo "Populating products...\n";
foreach ($data['data']['products'] as $prod) {
    echo "Processing product: {$prod['name']} (ID: {$prod['id']})\n";
    $productData = [
        'id'          => $prod['id'],
        'name'        => $prod['name'],
        // If 'inStock' is missing for a product, default to false.
        'in_stock'    => $prod['inStock'] ?? false, 
        'description' => $prod['description'] ?? '',
        'category_id' => $categoryMap[$prod['category']] ?? null,
        'brand'       => $prod['brand'] ?? ''
    ];

    try {
        $productModel->create($productData);
        echo "  Created product: {$prod['name']}\n";

        if (!empty($prod['gallery'])) {
            foreach ($prod['gallery'] as $index => $imageUrl) {
                $productModel->addGalleryImage($prod['id'], $imageUrl, $index);
            }
        }

        if (!empty($prod['prices'])) {
            foreach ($prod['prices'] as $price) {
                $currencyLabel = $price['currency']['label'] ?? 'USD';
                $currencyId = $currencyMap[$currencyLabel] ?? null;
                if ($currencyId) {
                    $productModel->addPrice($prod['id'], $currencyId, (float)$price['amount']);
                }
            }
        }

        if (!empty($prod['attributes'])) {
            foreach ($prod['attributes'] as $attrSetData) {
                $attributeSet = $attributeSetModel->getFirstOrCreate(
                    $attrSetData['id'],
                    $attrSetData['name'],
                    $attrSetData['type']
                );
                $attributeSetModel->linkProductToAttributeSet($prod['id'], $attributeSet['id']);

                if (!empty($attrSetData['items'])) {
                    foreach ($attrSetData['items'] as $attrItemData) {
                        // Make sure displayValue, value, and id exist in the source data.
                        $itemDisplayValue = $attrItemData['displayValue'] ?? 'N/A_from_populate';
                        $itemValue = $attrItemData['value'] ?? 'val_from_populate';
                        $itemId = $attrItemData['id'] ?? ('temp_id_' . uniqid());

                        if ($itemDisplayValue === 'N/A_from_populate' || $itemValue === 'val_from_populate' || strpos($itemId, 'temp_id_') === 0) {
                            echo "    WARNING: Missing data for attribute item in data.json for product '{$prod['name']}', attribute set '{$attrSetData['name']}': " . print_r($attrItemData, true) . "\n";
                        }

                        $attributeItemModel->getFirstOrCreate(
                            $itemId,
                            $attributeSet['id'],
                            $itemDisplayValue,
                            $itemValue
                        );
                    }
                }
            }
        }

    } catch (\Exception $e) {
        echo "  Error processing product '{$prod['name']}' (ID: {$prod['id']}): " . $e->getMessage() . "\n";
        echo "  Trace: " . $e->getTraceAsString() . "\n";
    }
}
echo "Products populated.\n";
echo "Data population script finished.\n";
?>