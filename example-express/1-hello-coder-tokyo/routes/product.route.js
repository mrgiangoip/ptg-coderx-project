// product.route.js

const express = require('express');
const router = express.Router();
const productController = require('../controllers/product.controller');
 // Giả sử bạn có một file controller cho sản phẩm

// Lấy danh sách tất cả sản phẩm
router.get('/', productController.getAllProducts);

// Lấy thông tin chi tiết một sản phẩm dựa trên ID
router.get('/:productid', productController.getProductById);

// Tạo mới một sản phẩm
router.post('/', productController.createProduct);

// Cập nhật thông tin sản phẩm
router.put('/:productid', productController.updateProduct);

// Xóa một sản phẩm dựa trên ID
router.delete('/:productid', productController.deleteProduct);

module.exports = router;
