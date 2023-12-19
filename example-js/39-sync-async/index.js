var fs = require('fs');

// Sync
// console.log('Start');
// var song1 = fs.readFileSync('song1.txt', { encoding: 'utf8'});
// console.log(song1);
// var song2 = fs.readFileSync('song2.txt', { encoding: 'utf8'});
// console.log(song2);
// var song3 = fs.readFileSync('song3.txt', { encoding: 'utf8'});
// console.log(song3);
// console.log('End');
//async
console.log('Start');
fs.readFile('song1.txt', { encoding: 'utf8'}, function(err, song1){
	console.log(song1);
});
console.log('End');
