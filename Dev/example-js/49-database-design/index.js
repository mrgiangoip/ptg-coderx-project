// Định nghĩa thông tin về các lớp học
var classes = [
  {
    id: 0,
    name: '1A',
    teacher: 0 // Mã giáo viên chủ nhiệm
  },
  {
    id: 1,
    name: '2A',
    teacher: 1
  },
  {
    id: 2,
    name: '3A',
    teacher: 2
  },
  {
    id: 3,
    name: '4A',
    teacher: 3
  },
  {
    id: 4,
    name: '5A',
    teacher: 4
  }
];

// Định nghĩa thông tin về các giáo viên
var teachers = [
  {
    id: 0,
    name: 'Quynh',
    age: 30
  },
  {
    id: 1,
    name: 'Chinh',
    age: 55
  },
  {
    id: 2,
    name: 'Nguyet',
    age: 40
  },
  {
    id: 3,
    name: 'Huong',
    age: 45
  },
  {
    id: 4,
    name: 'Hai',
    age: 50
  }
];

// Định nghĩa thông tin về các học sinh
var students = [
  { id: 0, name: 'Minh', height: 120, class: 0 },
  { id: 1, name: 'Minh', height: 120, class: 0 },
  { id: 2, name: 'Minh', height: 120, class: 0 },
  { id: 3, name: 'Hai', height: 120, class: 1 }
];

// Hàm trả về danh sách học sinh trong một lớp học cụ thể
function getStudentsInClass(className) {
  // Tìm thông tin lớp học từ tên lớp đầu vào
  var classObject = classes.find(function (x) {
    return x.name === className;
  });
  
  // Sử dụng filter để lọc danh sách học sinh trong lớp
  var studentsInClass = students.filter(function (student) {
    return student.class === classObject.id;
  });

  return studentsInClass;
}

// Gọi hàm và lấy danh sách học sinh trong lớp '2A'
var studentsInClass = getStudentsInClass('1A');

// Hiển thị danh sách học sinh ra màn hình
console.log(studentsInClass);