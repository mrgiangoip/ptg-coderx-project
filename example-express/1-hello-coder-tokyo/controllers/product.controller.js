var db = require('../db');

module.exports.index = function(request, response) {
    response.render('products/index', {
        products: db.get('products').value().slice(0, 8)
    });
};

// Các hàm khác
