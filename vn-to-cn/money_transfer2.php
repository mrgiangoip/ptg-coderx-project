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


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bank_china = $_POST['bank_china'];
    $exchange_rate = $_POST['exchange_rate'];
    $transfer_fee = $_POST['transfer_fee'];
    $amount_to_transfer = $_POST['amount_to_transfer'];
    $recipient_name = $_POST['recipient_name'];
    $converted_amount = ($amount_to_transfer * $exchange_rate) - $transfer_fee;
    $bank_vietnam = $_POST['bank_vietnam'];
    
    // Lấy giá trị của checkbox
    $checkbox_checked = isset($_POST['checkbox']) ? 1 : 0;

    // Lấy user_id của người dùng hiện tại (phải được xác định trước đó)
    $user_id = $_SESSION['user_id'];

    // Truy vấn cơ sở dữ liệu để lấy số tiền hiện tại của ngân hàng VN
    $get_balance_query = "SELECT total_amount_vn FROM bank_balance_vn WHERE bank_name_vn = '$bank_vietnam'";
    $balance_result = $conn->query($get_balance_query);
  
    // Truy vấn danh sách ngân hàng CN
      $sql = "SELECT bank_name_cn, total_amount_cn FROM bank_balance_cn";
      $result = $conn->query($sql);

    if ($balance_result->num_rows > 0) {
        $row = $balance_result->fetch_assoc();
        $current_balance_vn = $row['total_amount_vn'];
        
        // Trừ số tiền Việt Ra từ số tiền hiện tại của ngân hàng VN
        $new_balance_vn = $current_balance_vn - $converted_amount;

        // Cập nhật số tiền mới của ngân hàng VN vào cơ sở dữ liệu
        $update_balance_query = "UPDATE bank_balance_vn SET total_amount_vn = $new_balance_vn WHERE bank_name_vn = '$bank_vietnam'";
        $conn->query($update_balance_query);
    }
    
    // Tiếp tục với việc chèn dữ liệu mới vào bảng cn_to_vn_transfer
    $sql = "INSERT INTO cn_to_vn_transfer (bank_china, exchange_rate, transfer_fee, amount_to_transfer, recipient_name, converted_amount, bank_vietnam, checkbox_checked, user_id) VALUES ('$bank_china', $exchange_rate, $transfer_fee, $amount_to_transfer, '$recipient_name', $converted_amount, '$bank_vietnam', $checkbox_checked, $user_id)";

    if ($conn->query($sql) === TRUE) {
        $message = "Dữ liệu đã được ghi thành công!";
        $_SESSION['message'] = $message;
        header("Location: money_transfer2.php");
        exit;
    } else {
        $message = "Lỗi: " . $conn->error;
        $_SESSION['message'] = $message;
        header("Location: money_transfer2.php");
        exit;
    }
}


?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chuyển tiền từ Trung Quốc ra Việt Nam</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .nocopy {
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }
    </style>
</head>
<body>
<?php if (isset($_SESSION['user_id'])): ?>
    <?php require_once 'bank_list.php'; ?>
