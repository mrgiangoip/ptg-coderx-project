function printCounter() {
  console.log('Counter:', counter);
  counter++;
}

let counter = 1;

// In giá trị của counter lặp lại sau mỗi 1 giây
const intervalId = setInterval(printCounter, 1000);

// Sau 5 giây dừng việc lặp lại
setTimeout(() => {
  clearInterval(intervalId);
  console.log('Interval stopped.');
}, 5000);

// write a function count from 1 to 10
// return a promise
function countFrom(a, b){
 
}
countFrom(1, 10).then(function(){
  console.log('Done');
});