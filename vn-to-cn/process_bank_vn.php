<?php
// Kết nối đến cơ sở dữ liệu MySQL
session_start();
$servername = "localhost";
$username = "gomhang_khachvi";
$password = "Vumanhtien1@@";
$dbname = "gomhang_khachvi";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to utf8mb4
$conn->set_charset("utf8mb4");

// Xử lý dữ liệu từ form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bank_name = $_POST['bank_name'];
    $total_amount = $_POST['total_amount'];

    // Thêm ngân hàng VN vào cơ sở dữ liệu
    $sql = "INSERT INTO bank_balance_vn (bank_name_vn, total_amount_vn) VALUES ('$bank_name', $total_amount)";

    if ($conn->query($sql) === TRUE) {
        echo "Ngân Hàng VN đã được thêm thành công!";
    } else {
        echo "Lỗi: " . $conn->error;
    }
}
// Chuyển hướng về trang thêm ngân hàng CN với thông báo
$_SESSION['message'] = $message;
header("Location: money_transfer.php");
exit;
$conn->close();
?>
