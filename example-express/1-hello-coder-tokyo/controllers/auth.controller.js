var md5 = require('md5');
var db = require('../db');

module.exports.login = function(request, response) {
    response.render('auth/login');
};

module.exports.postLogin = function(request, response) {
    var email = request.body.email;
    var password = request.body.password;

    var user = db.get('users').find({ email: email }).value();

    if (!user) {
        response.render('auth/login', {
            errors: ['User does not exist.'],
            values: request.body
        });
        return;
    }

    var hashedPassword = md5(password);

    if (user.password !== hashedPassword) {
        response.render('auth/login', {
            errors: ['Đéo Đúng!.'],
            values: request.body
        });
        return;
    }

    // If user exists and password is correct, you can consider it a successful login.
    // You might want to add some session handling or authentication logic here.
    response.cookie('userId', user.id, {
    	signed: true
    });
    
    response.redirect('/users');
};
