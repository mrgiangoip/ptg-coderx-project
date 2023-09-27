<?php
session_start();

// Xóa tất cả biến phiên
session_unset();

// Hủy phiên làm việc
session_destroy();

// Chuyển hướng người dùng đến trang đăng nhập hoặc trang chính của ứng dụng
header("Location: money_transfer.php"); // Thay đổi URL theo trang đích của bạn
exit();
?>
