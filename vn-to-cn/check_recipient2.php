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

// Lấy tên người nhận từ yêu cầu POST
$recipientName = $_POST["recipient_name"];

// Thực hiện truy vấn SQL để kiểm tra tên trong bảng của bạn
$sql = "SELECT COUNT(*) as count FROM cn_to_vn_transfer WHERE recipient_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $recipientName);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$count = $row["count"];

// Trả về kết quả
if ($count > 0) {
    echo "exists";
} else {
    echo "not_exists";
}

// Đóng kết nối đến cơ sở dữ liệu
$conn->close();
?>
