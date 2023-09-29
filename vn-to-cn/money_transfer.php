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

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Lấy giá trị của checkbox
    $checkbox_checked = isset($_POST['checkbox']) ? 1 : 0;

    // Lấy user_id của người dùng hiện tại (phải được xác định trước đó)
    $user_id = $_SESSION['user_id'];

    // Cập nhật cơ sở dữ liệu với trạng thái checkbox
    $update_query = "UPDATE vn_to_cn_transfer SET checked = $checkbox_checked WHERE user_id = $user_id AND your_condition_here";
  
     // Lấy giá trị từ form
    $bank_name_vn = $_POST['bank_name_vn'];
    $amount_vn = floatval($_POST['amount_vn']); // Chuyển đổi giá trị thành kiểu số thực

    // Kiểm tra xem tên NH VN và số tiền đã được nhập
    if (!empty($bank_name_vn) && $amount_vn > 0) {
        // Kiểm tra xem tên ngân hàng VN đã tồn tại trong cơ sở dữ liệu
        $check_bank_query = "SELECT * FROM bank_balance_vn WHERE bank_name_vn = '$bank_name_vn'";
        $result = $conn->query($check_bank_query);

        if ($result->num_rows > 0) {
            // Ngân hàng đã tồn tại trong cơ sở dữ liệu, cập nhật số tiền của ngân hàng
            $row = $result->fetch_assoc();
            $current_balance = $row['total_amount_vn'];
            $new_balance = $current_balance + $amount_vn;

            // Cập nhật số tiền của ngân hàng đó trong cơ sở dữ liệu
            $update_balance_query = "UPDATE bank_balance_vn SET total_amount_vn = $new_balance WHERE bank_name_vn = '$bank_name_vn'";
            if ($conn->query($update_balance_query) === TRUE) {
                // Cập nhật thành công
                $message = "Số tiền của ngân hàng đã được cập nhật thành công.";
            } else {
                // Cập nhật không thành công
                $message = "Lỗi khi cập nhật số tiền của ngân hàng: " . $conn->error;
            }
        } else {
            // Ngân hàng chưa tồn tại, thêm mới vào cơ sở dữ liệu
            $insert_bank_query = "INSERT INTO bank_balance_vn (bank_name_vn, total_amount_vn) VALUES ('$bank_name_vn', $amount_vn)";
            if ($conn->query($insert_bank_query) === TRUE) {
                // Thêm mới thành công
                $message = "Ngân hàng mới đã được thêm vào cơ sở dữ liệu.";
            } else {
                // Thêm mới không thành công
                $message = "Lỗi khi thêm mới ngân hàng: " . $conn->error;
            }
        }
    } else {
        // Hiển thị thông báo nếu dữ liệu không hợp lệ
        $message = "Vui lòng nhập tên NH VN và số tiền hợp lệ.";
    }
  
    $exchange_rate = $_POST['exchange_rate'];
    $fee = $_POST['fee'];
    $recipient_name = $_POST['recipient_name'];
    $bank_name_cn = $_POST['bank_name_cn'];

       $sql = "INSERT INTO vn_to_cn_transfer (user_id, bank_name_vn, amount_vn, exchange_rate, fee, recipient_name, bank_name_cn) 
            VALUES ($user_id, '$bank_name_vn', $amount_vn, $exchange_rate, $fee, '$recipient_name', '$bank_name_cn')";

    if ($conn->query($sql) === TRUE) {
        $message = "Dữ liệu đã được ghi thành công!";
        $_SESSION['message'] = $message;  // Lưu thông điệp vào phiên để hiển thị sau khi chuyển hướng
        header("Location: money_transfer.php");  // Thay "your_current_page.php" bằng tên trang hiện tại của bạn
        exit;
    } else {
        $message = "Lỗi: " . $conn->error;
        $_SESSION['message'] = $message;
        header("Location: money_transfer.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chuyển tiền từ Việt Nam sang Trung Quốc</title>
    <!-- Bootstrap CSS -->
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
<!-- Nếu đã đăng nhập -->
<?php if (isset($_SESSION['user_id'])): ?>
    <div class="container mt-5">
        <h2>Danh Sách Ngân Hàng VN</h2>

        <?php
        // Truy vấn danh sách ngân hàng VN
        $sql = "SELECT bank_name_vn, total_amount_vn FROM bank_balance_vn";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo '<ul class="list-group">';
            while ($row = $result->fetch_assoc()) {
                // Sử dụng hàm number_format để định dạng số tiền với dấu chấm phân cách hàng nghìn
                $formatted_amount = number_format($row['total_amount_vn']);
                echo '<li class="col-md-2 list-group-item">' . $row['bank_name_vn'] . ': ' . $formatted_amount . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>Không có ngân hàng nào trong cơ sở dữ liệu.</p>';
        } ?>
        <button id="toggleFormButton" class="btn">+</button>
        <form id="bankForm" action="process_bank_vn.php" method="POST" style="display: none;">
            <div class="col-md-2 form-group">
                <label for="bank_name">Tên Ngân Hàng VN:</label>
                <input type="text" class="form-control" name="bank_name" required>
            </div>

            <div class="col-md-2 form-group">
                <label for="total_amount">Số Tiền:</label>
                <input type="number" class="form-control" name="total_amount" required>
            </div>

            <button type="submit" class="btn btn-primary">Thêm Ngân Hàng</button>
        </form>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const toggleFormButton = document.getElementById("toggleFormButton");
            const bankForm = document.getElementById("bankForm");

            toggleFormButton.addEventListener("click", function () {
                if (bankForm.style.display === "none") {
                    bankForm.style.display = "block";
                    toggleFormButton.textContent = "-";
                } else {
                    bankForm.style.display = "none";
                    toggleFormButton.textContent = "+";
                }
            });
        });
    </script>
    <div class="container mt-5">
    <h2>Chuyển tiền từ Việt Nam sang Trung Quốc</h2>
      <!-- Thêm nút đăng xuất -->
    <a href="logout.php" class="btn btn-danger">Đăng xuất</a>
    <a href="money_transfer2.php" class="btn btn-success">CN to VN</a>
            <form action="" method="post">
                <div class="row">
                    <div class="col-md-2 form-group">
                        <label for="bank_name_vn">Tên NH VN:</label>
                        <input type="text" class="form-control" name="bank_name_vn" id="bank_name_vn" required>
                    </div>
                    <div class="col-md-2 form-group">
                        <label for="amount_vn">Số tiền VN vào:</label>
                        <input type="number" class="form-control" name="amount_vn" id="amount_vn_input" required>
                        <span id="formatted_amount_vn" style="font-weight: bold;"></span>
                    </div>
                    <div class="col-md-2 form-group">
                        <label for="exchange_rate">Tỉ giá:</label>
                        <input type="number" step="0.01" class="form-control" name="exchange_rate" id="exchange_rate" required>
                    </div>
                    <div class="col-md-2 form-group">
                        <label for="fee">Phí:</label>
                        <input type="number" step="0.01" class="form-control" name="fee" id="fee" required>
                    </div>
                    <div class="col-md-2 form-group">
                        <label for="recipient_name">Tên người nhận:</label>
                        <input type="text" class="form-control" name="recipient_name" id="recipient_name" required>
                    </div>
                    <div class="col-md-2 form-group">
                        <label for="bank_name_cn">Tên NH TQ:</label>
                        <input type="text" class="form-control" name="bank_name_cn" id="bank_name_cn" required>
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
                  xhr.open("POST", "check_recipient.php", false);
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
                // Sử dụng JavaScript để định dạng số tiền VN nhận có dấu chấm khi nhập và hiển thị ngoài ô
                document.getElementById('amount_vn_input').addEventListener('input', function (e) {
                    // Lấy giá trị nhập vào
                    let inputValue = e.target.value;

                    // Định dạng giá trị với dấu chấm
                    let formattedValue = inputValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

                    // Hiển thị giá trị đã định dạng bên ngoài ô
                    document.getElementById('formatted_amount_vn').textContent = formattedValue;
                });
            </script>

    <?php
    if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    echo '<div class="alert alert-success mt-4">' . $message . '</div>';
    unset($_SESSION['message']);  // Xóa thông điệp khỏi phiên sau khi hiển thị
    }

    
    $sql = "SELECT vn_to_cn_transfer.*, users.fullname 
        FROM vn_to_cn_transfer 
        INNER JOIN users ON vn_to_cn_transfer.user_id = users.id";
      $result = $conn->query($sql);

      if ($result->num_rows > 0) {
          echo '<table class="table table-striped mt-4">
              <thead>
                  <tr>
                      <th>Người chuyển</th>
                      <th>NH VN</th>
                      <th>Số tiền VN vào</th>
                      <th>Tỉ giá</th>
                      <th>Phí</th>
                      <th>Người nhận</th>
                      <th>Số tiền Tệ ra</th>
                      <th>NH CN</th>
                      <th>Cho phép sao chép</th>
                  </tr>
              </thead>
              <tbody>';

          while ($row = $result->fetch_assoc()) {
              $canCopy = $row['user_id'] == $_SESSION['user_id'] ? '' : 'nocopy';
              $checkboxState = ($row['user_id'] == $_SESSION['user_id']) ? 'checked' : '';

              echo "<tr class='{$canCopy}'>
                  <td>" . htmlspecialchars($row["fullname"]) . "</td>
                  <td>" . htmlspecialchars($row["bank_name_vn"]) . "</td>
                  <td>" . htmlspecialchars($row["amount_vn"]) . "</td>
                  <td>" . htmlspecialchars($row["exchange_rate"]) . "</td>
                  <td>" . htmlspecialchars($row["fee"]) . "</td>
                  <td>" . htmlspecialchars($row["recipient_name"]) . "</td>
                  <td>" . floor(($row["amount_vn"] / $row["exchange_rate"]) - $row["fee"]) . "</td>
                  <td>" . htmlspecialchars($row["bank_name_cn"]) . "</td>
                  <td><input type='checkbox' {$checkboxState} onclick='toggleCopy(this, {$row['user_id']})'></td>
              </tr>";
          }

          echo '</tbody>
          </table>';
      }
    ?>
