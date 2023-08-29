// While
var secretPassword = 'coders.tokyo';
var readline = require('readline-sync');

var result;
while (true) {
  result = readline.question('Nhập vào mật khẩu: ');

  if (result === secretPassword) {
    console.log('Welcome!');
    break;
  } else {
    console.log('Wrong password');
  }
}
// do while
var secretPassword = 'coders.tokyo';
var readline = require('readline-sync');

var result;
do {
  result = readline.question('Nhập vào mật khẩu: ');

  if (result === secretPassword) {
    console.log('Welcome!');
  } else {
    console.log('Wrong password');
  }
} while (result !== secretPassword);