<?php
$servername = "localhost";
$username = "gomhang_khachvi";
$password = "Vumanhtien1@@";
$dbname = "gomhang_khachvi";

// Tạo kết nối đến cơ sở dữ liệu
$conn = new mysqli($servername, $username, $password, $dbname);



// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Đặt charset cho kết nối
$conn->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = $_POST['new_username'];
    $new_password = $_POST['new_password'];
    $fullname = $_POST['fullname'];

    // Kiểm tra xem tên đăng nhập đã tồn tại chưa
    $check_username_sql = "SELECT id FROM users WHERE username = '$new_username'";
    $result = $conn->query($check_username_sql);

    if ($result->num_rows > 0) {
        $error_message = "Tên đăng nhập đã tồn tại. Vui lòng chọn tên đăng nhập khác.";
    } else {
        // Băm mật khẩu trước khi lưu vào cơ sở dữ liệu
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Thêm thông tin người dùng mới vào cơ sở dữ liệu
        $insert_user_sql = "INSERT INTO users (username, password, fullname) VALUES ('$new_username', '$hashed_password', '$fullname')";
        if ($conn->query($insert_user_sql) === TRUE) {
          $success_message = "Đăng ký thành công. Bạn có thể đăng nhập ngay bây giờ.";
          // Chuyển hướng đến trang money_transfer.php
          header("Location: money_transfer.php");
          exit();
          
        } else {
            $error_message = "Lỗi: " . $conn->error;
        }
    }
}

$conn->close();
?>
