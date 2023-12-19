<?php
session_start();

$servername = "localhost";
$username = "gomhang_khachvi";
$password = "Vumanhtien1@@";
$dbname = "gomhang_khachvi";

// Kết nối đến database
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Đặt charset cho kết nối
$conn->set_charset("utf8mb4");

$message = "";

// Xử lý việc thêm ngân hàng CN mới
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bank_name_cn = $_POST['bank_name_cn'];
    $total_amount_cn = floatval($_POST['total_amount_cn']); // Chuyển đổi giá trị thành kiểu số thực

    // Kiểm tra xem tên ngân hàng CN và số tiền đã được nhập
    if (!empty($bank_name_cn) && $total_amount_cn > 0) {
        // Thêm ngân hàng CN mới vào cơ sở dữ liệu
        $insert_bank_query = "INSERT INTO bank_balance_cn (bank_name_cn, total_amount_cn) VALUES ('$bank_name_cn', $total_amount_cn)";
        if ($conn->query($insert_bank_query) === TRUE) {
            // Thêm mới thành công
            $message = "Ngân hàng CN mới đã được thêm vào cơ sở dữ liệu.";
        } else {
            // Thêm mới không thành công
            $message = "Lỗi khi thêm mới ngân hàng CN: " . $conn->error;
        }
    } else {
        // Hiển thị thông báo nếu dữ liệu không hợp lệ
        $message = "Vui lòng nhập tên ngân hàng CN và số tiền hợp lệ.";
    }
}
// Chuyển hướng về trang thêm ngân hàng CN với thông báo
$_SESSION['message'] = $message;
header("Location: money_transfer.php");
exit;
$conn->close();
?>