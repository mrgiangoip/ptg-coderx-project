var db = require('../db');
var shortid = require('shortid');

module.exports.index = function(request, response){
	response.render('users/index', {
		users: db.get('users').value()
	});
};

module.exports.search = function(request, response){
	var q = request.query.q;
	var matcheUsers = db.get('users').filter(function(user){
		return user.name.toLowerCase().indexOf(q.toLowerCase()) !== -1;
	})
	.value();
	response.render('users/index', {
		users: matcheUsers,
		q: q // Trả về giá trị tìm kiếm q trong phản hồi
	});
};

module.exports.create = function(request, response){
	console.log(request.cookies);
	response.render('users/create');
};

module.exports.get = function(request, response){
	var id = request.params.id;

	var user = db.get('users').find({ id: id }).value();

	response.render('users/view', {
		user: user
	});
};

module.exports.postCreate = function(request, response) {
  request.body.id = shortid.generate();
  request.body.avatar = request.file.path.split('/').slice(1).join('/');


	db.get('users').push(request.body).write();
	response.redirect("/users");
};