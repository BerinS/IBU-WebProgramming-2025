<?php
require_once __DIR__ . '/BaseService.php'; 
require_once __DIR__ . '/../dao/ProductsDao.php';

class ProductsService extends BaseService {
    public function __construct() {
        // Verify ProductsDao can be instantiated
        $dao = new ProductsDao();
        parent::__construct($dao);
    }

    public function getByCategory($category_id) {
        return $this->dao->getByCategory($category_id);
    }

    public function searchByName($name) {
        return $this->dao->searchByName($name);
    }

    public function getByBrand($brand) {
        return $this->dao->getByBrand($brand);
    }
    
    public function getByPriceRange($min_price, $max_price) {
        return $this->dao->getByPriceRange($min_price, $max_price);
    }
    
    public function getByStockQuantity($min_stock) {
        return $this->dao->getByStockQuantity($min_stock);
    }

    public function getByGender($gender) {
        return $this->dao->getByGender($gender);
    }

    //Function to add products
    public function add($product) {
        // Required fields
        $required = ['name', 'brand', 'price', 'stock_quantity', 'category_id', 'gender'];
    
        foreach ($required as $field) {
            if (!isset($product[$field]) || empty($product[$field])) {
                throw new Exception("Field '$field' is required.");
            }
        }
    
        if (!is_numeric($product['price']) || $product['price'] < 0) {
            throw new Exception("Price must be a positive number.");
        }
    
        if (!is_numeric($product['stock_quantity']) || $product['stock_quantity'] < 0) {
            throw new Exception("Stock quantity must be a non-negative number.");
        }
    
        $valid_genders = ['male', 'female', 'unisex'];
        if (!in_array(strtolower($product['gender']), $valid_genders)) {
            throw new Exception("Gender must be one of: male, female, unisex.");
        }
    
        // Insert the product and get the new ID
        $product_id = $this->dao->insert($product);
        
        // Return the full product object by fetching it from the database
        if ($product_id) {
            return $this->dao->get_by_id($product_id);
        } else {
            throw new Exception("Failed to create product.");
        }
    }
    
    
    
}
?>