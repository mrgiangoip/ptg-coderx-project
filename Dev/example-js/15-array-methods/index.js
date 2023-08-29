// các method  của 1 array
// - a.concat(b)
// - a.push(b)
// - a.pop()
// - a.shift()
// - a.unshift()
// Tự đọc trên MDN (Mozila Developer Network)
// Google keyword: array methods
// - a.splice
// - a.splice
var a = [1, 3, 2];
var a1 = [1, 3, 2];
var a2 = [1, 3, 2];
var a3 = [1, 3, 2];
var a4 = [1, 3, 2];
var b = [10,20];
var c = 5;

// this is concat method
var c1 = a.concat(b);
console.log('Concat method:');
console.log('Đây là a:');
console.log(a);
console.log('Đây là b:');
console.log(b);
console.log('Đây là c1:');
console.log(c1);
console.log('Lưu ý: a và b không thay đổi, c1 là mảng mới');
console.log('');

// this is push method
var d = a1.push(c); //length
console.log('Push method:');
console.log('Đây là a1:');
console.log(a1);
console.log('Đây là d:');
console.log(d);
console.log('Lưu ý: a1 thay đổi thành mảng mới, d là độ dài của mảng (length)');
console.log('');

//this is pop method
var e = a2.pop();
console.log('Pop method:');
console.log('Đây là e:');
console.log(e);
console.log('Đây là a2:');
console.log(a2);
console.log('Lưu ý: a2 thay đổi thành mảng mới, e là độ dài của mảng (length)');
console.log('');

//this is shift method
var f = a3.shift();
console.log('Shift method:');
console.log('Đây là f:');
console.log(f);
console.log('Đây là a3:');
console.log(a3);
console.log('Lưu ý: a3 thay đổi thành mảng mới, f là phần từ đầu tiên của mảng được lấy ra');
console.log('');

//this is unshift method
var g = a4.unshift(c);
console.log('Unshift method:');
console.log('Đây là g:');
console.log(g);
console.log('Đây là a4:');
console.log(a4);
console.log('Lưu ý: a4 thay đổi thành mảng mới phần tử của c được thay vào vị trí đầu tiên, g là độ dài của mảng (length)');
console.log('');

// this is slice method
console.log('Slice method:');
var h = c1.slice(0, 4);
console.log('Đây là h:');
console.log(h);
console.log('Lưu ý: h tạo thành mảng mới được lấy từ vị trí 0 đến 4 của mảng cũ');
console.log('');

// this is slice method
console.log('Splice method:');
let fruits = ['apple', 'banana', 'cherry'];
fruits.splice(1, 0, 'orange'); // Thêm 'orange' vào index 1 của fruits
console.log(fruits); // ['apple', 'orange', 'banana', 'cherry']
console.log('Thêm orange vào index 1 của fruits');
console.log('');

let numbers = [1, 2, 3, 4, 5];
numbers.splice(2, 2); // Xóa 2 phần tử từ index 2
console.log(numbers); // [1, 2, 5]
console.log('Xóa 2 phần tử từ index 2');
console.log('');

let colors = ['red', 'green', 'blue'];
colors.splice(1, 1, 'yellow'); // Thay thế phần tử ở index 1 bằng 'yellow'
console.log(colors); // ['red', 'yellow', 'blue']
console.log('Thay thế phần tử ở index 1 bằng yellow');
console.log('');

let items = [1, 2, 3, 4, 5];
let removedItems = items.splice(2, 2); // Xóa 2 phần tử từ index 2
console.log(removedItems); // [3, 4]
console.log(items); // [1, 2, 5]
console.log('Xóa 2 phần tử từ index 2');
