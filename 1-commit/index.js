const Mouse = require('./mouse.js');
const Cat = require('./cat.js');
const Dog = require('./dog.js'); // Sử dụng .js ở đây

const dog = new Dog('Tom');
dog.sayHi();

const mickey = new Mouse('black');
const jerry = new Mouse('orage');

console.log(mickey);
console.log(jerry);

const tom = new Cat();
tom.eat(mickey);
tom.eat(jerry);
console.log(tom);
