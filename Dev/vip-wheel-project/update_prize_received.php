<?php
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

// Check if user is logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
    if (isset($_POST['prizeId']) && isset($_POST['isReceived'])) {
        $prizeId = $_POST['prizeId'];
        $isReceived = $_POST['isReceived'];

        // Cập nhật trạng thái quà trong bảng quay_thuong
        $sql = "UPDATE quay_thuong SET is_received = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $isReceived, $prizeId);

        if ($stmt->execute()) {
            if ($isReceived) {
                echo "Trạng thái nhận quà đã được cập nhật thành công.";
            } else {
                echo "Trạng thái nhận quà đã được huỷ thành công.";
            }
        } else {
            echo "Lỗi: " . $conn->error;
        }
    } else {
        echo "Dữ liệu không hợp lệ.";
    }
} else {
    echo "Không tìm thấy thông tin người dùng.";
}

$conn->close();
?>
