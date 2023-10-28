var db = require('../db');

module.exports.index = function(request, response) {
    var page = parseInt(request.query.page) || 1;
    var perPage = 8;

    var drop = (page - 1) * perPage;
    var totalProducts = db.get('products').value().length;
    var totalPages = Math.ceil(totalProducts / perPage);

    response.render('products/index', {
        products: db.get('products').drop(drop).take(perPage).value(),
        page,
        totalPages
    });
};
