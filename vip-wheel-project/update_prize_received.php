<?php
session_start();

$servername = "localhost";
$username = "gomhang_khachvi";
$password = "Vumanhtien1@@";
$dbname = "gomhang_khachvi";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Kiểm tra người dùng đã đăng nhập
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo "Bạn cần đăng nhập để thực hiện hành động này.";
    exit;
}

$user_info = $_SESSION['user_info'];

if (isset($_POST['prizeId']) && isset($_POST['isReceived'])) {
    $prizeId = $_POST['prizeId'];
    $isReceived = $_POST['isReceived'];

    // Cập nhật trạng thái nhận quà
    $sql = "UPDATE quay_thuong SET is_received = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $isReceived, $prizeId);

    if ($stmt->execute()) {
        // Ghi nhận hành động vào bảng lịch sử
        $actionType = $isReceived ? 'Đã Nhận Quà' : 'Hủy Nhận Quà';
        $enteredBy = $user_info['fullname']; // Tên người dùng từ session
        $adminLevel = $user_info['level']; // Cấp độ từ session
        $customerId = $user_info['id']; // ID người dùng từ session

        $historySql = "INSERT INTO prize_actions (customer_id, prize_id, action_type, admin_level, entered_by, action_timestamp) VALUES (?, ?, ?, ?, ?, CONVERT_TZ(NOW(),@@session.time_zone,'+07:00'))";
        $historyStmt = $conn->prepare($historySql);
        $historyStmt->bind_param("iisss", $customerId, $prizeId, $actionType, $adminLevel, $enteredBy);
        $historyStmt->execute();

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

$conn->close();
?>