var Mouse = require('./mouse.js');

var Cat = require('./cat.js');

var mickey = new Mouse('black');
var jerry = new Mouse('orage');

console.log(mickey);
console.log(jerry);

var tom = new Cat();
tom.eat(mickey);
tom.eat(jerry);
console.log(tom);