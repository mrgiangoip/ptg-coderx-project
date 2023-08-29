// write a function count from 1 to 10
// return a promise
// function countFrom(a, b){
 
// }
// countFrom(1, 10).then(function(){
//   console.log('Done');
// });

// Viết một hàm đếm từ a đến b và trả về một promise
function countFrom(a, b) {
  // Tạo và trả về một Promise mới
  return new Promise((resolve, reject) => {
    // Khởi tạo biến để lưu giữ giá trị hiện tại của đếm
    let current = a;
    
    // Định nghĩa một hàm đệ quy để thực hiện việc đếm và log giá trị hiện tại ra console
    function count() {
      // Log giá trị hiện tại ra console
      console.log(current);
      
      // Kiểm tra nếu giá trị hiện tại chưa đạt đến giá trị kết thúc b
      if (current < b) {
        // Nếu chưa, tăng giá trị hiện tại lên 1
        current++;
        
        // Sử dụng setTimeout để gọi lại hàm đếm sau một khoảng thời gian 1000ms (1 giây)
        setTimeout(count, 1000);
      } else {
        // Nếu giá trị hiện tại đã đạt đến giá trị kết thúc b, thì resolve Promise
        resolve();
      }
    }
    
    // Bắt đầu quá trình đếm bằng cách gọi hàm count
    count();
  });
}

// Gọi hàm countFrom với a = 1 và b = 10
countFrom(1, 10).then(() => {
  console.log('Done'); // Đoạn này sẽ được thực hiện sau khi quá trình đếm hoàn tất
});

// cách 2

// Hàm countFrom sẽ đếm từ số a đến số b và trả về một Promise
function countFrom(a, b) {
  return new Promise((resolve) => {
    // Khởi tạo biến current để lưu giá trị đang đếm, ban đầu là a
    let current = a;

    // Sử dụng hàm setInterval để thực hiện đếm sau mỗi khoảng thời gian 1000ms (1 giây)
    const intervalId = setInterval(() => {
      // In ra giá trị hiện tại của biến current
      console.log(current);

      // Nếu current chưa đếm đến b, tăng current lên 1 để chuyển sang số tiếp theo
      if (current < b) {
        current++;
      } else {
        // Nếu current đã đếm đến b, dừng đếm bằng cách xóa interval
        clearInterval(intervalId);

        // Sau khi hoàn thành đếm từ a đến b, resolve Promise
        resolve();
      }
    }, 1000);
  });
}

// Hàm main là async function để sử dụng await
async function main() {
  console.log('Start counting...');

  // Gọi hàm countFrom từ 1 đến 10 và sử dụng await để đợi hoàn thành
  await countFrom(1, 10);

  // Sau khi đếm hoàn tất, in ra thông báo "Done"
  console.log('Done');
}

// Gọi hàm main để bắt đầu thực hiện đếm và in kết quả
main();