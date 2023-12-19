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

// Xử lý chuyển tiền
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $from_bank = $_POST['from_bank'];
    $to_bank = $_POST['to_bank'];
    $transfer_amount = $_POST['transfer_amount'];

    if ($from_bank == $to_bank) {
        $_SESSION['message'] = "Không thể chuyển tiền cho cùng một ngân hàng!";
    } else {
        // Trừ tiền từ ngân hàng gửi
        $sql_deduct = "UPDATE bank_balance_vn SET total_amount_vn = total_amount_vn - $transfer_amount WHERE bank_name_vn = '$from_bank'";
        
        // Cộng tiền cho ngân hàng nhận
        $sql_add = "UPDATE bank_balance_vn SET total_amount_vn = total_amount_vn + $transfer_amount WHERE bank_name_vn = '$to_bank'";

        if ($conn->query($sql_deduct) === TRUE && $conn->query($sql_add) === TRUE) {
          // Lưu thông tin giao dịch vào bảng transactions_vn
          $sql_insert_transaction = "INSERT INTO vn_to_vn_transfer (from_bank, to_bank, transfer_amount) VALUES ('$from_bank', '$to_bank', $transfer_amount)";
          $conn->query($sql_insert_transaction);

          $_SESSION['message'] = "Chuyển tiền thành công!";
        } else {
            $_SESSION['message'] = "Lỗi: " . $conn->error;
        }
    }
}

// Chuyển hướng về trang danh sách ngân hàng với thông báo
header("Location: money_transfer.php");
exit;
$conn->close();
?>
