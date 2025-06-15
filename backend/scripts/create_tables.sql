-- backend/scripts/create_tables.sql

-- Ensure you are using the correct database before running this script.
-- USE scandiweb_test;

SET FOREIGN_KEY_CHECKS = 0; -- Temporarily disable FK checks for easier table recreation.

-- Drop existing tables for a clean slate (useful during development).
DROP TABLE IF EXISTS order_item_selected_attributes;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS product_attribute_sets_pivot;
DROP TABLE IF EXISTS attribute_items;
DROP TABLE IF EXISTS attribute_sets;
DROP TABLE IF EXISTS prices;
DROP TABLE IF EXISTS product_gallery_images;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS currencies;
DROP TABLE IF EXISTS categories;

SET FOREIGN_KEY_CHECKS = 1; -- Re-enable FK checks.

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Currencies table
CREATE TABLE IF NOT EXISTS currencies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(10) NOT NULL UNIQUE, -- e.g., USD, EUR
    symbol VARCHAR(5) NOT NULL -- e.g., $, €
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id VARCHAR(255) PRIMARY KEY, -- Using VARCHAR for product ID to match data.json
    name VARCHAR(255) NOT NULL,
    in_stock BOOLEAN DEFAULT TRUE,
    description TEXT,
    category_id INT, -- Foreign key to categories table
    brand VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product gallery images table
CREATE TABLE IF NOT EXISTS product_gallery_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(255) NOT NULL,
    image_url VARCHAR(1024) NOT NULL,
    sort_order INT DEFAULT 0, -- To sort images
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Prices table
CREATE TABLE IF NOT EXISTS prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(255) NOT NULL,
    currency_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL, -- Suitable for prices
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (currency_id) REFERENCES currencies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Attribute sets table (e.g., "Size", "Color")
CREATE TABLE IF NOT EXISTS attribute_sets (
    id VARCHAR(255) PRIMARY KEY, -- Using VARCHAR for ID to match data.json (e.g., "Size", "Color")
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL -- e.g., "text", "swatch"
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Attribute items/options table (e.g., "Small", "Medium", "Red", "#FFFFFF")
CREATE TABLE IF NOT EXISTS attribute_items (
    id VARCHAR(255) NOT NULL, -- Using VARCHAR for ID to match data.json (e.g., "Small", "Green")
    attribute_set_id VARCHAR(255) NOT NULL,
    display_value VARCHAR(255) NOT NULL, -- User-facing value (e.g., "Small", "Green")
    value VARCHAR(255) NOT NULL, -- Actual value (e.g., "S", "#00FF00")
    PRIMARY KEY (id, attribute_set_id), -- Composite PK to ensure item uniqueness within a set
    FOREIGN KEY (attribute_set_id) REFERENCES attribute_sets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pivot table to link products to attribute sets (many-to-many)
CREATE TABLE IF NOT EXISTS product_attribute_sets_pivot (
    product_id VARCHAR(255) NOT NULL,
    attribute_set_id VARCHAR(255) NOT NULL,
    PRIMARY KEY (product_id, attribute_set_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (attribute_set_id) REFERENCES attribute_sets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Main orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    -- user_id INT, -- Could be added later if a user system is implemented
    total_amount DECIMAL(10, 2) NOT NULL,
    currency_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- status VARCHAR(50) DEFAULT 'pending', -- Status field could be added
    FOREIGN KEY (currency_id) REFERENCES currencies(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order items table (products within each order)
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    price_per_unit DECIMAL(10, 2) NOT NULL, -- Price at time of order
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT -- Or SET NULL if order should persist on product deletion
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table to store selected attributes for each order item
CREATE TABLE IF NOT EXISTS order_item_selected_attributes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_item_id INT NOT NULL,
    attribute_set_id VARCHAR(255) NOT NULL, -- Attribute set ID (e.g., "Size")
    attribute_item_id VARCHAR(255) NOT NULL, -- Selected attribute item ID (e.g., "Small")
    attribute_item_display_value VARCHAR(255) NOT NULL, -- Selected item display value (e.g., "Small")
    attribute_item_value VARCHAR(255) NOT NULL, -- Selected item actual value (e.g., "S")
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE,
    FOREIGN KEY (attribute_set_id) REFERENCES attribute_sets(id) ON DELETE CASCADE,
    -- An FK on attribute_item_id is not possible with a composite PK on the source table,
    -- so data integrity relies on the application logic.
    INDEX idx_order_item_selected_attributes_item_set (attribute_item_id, attribute_set_id) -- Index for faster lookups
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;