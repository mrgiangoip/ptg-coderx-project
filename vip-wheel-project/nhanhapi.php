<?php
echo '<!DOCTYPE html>';
echo '<html lang="en">';
echo '<head>';
echo '    <meta charset="UTF-8">';
echo '    <meta http-equiv="refresh" content="60">';
echo '    <title>Tự động tải lại trang mỗi phút</title>';
echo '</head>';
echo '<body>';
// Khởi tạo một ứng dụng web sử dụng Slim Framework hoặc một framework PHP khác
// Ở đây, tôi sẽ chỉ cung cấp một đoạn mã PHP thuần túy mà không sử dụng framework

$maxPages = 69; // Giả sử có tối đa 200 trang
$saleChannelForStore = 2; // Giả sử giá trị saleChannel cho mua hàng tại cửa hàng là 2
$allOrderIds = [];

for ($page = $maxPages; $page >= 1; $page--) {
    $ch = curl_init();

    $data = [
        "version" => "2.0",
        "appId" => "73659",
        "businessId" => "21995",
        "accessToken" => "u4JBZHWNzxm00sJU75beloRiGzrdC4RoX8tehodCu6pxCwcVjOK4pDwwxscFlUw2Z1Ghq5FlTEVkMATKn4twjb0p0xLS3xGNVOrJNbZawDZJmxGM5fXocSUEBbKZGhLYu4mL0pb1LxT17EHiikfFeDxvD",
        "data" => json_encode(["page" => $page, "saleChannel" => $saleChannelForStore])
    ];

    curl_setopt($ch, CURLOPT_URL, "https://open.nhanh.vn/api/order/index");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // Bỏ qua xác thực tên máy chủ
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // Bỏ qua xác thực chứng chỉ

    $response = curl_exec($ch);
    $err = curl_error($ch);

    curl_close($ch);

    if ($err) {
        echo "Error fetching data: $err";
    } else {
        $responseData = json_decode($response, true);
        if (isset($responseData['data']['orders'])) {
            foreach ($responseData['data']['orders'] as $order) {
                $allOrderIds[] = $order['id'];
            }
        } else {
            // Nếu không có thêm dữ liệu, dừng vòng lặp
            break;
        }
    }

    // Nếu không có order nào trả về, dừng vòng lặp
    if (!$responseData['data']['orders']) {
        break;
    }
}

header('Content-Type: application/json');
echo json_encode($allOrderIds);

// Thông tin kết nối đến cơ sở dữ liệu
$servername = "localhost";
$username = "gomhang_khachvi";
$password = "Vumanhtien1@@";
$dbname = "gomhang_khachvi";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Khi thêm một order_id mới:
foreach ($allOrderIds as $orderId) {
    $currentTimestamp = date('Y-m-d H:i:s');
    $sql = "INSERT INTO orderids (order_id, created_at) VALUES ('$orderId', '$currentTimestamp')";
    if (!$conn->query($sql)) {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Xóa dữ liệu cũ hơn 7 ngày:
$sql = "DELETE FROM orderids WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)";
if (!$conn->query($sql)) {
    echo "Error deleting old data: " . $conn->error;
}

echo "Data inserted successfully!";

$conn->close();
echo '</body>';
echo '</html>';
?>
