var moment = require('moment');

// var now = new Date();
// var myBirthday = new Date(1992, 2, 20); //20-02-1992

// console.log(now.getTime());
// console.log(myBirthday.getTime());

var now = moment('2023-08-09 00:00');
console.log(now.fromNow());
