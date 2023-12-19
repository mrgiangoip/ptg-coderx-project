var db = require('../db');

module.exports.addToCart = function(request,response, next){
	var productId = request.params.productId;
	var sessionId = request.signedCookies.sessionId;

	if(!sessionId){
		response.redirect('/products');
	 	return;
	}
	db.get('sessions')
	  .find({ Id: sessionId})
	  .set('cart.' + productId, 1)
	  .write();
	  response.redirect('/products');
};