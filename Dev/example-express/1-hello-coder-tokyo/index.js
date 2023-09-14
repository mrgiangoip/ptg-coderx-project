var express = require('express');
var bodyParser = require('body-parser');

var userRoute = require('./routes/user.route');

var port = 3000;

var app = express();

app.set('view engine', 'pug');
app.set('views', './views');

app.use(bodyParser.json()) // for parsing application/json
app.use(bodyParser.urlencoded({ extended: true })) // for parsing application/x-www-form-urlencoded

app.use(express.static('public'));

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

app.use('/users', userRoute);

app.listen(port, function(){
	console.log('Server listenning on port' + port);
});