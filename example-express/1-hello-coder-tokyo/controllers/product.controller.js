const { db, getProducts } = require('../db');

module.exports = {
    getAllProducts: function(request, response) {
        const products = getProducts();
        response.render('products/index', {
            products: products
        });
    },

    // Lấy thông tin chi tiết của một sản phẩm dựa trên ID
    getProductById: function(request, response) {
        const productId = request.params.productid;
        const product = db.get('products').find({ id: productId }).value();

        if (!product) {
            response.status(404).send('Product not found');
            return;
        }

        response.render('products/detail', { product: product }); // Giả sử bạn có một view detail.pug cho sản phẩm
    },

    // Tạo mới một sản phẩm
    createProduct: function(request, response) {
        // TODO: Add create logic here
        response.send('Create product logic here');
    },

    // Cập nhật thông tin sản phẩm
    updateProduct: function(request, response) {
        // TODO: Add update logic here
        response.send('Update product logic here');
    },

    // Xóa một sản phẩm
    deleteProduct: function(request, response) {
        // TODO: Add delete logic here
        response.send('Delete product logic here');
    }
};
