var fs = require('fs');
var dataString = fs.readFileSync('./data.json', {encoding: 'utf8'});
var dataObject = JSON.parse(dataString);
console.log(dataObject.name);

var members = [];
var mySelf = {};
mySelf.name = "Giang";
mySelf.age = "31";
members.push(mySelf);
dataObject.members = members;
dataString = JSON.stringify(dataObject);
console.log(dataObject);

fs.writeFileSync('./data.json',dataString);
// 1.Show all students
// 2.Create a new student
// 3.Save & Exit
