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
    $user_info = $_SESSION['user_info'];

    if (isset($_POST['prizeResult'])) {
        $prizeResult = $_POST['prizeResult'];
        $customerId = $user_info['id'];

        // Kiểm tra số lần quay của người dùng
        $quay_count = $user_info['quay_count'];

        if ($quay_count == 0) {
            echo "Bạn đã quay đủ số  lượt. Xin hẹn gặp lại lần sau.";
        } else {
            // Tiến hành quay thưởng và cập nhật số lần quay
            $quay_count--;

            // Thêm kết quả quay vào bảng quay_thuong
            $sqlInsertResult = "INSERT INTO quay_thuong (customer_id, result, quay_lan) VALUES ($customerId, '$prizeResult', $quay_count)";

            if ($conn->query($sqlInsertResult) === TRUE) {
                // Cập nhật số lần quay
                $sqlUpdateQuayCount = "UPDATE customers SET quay_count = $quay_count WHERE id = $customerId";
                if ($conn->query($sqlUpdateQuayCount) === TRUE) {
                    if ($quay_count == 0) {
                        // Nếu đã quay đủ 3 lần, cập nhật cột "power_table_hidden" thành 1
                        $sqlUpdatePower = "UPDATE customers SET power_table_hidden = 1 WHERE id = $customerId";
                        if ($conn->query($sqlUpdatePower) === TRUE) {
                            echo "Kết quả quay thưởng đã được cập nhật thành công. Bạn đã hoàn thành đủ số lượt quay.";
                        } else {
                            echo "Lỗi: " . $conn->error;
                        }
                    } else {
                        echo "Kết quả quay thưởng đã được cập nhật thành công.";
                    }
                } else {
                    echo "Lỗi: " . $conn->error;
                }
            } else {
                echo "Lỗi: " . $conn->error;
            }
        }
    }
} else {
    echo "Không tìm thấy thông tin người dùng.";
}

$conn->close();
?>