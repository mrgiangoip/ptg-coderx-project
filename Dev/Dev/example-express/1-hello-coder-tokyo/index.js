var express = require('express');
var app = express();

var port = 3000;

app.get('/', function(request, response){
	response.send('<h1>Hello Coders.Tokyo</h1>');
});

app.get('/users', function(request, response){
	response.send('User List');
});

app.listen(port, function(){
	console.log('Server listenning on port' + port);
});