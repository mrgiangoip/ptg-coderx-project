var done = function(){
	console.log('Finish');
}

console.log('Start');
// khởi tạo timeout
var timeoutID = setTimeout(done,1000);
console.log('Done');
// hủy timeout
clearTimeout(timeoutID);