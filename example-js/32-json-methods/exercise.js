const fs = require('fs');
const readline = require('readline').createInterface({
  input: process.stdin,
  output: process.stdout
});

const dataFilePath = './data2.json';
let students = [];

// Hàm hiển thị menu và chờ người dùng chọn tùy chọn
function showMenu() {
  readline.question(
    "Choose an option:\n1. Show all students\n2. Create a new student\n3. Save & Exit\n",
    option => {
      switch (option) {
        case "1":
          showAllStudents();
          break;
        case "2":
          createNewStudent();
          break;
        case "3":
          saveAndExit();
          break;
        default:
          console.log("Invalid option. Please try again.");
          showMenu();
      }
    }
  );
}

// Hàm hiển thị danh sách tất cả sinh viên từ tệp data2.json
function showAllStudents() {
  loadStudentsFromDataFile();
  console.log("Showing all students...");
  students.forEach(student => {
    console.log(`${student.name}, Age: ${student.age}, Class: ${student.class}`);
  });
  showMenu();
}

// Hàm tạo sinh viên mới và yêu cầu người dùng nhập thông tin
function createNewStudent() {
  readline.question("Your name? ", name => {
    readline.question("Your age? ", age => {
      readline.question("Your class? ", className => {
        if (name && age && className) {
          const newStudent = { name: name, age: age, class: className };
          students.push(newStudent);
          console.log("New student created successfully!");
        } else {
          console.log("Please enter all information to create a new student.");
        }
        showMenu();
      });
    });
  });
}

// Hàm lưu dữ liệu vào tệp data2.json và thoát ứng dụng
function saveAndExit() {
  const data = JSON.stringify(students, null, 2);

  fs.writeFile(dataFilePath, data, 'utf8', err => {
    if (err) {
      console.error("Error saving data:", err);
    } else {
      console.log("Data saved to data2.json successfully!");
    }
    readline.close();
  });
}

// Hàm tải dữ liệu sinh viên từ tệp data2.json (nếu có) vào mảng students
function loadStudentsFromDataFile() {
  if (fs.existsSync(dataFilePath)) {
    const data = fs.readFileSync(dataFilePath, 'utf8');
    students = JSON.parse(data);
    console.log("Data loaded from data2.json.");
  }
}

// Bắt đầu ứng dụng bằng cách hiển thị menu
showMenu();