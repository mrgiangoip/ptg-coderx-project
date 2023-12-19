<?php
// Kết nối vào cơ sở dữ liệu
$servername = "localhost";
$username = "gomhang_khachvi";
$password = "Vumanhtien1@@";
$dbname = "gomhang_khachvi";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to utf8mb4
$conn->set_charset("utf8mb4");

// Truy vấn số lượt quay từ cơ sở dữ liệu (sử dụng giá trị mặc định là 3 nếu không có giá trị)
$query = "SELECT COALESCE(spins_left, 3) AS spins_left FROM customers WHERE user_id = $user_id";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $spins_left = $row['spins_left'];
} else {
    $spins_left = 3; // Sử dụng giá trị mặc định là 3 nếu không tìm thấy dữ liệu
}

// Đóng kết nối cơ sở dữ liệu
$conn->close();

// Sử dụng JavaScript để gán giá trị vào biến
echo "<script>var spinsLeft = " . $spins_left . ";</script>";
?>
