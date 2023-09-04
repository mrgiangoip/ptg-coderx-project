var express = require('express');
var app = express();
var bodyParser = require('body-parser');
var low = require('lowdb');
var shortid = require('shortid');

var FileSync = require('lowdb/adapters/FileSync');
const adapter = new FileSync('db.json');
const db = low(adapter);

db.defaults({ users: [] }).write();

var port = 3000;

app.set('view engine', 'pug');
app.set('views', './views');

app.use(bodyParser.json()) // for parsing application/json
app.use(bodyParser.urlencoded({ extended: true })) // for parsing application/x-www-form-urlencoded

var users = [
	{id: 1, name: 'Giang'},
	{id: 2, name: 'Linh'},
	{id: 3, name: 'Trang'}
];

app.get('/', function(request, response){
	response.render('index', {
		name: "Giang"
	});
});

app.get('/users', function(request, response){
	response.render('users/index', {
		users: db.get('users').value()
	});
});

app.get('/users/search', function(request, response){
	var q = request.query.q;
	var matcheUsers = db.get('users').filter(function(user){
		return user.name.toLowerCase().indexOf(q.toLowerCase()) !== -1;
	})
	.value();
	response.render('users/index', {
		users: matcheUsers,
		q: q // Trả về giá trị tìm kiếm q trong phản hồi
	});
});

app.get('/users/create', function(request, response){
	response.render('users/create');
});

app.get('/users/:id', function(request, response){
	var id = request.params.id;

	var user = db.get('users').find({ id: id }).value();

	response.render('users/view', {
		user: user
	});
});

app.post('/users/create', function(request, response){
	request.body.id = shortid.generate();
	db.get('users').push(request.body).write();
	response.redirect("/users");
});

app.listen(port, function(){
	console.log('Server listenning on port' + port);
});