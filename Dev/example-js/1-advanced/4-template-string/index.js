function greeting(name){
	return 'Hi, ' + name + '!';
};


var result = greeting('Minh');
console.log(result);


//Template String
function greeting2(name){
	return `Hi, ${name} ${1 + 2}!`;
};

var result2 = greeting2('Minh');
console.log(result2);