</div>

<!-- Nếu chưa đăng nhập -->
<?php else: ?>
    <div class="container mt-5">
        <div class="row">
            <div id="loginForm" class="col-md-6 mx-auto">
                <!-- Form đăng nhập -->
                <h2 class="text-center">Đăng nhập</h2>
                <form action="login.php" method="post">
                    <div class="form-group">
                        <label for="username">Tên đăng nhập:</label>
                        <input type="text" class="form-control" name="username" id="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Mật khẩu:</label>
                        <input type="password" class="form-control" name="password" id="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Đăng nhập</button>
                </form>
            </div>
            <div id="registerForm" class="col-md-6 mx-auto" style="display: none;">
                <!-- Form đăng ký -->
                <h2 class="text-center">Đăng ký</h2>
                <form action="register.php" method="post">
                    <div class="form-group">
                        <label for="new_username">Tên đăng nhập mới:</label>
                        <input type="text" class="form-control" name="new_username" id="new_username" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">Mật khẩu mới:</label>
                        <input type="password" class="form-control" name="new_password" id="new_password" required>
                    </div>
                    <div class="form-group">
                        <label for="fullname">Họ và tên:</label>
                        <input type="text" class="form-control" name="fullname" id="fullname" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">Đăng ký</button>
                </form>
            </div>
        </div>
        <div class="text-center my-3">
            <button id="toggleFormButton" class="btn btn-info ml-2">Đăng ký</button>
        </div>
    </div>


    <script>
        var loginForm = document.getElementById('loginForm');
        var registerForm = document.getElementById('registerForm');
        var toggleFormButton = document.getElementById('toggleFormButton');

        toggleFormButton.addEventListener('click', function () {
            if (loginForm.style.display === 'block') {
                loginForm.style.display = 'none';
                registerForm.style.display = 'block';
                toggleFormButton.textContent = 'Đăng nhập';
            } else {
                loginForm.style.display = 'block';
                registerForm.style.display = 'none';
                toggleFormButton.textContent = 'Đăng ký';
            }
        });

        function toggleCopy(checkbox, userId) {
            const row = checkbox.closest('tr');
            
            // Nếu checkbox được tick và người dùng đang xem là người tạo dòng dữ liệu này
            if (checkbox.checked && userId == <?php echo $_SESSION['user_id']; ?>) {
                row.classList.remove('nocopy');
            } else {
                row.classList.add('nocopy');
            }
        }
    </script>

<?php endif; ?>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js">
</body>
</html>