<div class="container mt-5">
    <h2>Chuyển tiền từ Trung Quốc ra Việt Nam</h2>
    <!-- Thêm nút đăng xuất -->
    <a href="logout.php" class="btn btn-danger">Đăng xuất</a>
    <a href="money_transfer.php" class="btn btn-success">VN to CN</a>
    <form action="" method="post">
      <div class="row">
        <div class="col-md-2 form-group">
          <label for="bank_china">Tên NH TQ:</label>
          <input type="text" class="form-control" name="bank_china" id="bank_china" required>
        </div>
        <div class="col-md-2 form-group">
          <label for="amount_to_transfer">Số Tệ Vào:</label>
          <input type="number" step="0.01" class="form-control" name="amount_to_transfer" id="amount_to_transfer" required>
          <span id="formatted_amount" style="font-weight: bold;"></span>
        </div>
        <div class="col-md-2 form-group">
          <label for="exchange_rate">Tỉ giá:</label>
          <input type="number" step="0.01" class="form-control" name="exchange_rate" id="exchange_rate" required>
        </div>
        <div class="col-md-2 form-group">
          <label for="transfer_fee">Phí:</label>
          <input type="number" step="0.01" class="form-control" name="transfer_fee" id="transfer_fee" required>
        </div>
        <div class="col-md-2 form-group">
          <label for="recipient_name">Tên người nhận tiền:</label>
          <input type="text" class="form-control" name="recipient_name" id="recipient_name" required>
        </div>
        <div class="col-md-2 form-group">
          <label for="bank_vietnam">Tên NH VN:</label>
          <input type="text" class="form-control" name="bank_vietnam" id="bank_vietnam" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Chuyển</button>
    </form>
    <script>
      document.addEventListener("DOMContentLoaded", function() {
        const recipientNameInput = document.getElementById("recipient_name");
        const submitButton = document.querySelector("button[type='submit']");

        recipientNameInput.addEventListener("blur", function() {
          const recipientName = recipientNameInput.value;

          // Thực hiện kiểm tra tên người nhận trong cơ sở dữ liệu SQL của bạn.
          // Dưới đây là một ví dụ đơn giản.
          const nameExistsInDatabase = checkIfNameExistsInDatabase(recipientName);

          if (nameExistsInDatabase) {
            const confirmation = confirm("Tên người nhận này đã tồn tại. Bạn có muốn tiếp tục?");

            if (!confirmation) {
              recipientNameInput.value = ""; // Xóa giá trị người nhận
            }
          }
        });

        // Hàm kiểm tra tên người nhận
        function checkIfNameExistsInDatabase(name) {
          // Thực hiện truy vấn SQL để kiểm tra tên trong bảng của bạn.
          // Đây là nơi bạn cần thêm mã kiểm tra SQL thực tế.

          // Dưới đây là một ví dụ giả định:
          const xhr = new XMLHttpRequest();
          xhr.open("POST", "check_recipient2.php", false);
          xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
          xhr.send("recipient_name=" + name);

          if (xhr.status === 200) {
            return xhr.responseText === "exists";
          } else {
            return false;
          }
        }
      });
    </script>


    <script>
      // Sử dụng JavaScript để định dạng số Tệ có dấu chấm khi nhập và hiển thị ngoài ô
      document.getElementById('amount_to_transfer').addEventListener('input', function (e) {
        // Lấy giá trị nhập vào
        let inputValue = e.target.value;

        // Định dạng giá trị với dấu chấm
        let formattedValue = inputValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        // Hiển thị giá trị đã định dạng bên ngoài ô
        document.getElementById('formatted_amount').textContent = formattedValue;
      });
    </script>


  
    <?php
      if (isset($_SESSION['message'])) {
      $message = $_SESSION['message'];
      echo '<div class="alert alert-success mt-4">' . $message . '</div>';
      unset($_SESSION['message']);  // Xóa thông điệp khỏi phiên sau khi hiển thị
      }

      // Truy vấn JOIN giữa cn_to_vn_transfer và users dựa trên user_id
      $result = $conn->query("SELECT t.*, u.fullname FROM cn_to_vn_transfer t 
                              JOIN users u ON t.user_id = u.id");

      if ($result->num_rows > 0) {
          echo '<table class="table table-striped mt-4">
              <thead>
                  <tr>
                      <th>Người chuyển</th>
                      <th>NH TQ</th>
                      <th>Số Tệ Vào</th>
                      <th>Tỉ giá</th>
                      <th>Phí</th>
                      <th>Người nhận</th>
                      <th>Số tiền Việt Ra</th>
                      <th>NH VN</th>
                      <th>Cho phép sao chép</th>
                  </tr>
              </thead>
              <tbody>';

          while ($row = $result->fetch_assoc()) {
              $canCopy = $row['user_id'] == $_SESSION['user_id'] ? '' : 'nocopy';
              $checkboxState = ($row['user_id'] == $_SESSION['user_id']) ? 'checked' : '';

              echo "<tr class='{$canCopy}'>
                    <td>" . htmlspecialchars($row["fullname"]) . "</td> 
                    <td>" . htmlspecialchars($row["bank_china"]) . "</td>
                    <td>" . htmlspecialchars($row["amount_to_transfer"]) . "</td>
                    <td>" . htmlspecialchars($row["exchange_rate"]) . "</td>
                    <td>" . htmlspecialchars($row["transfer_fee"]) . "</td>
                    <td>" . htmlspecialchars($row["recipient_name"]) . "</td>
                    <td>" . htmlspecialchars($row["converted_amount"]) . "</td>
                    <td>" . htmlspecialchars($row["bank_vietnam"]) . "</td>
                    <td><input type='checkbox' {$checkboxState} onclick='toggleCopy(this, {$row['id']})'></td>
                </tr>";
          }

          echo '</tbody></table>';
      }
      ?>

</div>
  
<!-- Nếu chưa đăng nhập -->
<?php else: ?>
  
    <?php require_once 'login_register.php'; ?>
 
<?php endif; ?>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js">
</body>
</html>
