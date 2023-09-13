<?php
$servername = "localhost";
$username = "gomhang_khachvi";
$password = "Vumanhtien1@@";
$dbname = "gomhang_khachvi";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

if (isset($_POST['prizeId']) && isset($_POST['isReceived'])) {
    $prizeId = $_POST['prizeId'];
    $isReceived = $_POST['isReceived'];

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

$conn->close();
?>
