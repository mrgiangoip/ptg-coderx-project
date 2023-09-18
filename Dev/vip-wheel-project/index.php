 <!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Danh sách khách VIP</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <link rel="stylesheet" href="./style.css" type="text/css" />
        <link rel="stylesheet" href="./main.css" type="text/css" />
        <script type="text/javascript" src="https://gomhang.vn/wp-content/plugins/vip-wheel-plugin/Winwheel.js"></script>
        <script src="http://cdnjs.cloudflare.com/ajax/libs/gsap/latest/TweenMax.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <!-- SweetAlert2 CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <!-- SweetAlert2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<body>
    <?php
    session_start();

    $servername = "localhost";
    $username = "gomhang_khachvi";
    $password = "Vumanhtien1@@";
    $dbname = "gomhang_khachvi";

    $wpDbname = "gomhang_gh2";
    $wpUsername = "gomhang_gh2";
    $wpPassword = "Vumanhtien1@@";

    // Create connection to main database
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Create connection to WordPress database
    $wpConn = new mysqli($servername, $wpUsername, $wpPassword, $wpDbname);

    // Check main connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check WordPress connection
    if ($wpConn->connect_error) {
        die("WordPress connection failed: " . $wpConn->connect_error);
    }

    // Set character set to utf8mb4 for both databases
    $conn->set_charset("utf8mb4");
    $wpConn->set_charset("utf8mb4");

    // Check if user is logged in
    $loggedin = false;
    $user_info = null;

    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
        $loggedin = true;
        $user_info = $_SESSION['user_info'];
    }

    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = md5($_POST['password']); 

        $sql = "SELECT * FROM customers WHERE phone = '$username' AND password = '$password'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $loggedin = true;
            $user_info = $result->fetch_assoc();
        } else {
            $_SESSION['login_error'] = "Đăng nhập không thành công. Vui lòng kiểm tra lại số điện thoại hoặc mật khẩu!";
        }
    }

    // Logout
    if (isset($_GET['logout'])) {
        session_destroy();
        echo '<script>window.location.replace("./index.php");</script>';
        exit;
    }

    if ($loggedin) {
        $_SESSION['loggedin'] = true;
        $_SESSION['user_info'] = $user_info;
    }

        // Handle redeem code for user
        if ($loggedin && isset($_POST['redeem_submit'])) {
            $redeemCode = $_POST['redeem_code'];
            $customerId = $user_info['id'];

            // Kiểm tra xem mã mua hàng đã tồn tại trong bảng redeem_history và đã được người dùng này nhập chưa
            $sqlCheckRedeemHistory = "SELECT redeem_code FROM redeem_history WHERE redeem_code = '$redeemCode' AND customer_id = $customerId";
            $resultCheckRedeemHistory = $conn->query($sqlCheckRedeemHistory);

            // Kiểm tra xem mã mua hàng có tồn tại trong bảng wp_wc_order_product_lookup
            $sqlCheckOrderInWP = "SELECT order_id FROM wp_wc_order_product_lookup WHERE order_id = '$redeemCode'";
            $resultCheckOrderInWP = $wpConn->query($sqlCheckOrderInWP);

            // Kiểm tra xem mã mua hàng có tồn tại trong bảng orderids
            $sqlCheckOrderInOrderIds = "SELECT order_id FROM orderids WHERE order_id = '$redeemCode'";
            $resultCheckOrderInOrderIds = $conn->query($sqlCheckOrderInOrderIds);

            if ($resultCheckRedeemHistory->num_rows > 0) {
                $_SESSION['error_message'] = "Mã mua hàng đã tồn tại và bạn đã nhập rồi!";
            } elseif ($resultCheckOrderInWP->num_rows > 0 || $resultCheckOrderInOrderIds->num_rows > 0) {
                // Mã mua hàng tồn tại trong wp_wc_order_product_lookup hoặc orderids, tiếp tục xử lý
                // Cập nhật mã mua hàng vào bảng redeem_history
                $sqlInsertRedeem = "INSERT INTO redeem_history (customer_id, redeem_code, redeemed_at) VALUES ($customerId, '$redeemCode', CONVERT_TZ(NOW(), '+00:00', '+07:00'))";
                if ($conn->query($sqlInsertRedeem) === TRUE) {
                    // Cập nhật số lượt quay và power_table_hidden cho khách hàng
                    $sqlUpdateQuayAndPowerTable = "UPDATE customers SET quay_count = quay_count + 1 WHERE id = $customerId";
                    $conn->query($sqlUpdateQuayAndPowerTable);

                    // Kiểm tra quay_count sau khi đã cập nhật và cập nhật power_table_hidden nếu cần thiết
                    $sqlCheckQuayCount = "SELECT quay_count FROM customers WHERE id = $customerId";
                    $resultCheckQuayCount = $conn->query($sqlCheckQuayCount);
                    $rowQuayCount = $resultCheckQuayCount->fetch_assoc();
                    if ($rowQuayCount['quay_count'] >= 1) {
                        $sqlUpdatePowerTable = "UPDATE customers SET power_table_hidden = 0 WHERE id = $customerId";
                        $conn->query($sqlUpdatePowerTable);
                    }
                    $_SESSION['success_message'] = "Nhập mã mua hàng thành công và bạn đã nhận được thêm 1 lượt quay!";
                    $user_info['quay_count'] += 1;
                    $_SESSION['user_info']['quay_count'] = $user_info['quay_count'];
                    $user_info['power_table_hidden'] = 0;
                } else {
                    $_SESSION['error_message'] = "Lỗi khi thêm mã mua hàng: " . $conn->error;
                }
            } else {
                $_SESSION['error_message'] = "Mã mua hàng không hợp lệ.";
            }
            header("Location: ./index.php");
            exit;
        }


            // Xử lý nhập mã mua hàng cho admin
            if (isset($_POST['admin_redeem_submit'])) {
                $redeemCodeAdmin = $_POST['admin_redeem_code'];
                $customerIdAdmin = $_POST['customer_id'];

                // Kiểm tra xem mã mua hàng có tồn tại trong bảng wp_wc_order_product_lookup
                $sqlCheckOrderAdminInWP = "SELECT order_id FROM wp_wc_order_product_lookup WHERE order_id = '$redeemCodeAdmin'";
                $resultCheckOrderAdminInWP = $wpConn->query($sqlCheckOrderAdminInWP);

                // Kiểm tra xem mã mua hàng có tồn tại trong bảng orderids
                $sqlCheckOrderAdminInOrderIds = "SELECT order_id FROM orderids WHERE order_id = '$redeemCodeAdmin'";
                $resultCheckOrderAdminInOrderIds = $conn->query($sqlCheckOrderAdminInOrderIds);

                if ($resultCheckOrderAdminInWP->num_rows > 0 || $resultCheckOrderAdminInOrderIds->num_rows > 0) {
                    // Mã mua hàng tồn tại trong wp_wc_order_product_lookup hoặc orderids, tiếp tục xử lý

                    // Kiểm tra xem mã đã tồn tại trong bảng redeem_history chưa
                    $sqlCheckRedeemAdmin = "SELECT * FROM redeem_history WHERE redeem_code = '$redeemCodeAdmin'";
                    $resultCheckRedeemAdmin = $conn->query($sqlCheckRedeemAdmin);

                    // Nếu mã chưa tồn tại trong bảng redeem_history
                    if ($resultCheckRedeemAdmin->num_rows == 0) { 
                        // Lấy tên người nhập mã mua hàng (admin)
                        $enteredByAdmin = $_SESSION['user_info']['fullname'];

                        // Lấy level của admin hoặc admin1
                        $adminLevel = $_SESSION['user_info']['level'];

                        // Thêm mã vào bảng redeem_history và cập nhật cột entered_by và admin_level
                        $sqlInsertRedeemAdmin = "INSERT INTO redeem_history (customer_id, redeem_code, entered_by, admin_level, redeemed_at) VALUES ($customerIdAdmin, '$redeemCodeAdmin', '$enteredByAdmin', '$adminLevel', CONVERT_TZ(NOW(), '+00:00', '+07:00'))";
                        if ($conn->query($sqlInsertRedeemAdmin) === TRUE) {
                            // Tăng số lượng quay_count cho khách hàng
                            $sqlUpdateQuayCountAdmin = "UPDATE customers SET quay_count = quay_count + 1 WHERE id = $customerIdAdmin";
                            $conn->query($sqlUpdateQuayCountAdmin);

                            // Kiểm tra giá trị của power_table_hidden
                            $sqlCheckPowerTable = "SELECT power_table_hidden FROM customers WHERE id = $customerIdAdmin";
                            $resultCheckPowerTable = $conn->query($sqlCheckPowerTable);
                            $rowPowerTable = $resultCheckPowerTable->fetch_assoc();

                            // Nếu power_table_hidden là 1, cập nhật giá trị thành 0
                            if ($rowPowerTable['power_table_hidden'] == 1) {
                                $sqlUpdatePowerTableAdmin = "UPDATE customers SET power_table_hidden = 0 WHERE id = $customerIdAdmin";
                                $conn->query($sqlUpdatePowerTableAdmin);
                            }

                            $_SESSION['admin_message'] = "Cập nhật mã mua hàng thành công cho khách hàng có ID $customerIdAdmin.";
                        } else {
                            $_SESSION['admin_error'] = "Lỗi khi thêm mã mua hàng: " . $conn->error;
                        }
                    } else {
                        $_SESSION['admin_error'] = "Mã mua hàng đã tồn tại.";
                    }
                } else {
                    $_SESSION['admin_error'] = "Mã mua hàng không hợp lệ.";
                }
                header("Location: ./index.php");
                exit;
            }

    // Ở phần nội dung trang:
    ?>
    <div class="container mt-5">
        <?php
        if(isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success text-center">' . $_SESSION['success_message'] . '</div>';
            unset($_SESSION['success_message']); // Xóa thông điệp sau khi hiển thị
        }

        if(isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger text-center">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']); // Xóa thông điệp sau khi hiển thị
        }
        ?>
        <?php if (!$loggedin) { ?>
            <div class="login-form">
                <?php
                  if (isset($_SESSION['login_error'])) {
                      echo '<div class="alert alert-danger text-center">' . $_SESSION['login_error'] . '</div>';
                      // Xóa thông báo sau khi hiển thị
                      unset($_SESSION['login_error']);
                  }
                 ?>
                <h1>Đăng nhập</h1>
                <form method="post" action="">
                    <div class="form-group">
                        <label for="username">Số điện thoại:</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Mật khẩu:</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Đăng nhập</button>
                    </div>
                </form>
            </div>
            <div class="container vip-info mt-5">
                <h1>Đăng ký</h1>
                <form method="post" action="">
                      <div class="form-group">
                          <label for="fullname">Tên đầy đủ:</label>
                          <input type="text" id="fullname" name="fullname" class="form-control" required>
                      </div>

                      <div class="form-group">
                          <label for="phone">Số điện thoại:</label>
                          <input type="text" id="phone" name="phone" class="form-control" required>
                      </div>
                    
                      <div class="form-group">
                         <label for="password">Mật khẩu:</label>
                         <input type="password" id="password" name="password" class="form-control" required>
                      </div>

                      <div class="form-group">
                          <label for="level">Cấp độ:</label>
                          <select id="level" name="level" class="form-control" readonly required>
                              <option value="Vip1" selected>Vip1</option>
                          </select>
                      </div>

                      <div class="form-group">
                          <label for="points">Điểm:</label>
                          <input type="number" id="points" name="points" class="form-control" value="0" readonly required>
                      </div>
                  
                      <button type="submit" name="create_account" class="btn btn-primary">Tạo tài khoản</button>
                  
                        <div style="margin-top: 10px;" class="g-recaptcha" data-sitekey="6LeE-A8oAAAAAN2f18KJ8JP9kaIan9v14O4SVtq2"></div>
                  </form>
                </div>
                <div class="text-center my-3">
                    <button id="showLoginForm" class="btn btn-info ml-2">Đăng nhập</button>
                    <button id="showRegisterForm" class="btn btn-success mr-2">Đăng ký</button>
                </div>
                <?php
                      // Xử lý dữ liệu khi người dùng gửi thông tin
                          if (isset($_POST['create_account'])) {
                              $fullname = $_POST['fullname'];
                              $phone = $_POST['phone'];
                              $level = $_POST['level'];
                              $points = $_POST['points'];
                              $password = md5($_POST['password']);  // Mã hóa mật khẩu trước khi lưu

                              // Kiểm tra số điện thoại đã tồn tại trong CSDL
                              $sql_check_phone = "SELECT phone FROM customers WHERE phone = '$phone'";
                              $result = $conn->query($sql_check_phone);

                              if ($result->num_rows > 0) {
                                  // Số điện thoại đã tồn tại
                                  echo "<script>alert('Tài khoản với số điện thoại này đã tồn tại!');</script>";
                              } else {
                                  // Tiến hành tạo tài khoản
                                  $sql_insert = "INSERT INTO customers (fullname, phone, level, points, password) VALUES ('$fullname', '$phone', '$level', $points, '$password')";

                                  if ($conn->query($sql_insert) === TRUE) {
                                      echo "Tài khoản mới đã được tạo!";
                                  } else {
                                      echo "Lỗi: " . $sql_insert . "<br>" . $conn->error;
                                  }
                               }
                            }
                      ?>
                <script>
                  $(document).ready(function() {
                        // Ẩn form Đăng ký và nút Đăng nhập khi mới vào trang
                        $(".vip-info").hide();
                        $("#showLoginForm").hide();

                        // Sự kiện click cho nút "Đăng ký"
                        $("#showRegisterForm").click(function() {
                            $(".login-form").hide();
                            $(".vip-info").show();
                            $(this).hide();
                            $("#showLoginForm").show();
                        });

                        // Sự kiện click cho nút "Đăng nhập"
                        $("#showLoginForm").click(function() {
                            $(".vip-info").hide();
                            $(".login-form").show();
                            $(this).hide();
                            $("#showRegisterForm").show();
                        });
                    });
              </script>
        <?php } else { ?>
            <div class="vip-info">
                    <div class="d-flex align-items-center justify-content-center">
                      <div class="text-center">
                        <button id="changePasswordBtn" onclick="showChangePasswordForm()" class="btn btn-warning">Đổi mật khẩu</button>
                        <button id="redeemBtn" onclick="showRedeemForm()" class="btn btn-primary" style="display: none;">Mã mua hàng</button>
                      </div>
                    </div>
                  <div class="redeem-form">
                      <form method="post" action="">
                          <div class="form-group">
                              <label for="redeem_code">Mã mua hàng:</label>
                              <input type="text" class="form-control" id="redeem_code" name="redeem_code" required>
                          </div>
                          <button type="submit" name="redeem_submit" class="btn btn-primary">Nhập mã</button>
                      </form>
                  </div>
              
                    <div class="change-password-form mt-5" style="display:none;">
                      <form action="" method="post" class="mt-5 w-50 mx-auto">
                          <div class="form-group">
                              <label for="current_password">Nhập mật khẩu cũ:</label>
                              <input type="password" class="form-control" id="current_password" name="current_password">
                          </div>
                          <div class="form-group">
                              <label for="new_password">Nhập mật khẩu mới:</label>
                              <input type="password" class="form-control" id="new_password" name="new_password">
                          </div>
                          <div class="form-group">
                              <label for="confirm_new_password">Xác nhận mật khẩu mới:</label>
                              <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password">
                          </div>
                          <button type="submit" name="change_password" class="btn btn-warning">Đổi mật khẩu</button>
                      </form>
                      <?php 
                        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] && isset($_POST['change_password'])) {
                            $current_password = md5($_POST['current_password']); 
                            $new_password = md5($_POST['new_password']);
                            $confirm_new_password = md5($_POST['confirm_new_password']);

                            $messageType = "";
                            $messageContent = "";

                            if ($user_info['password'] === $current_password) {
                                if ($new_password === $confirm_new_password) {
                                    $stmt = $conn->prepare("UPDATE customers SET password = ? WHERE id = ?");
                                    $stmt->bind_param("si", $new_password, $user_info['id']);
                                    if ($stmt->execute()) {
                                        $messageType = "success";
                                        $messageContent = "Đổi mật khẩu thành công!";
                                        $_SESSION['user_info']['password'] = $new_password;
                                    } else {
                                        $messageType = "danger";
                                        $messageContent = "Có lỗi xảy ra. Vui lòng thử lại.";
                                    }
                                    $stmt->close();
                                } else {
                                    $messageType = "danger";
                                    $messageContent = "Mật khẩu mới và mật khẩu xác nhận không khớp.";
                                }
                            } else {
                                $messageType = "danger";
                                $messageContent = "Mật khẩu cũ không chính xác.";
                            }
                        }
                        if (!empty($messageContent)) {
                            echo "<script>alert('{$messageContent}');</script>";
                        }
                      ?>
                  </div>
                    <script>
                        function showChangePasswordForm() {
                            // Ẩn form mã mua hàng và hiển thị form đổi mật khẩu
                            document.querySelector('.redeem-form').style.display = 'none';
                            document.querySelector('.change-password-form').style.display = 'block';

                            // Ẩn nút Đổi mật khẩu và hiển thị nút Mã mua hàng
                            document.getElementById('changePasswordBtn').style.display = 'none';
                            document.getElementById('redeemBtn').style.display = 'block';
                        }

                        function showRedeemForm() {
                            // Hiển thị form mã mua hàng và ẩn form đổi mật khẩu
                            document.querySelector('.redeem-form').style.display = 'block';
                            document.querySelector('.change-password-form').style.display = 'none';

                            // Hiển thị nút Đổi mật khẩu và ẩn nút Mã mua hàng
                            document.getElementById('changePasswordBtn').style.display = 'block';
                            document.getElementById('redeemBtn').style.display = 'none';
                        }
                    </script>
              
                  <h1>Thông tin khách VIP</h1>
                  <table>
                      <tr>
                          <th>Full Name</th>
                          <th>Phone</th>
                          <th>Level</th>
                          <th>Điểm</th>
                          <th>Kết quả quay thưởng</th>
                          <th>Lượt quay còn lại</th>
                      </tr>
                      <?php
                      if ($loggedin) {
                          echo "<tr>";
                          echo "<td>" . $user_info['fullname'] . "</td>";
                          echo "<td>" . $user_info['phone'] . "</td>";
                          echo "<td>" . $user_info['level'] . "</td>";
                          echo "<td>" . $user_info['points'] . "</td>";

                          // Lấy thông tin kết quả quay từ bảng quay_thuong
                          $customerId = $user_info['id'];
                          $sqlGetQuayResults = "SELECT result, is_received, quay_lan FROM quay_thuong WHERE customer_id = $customerId ORDER BY quay_lan DESC LIMIT 100";
                          $resultQuayResults = $conn->query($sqlGetQuayResults);

                          echo "<td>";
                          if ($resultQuayResults->num_rows > 0) {
                              while ($rowQuayResult = $resultQuayResults->fetch_assoc()) {
                                  $color = $rowQuayResult['is_received'] ? 'color: red;' : '';
                                  // Tạo ID duy nhất cho mỗi kết quả
                                  $result_id = 'prize-result-' . $rowQuayResult["id"];
                                  echo "<span id='$result_id' style='$color'>Nhận: " . $rowQuayResult["result"] . "</span><br>";
                              }
                          } else {
                              echo "Chưa có kết quả quay nào.";
                          }
                          echo "</td>";

                          // Hiển thị giá trị quay_count
                          echo "<td id='remaining_spins'>" . $user_info['quay_count'] . " lượt</td>";

                          echo "</tr>";
                      }
                      ?>
                  </table>
                  <a href="?logout=true" class="btn btn-danger">Đăng xuất</a>
              </div>
            <?php if ($loggedin && $user_info['power_table_hidden'] == 0 && $user_info['level'] >= 'Vip1' && $user_info['level'] <= 'Vip5') { ?>
         <!-- Phần hiển thị form quay thưởng -->
                <div class="congratulations">
                    <!-- Lời chúc mừng đến khách hàng -->
                    <p>Chào mừng <strong><?php echo $user_info['fullname']; ?></strong> đến với thế giới của những ưu đãi đặc biệt!</p>
                    <p>Hãy cùng khám phá và nhận thưởng với đẳng cấp <strong>VIP <?php echo substr($user_info['level'], 3); ?></strong>.</p>
                     <!--code quay thưởng -->
                      <div align="center">
            <table id="power_table" cellpadding="0" cellspacing="0" border="0" class="luckywheel">
            <tr>
                <td class="level-lucky">
                    <div class="power_controls">
                        <br />
                        <br />
                        <table class="power" cellpadding="10" cellspacing="0">
                            <tr>
                                <th align="center">Power</th>
                            </tr>
                            <tr>
                                <td width="78" align="center" id="pw3" onClick="powerSelected(3);">Khó</td>
                            </tr>
                            <tr>
                                <td align="center" id="pw2" onClick="powerSelected(2);">Bình Thường</td>
                            </tr>
                            <tr>
                                <td align="center" id="pw1" onClick="powerSelected(1);">Dễ</td>
                            </tr>
                        </table>
                        <br />
                        <img id="spin_button" src="https://gomhang.vn/wp-content/plugins/vip-wheel-plugin/spin_off.png" alt="Spin" onClick="startSpin();" />
                        <br /><br />
                        &nbsp;&nbsp;<a href="#" onClick="resetWheel(); return false;">Chơi lại</a><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(reset)
                    </div>
                </td>
                <td width="438" height="582" class="the_wheel" align="center" valign="center">
                    <canvas id="canvas" width="434" height="434">
                        <p style="color: white" align="center">Sorry, your browser doesn't support canvas. Please try another.</p>
                    </canvas>
                </td>
            </tr>
        </table>
            <script src="./quay_function.js"></script>                
          <!--code quay thưởng -->
            <?php } elseif($loggedin && $user_info['power_table_hidden'] == 1) { ?>
                    <!-- Thông báo cho người dùng rằng họ chưa đủ điều kiện để quay thưởng -->
                    <div class="not-qualified">
                        <p>Bạn chưa đủ điều kiện để quay thưởng. Vui lòng nhập mã mua hàng và đảm bảo bạn còn lượt quay.</p>
                    </div>
            <?php } else { ?>
                <div class="not-qualified">
                    <!-- Thông báo cho khách hàng không phải Vip2 đến Vip5 -->
                    <p>Xin lỗi, bạn không thuộc đối tượng khách hàng được quay thưởng.</p>
                    <p>Khách VIP từ Vip2 đến Vip5 mới được hưởng chế độ này.</p>
                </div>
            <?php } ?>


           <?php if ($user_info['level'] == 'admin' || $user_info['level'] == 'admin1') { ?>
                        
            <!--bắt đầu phần bảng Lịch sử -->              
        <?php if ($user_info['level'] == 'admin') { //bắt đầu phần bảng Lịch sử?>    
              
                <div class="vip-info mt-5">
                <div class="text-center my-3">
                    <button class="btn btn-primary" id="showRedeemHistory" style="display: none;">Lịch Sử Mã Mua Hàng</button>
                    <button class="btn btn-info" id="showCheckboxHistory">Lịch Sử Checkbox</button>
                    <button class="btn btn-success" id="showCreateHistory">Lịch Sử Tạo Tài Khoản</button>
                </div>
                <div id="redeemHistoryTable" class="history-table">
                  
                        <!-- Bảng lịch sử mã mua hàng -->
                        <h1>Lịch Sử Mã Mua Hàng</h1>

                        <div class="mt-4">
                            <form action="" method="get" class="form-inline">
                                <input type="hidden" name="view" value="redeem">

                                <label class="mr-2" id="daysFilterLabel">Chọn thời gian:</label>

                                <select name="days_filter" class="form-control mr-2">
                                    <option value="all">Tất cả</option>
                                    <option value="1">1 ngày</option>
                                    <option value="7">7 ngày</option>
                                    <option value="30">30 ngày</option>
                                    <option value="90">90 ngày</option>
                                    <option value="custom">Chọn theo lịch</option>
                                </select>

                                <input type="date" name="from_date" id="fromDate" class="form-control mr-2" style="display: none;">

                                <input type="date" name="to_date" id="toDate" class="form-control mr-2" style="display: none;">
                              
                                <label class="mr-2">Tìm theo SĐT:</label>
                                <input type="text" name="phone_search" class="form-control mr-2" value="<?php echo isset($_GET['phone_search']) ? htmlspecialchars($_GET['phone_search']) : ''; ?>">

                                <input type="submit" value="Lọc" class="btn btn-primary">
                            </form>
                        </div>
                        <script>
                        
                          // Lưu giá trị đã chọn vào localStorage khi người dùng thay đổi tùy chọn
                          document.querySelector('select[name="days_filter"]').addEventListener('change', function() {
                              localStorage.setItem('selected_days_filter', this.value);
                          });
                            
                          // Lấy giá trị đã lưu từ localStorage và đặt nó vào phần "Chọn khoảng thời gian"
                          document.addEventListener('DOMContentLoaded', function() {
                              const selectedDaysFilter = localStorage.getItem('selected_days_filter');
                              if (selectedDaysFilter) {
                                  const selectElement = document.querySelector('select[name="days_filter"]');
                                  selectElement.value = selectedDaysFilter;
                                  // Hiển thị hoặc ẩn các input date tùy thuộc vào giá trị đã chọn
                                  if (selectedDaysFilter === "custom") {
                                      document.getElementById('fromDate').style.display = "inline-block";
                                      document.getElementById('toDate').style.display = "inline-block";
                                  } else {
                                      document.getElementById('fromDate').style.display = "none";
                                      document.getElementById('toDate').style.display = "none";
                                  }
                              }
                          });

                          
                        </script>
                        <script>
                            document.querySelector('select[name="days_filter"]').addEventListener('change', function() {
                                if (this.value === "custom") {
                                    document.getElementById('fromDate').style.display = "inline-block";
                                    document.getElementById('toDate').style.display = "inline-block";
                                } else {
                                    document.getElementById('fromDate').style.display = "none";
                                    document.getElementById('toDate').style.display = "none";
                                }
                            });
                        </script>

                        <?php
                        $view = isset($_GET['view']) ? $_GET['view'] : 'redeem'; // Mặc định là redeem nếu không có tham số 
                        
                        $filter_date = "";
                          if (isset($_GET['days_filter'])) {
                              switch ($_GET['days_filter']) {
                                  case 'all':
                                      $filter_date = ""; // Không áp dụng bất kỳ bộ lọc nào
                                      break;
                                  case '1':
                                      $filter_date = " AND DATE(redeemed_at) = CURDATE()";
                                      break;
                                  case '7':
                                      $filter_date = " AND DATE(redeemed_at) BETWEEN CURDATE() - INTERVAL 6 DAY AND CURDATE()";
                                      break;
                                  case '30':
                                      $filter_date = " AND DATE(redeemed_at) BETWEEN CURDATE() - INTERVAL 29 DAY AND CURDATE()";
                                      break;
                                  case '90':
                                      $filter_date = " AND DATE(redeemed_at) BETWEEN CURDATE() - INTERVAL 89 DAY AND CURDATE()";
                                      break;
                                  case 'custom':
                                      if (isset($_GET['from_date']) && isset($_GET['to_date'])) {
                                          $from_date = $_GET['from_date'];
                                          $to_date = $_GET['to_date'];
                                          $filter_date = " AND DATE(redeemed_at) BETWEEN '$from_date' AND '$to_date'";
                                      }
                                      break;
                              }
                          }
                        
                        if ($user_info['level'] == 'admin') {
                              $records_per_page = 10;

                              // Lấy tổng số bản ghi
                              $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM redeem_history WHERE admin_level IN (?, ?) " . $filter_date);
                              $stmt->bind_param("ss", $level1, $level2);
                              $level1 = 'admin';
                              $level2 = 'admin1';
                              $stmt->execute();
                              $resultCount = $stmt->get_result();
                              $rowTotal = $resultCount->fetch_assoc();
                              $total_pages = ceil($rowTotal['total'] / $records_per_page);

                              $redeemPage = (isset($_GET['redeemPage']) && is_numeric($_GET['redeemPage'])) ? intval($_GET['redeemPage']) : 1;
                              $redeemPage = max(1, min($total_pages, $redeemPage));

                              $start_from = ($redeemPage - 1) * $records_per_page;

                              $phone_search = isset($_GET['phone_search']) ? $_GET['phone_search'] : '';

                              // Lấy dữ liệu
                              $stmt = $conn->prepare("SELECT r.id, r.customer_id, r.redeem_code, r.redeemed_at, r.entered_by, r.admin_level, c.fullname, c.phone
                                  FROM redeem_history r
                                  JOIN customers c ON r.customer_id = c.id
                                  WHERE r.admin_level IN (?, ?) " . $filter_date . " AND (c.phone LIKE ? OR c.fullname LIKE ?)
                                  LIMIT ?, ?");
                              $search_term = '%' . $phone_search . '%';
                              $stmt->bind_param("ssssii", $level1, $level2, $search_term, $search_term, $start_from, $records_per_page);
                              $stmt->execute();
                              $resultRedeemHistory = $stmt->get_result();

                              if ($resultRedeemHistory->num_rows > 0) {
                                echo '<table class="table table-striped">';
                                echo "<thead><tr><th>Tên Khách Hàng</th><th>Số điện thoại</th><th>Mã Mua Hàng</th><th>Thời Gian Nhập</th><th>Tên Người Nhập</th><th>Cấp Độ</th></tr></thead>";
                                echo "<tbody>";

                                while ($row = $resultRedeemHistory->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row["fullname"] . "</td>";
                                    echo "<td>" . $row["phone"] . "</td>";
                                    echo "<td>" . $row["redeem_code"] . "</td>";
                                    echo "<td>" . $row["redeemed_at"] . "</td>";
                                    echo "<td>" . $row["entered_by"] . "</td>";
                                    echo "<td>" . $row["admin_level"] . "</td>";
                                    echo "</tr>";
                                }

                                echo "</tbody>";
                                echo "</table>";
                                // Phân trang
                                echo "<div>Trang: </div>";
                                echo "<ul class='pagination'>";

                                // Nút "Trước"
                                $prevPage = $redeemPage > 1 ? $redeemPage - 1 : 1;
                                $prev_url = "./index.php?redeemPage=$prevPage&view=redeem&phone_search=" . urlencode($phone_search) . "&days_filter=" . $_GET['days_filter'];
                                if (isset($_GET['from_date'])) {
                                    $prev_url .= "&from_date=" . $_GET['from_date'];
                                }
                                if (isset($_GET['to_date'])) {
                                    $prev_url .= "&to_date=" . $_GET['to_date'];
                                }
                                echo "<li class='page-item " . ($redeemPage == 1 ? "disabled" : "") . "'><a class='page-link' href='$prev_url'>Trước</a></li>";

                                // Số trang cụ thể
                                for ($page = 1; $page <= $total_pages; $page++) {
                                    $activeClass = $page == $redeemPage ? "active" : "";
                                    $page_url = "./index.php?redeemPage=$page&view=redeem&phone_search=" . urlencode($phone_search) . "&days_filter=" . $_GET['days_filter'];
                                    if (isset($_GET['from_date'])) {
                                        $page_url .= "&from_date=" . $_GET['from_date'];
                                    }
                                    if (isset($_GET['to_date'])) {
                                        $page_url .= "&to_date=" . $_GET['to_date'];
                                    }
                                    echo "<li class='page-item $activeClass'><a class='page-link' href='$page_url'>$page</a></li>";
                                }

                                // Nút "Tiếp"
                                $nextPage = $redeemPage < $total_pages ? $redeemPage + 1 : $total_pages;
                                $next_url = "./index.php?redeemPage=$nextPage&view=redeem&phone_search=" . urlencode($phone_search) . "&days_filter=" . $_GET['days_filter'];
                                if (isset($_GET['from_date'])) {
                                    $next_url .= "&from_date=" . $_GET['from_date'];
                                }
                                if (isset($_GET['to_date'])) {
                                    $next_url .= "&to_date=" . $_GET['to_date'];
                                }
                                echo "<li class='page-item " . ($redeemPage == $total_pages ? "disabled" : "") . "'><a class='page-link' href='$next_url'>Tiếp</a></li>";

                                echo "</ul>";

                            } else {
                                echo "Không có dữ liệu lịch sử mã mua hàng.";

                                // Nút quay trở lại
                                $prevPage = $redeemPage > 1 ? $redeemPage - 1 : 1;
                                $back_url = "./index.php?redeemPage=$prevPage&view=redeem&phone_search=" . urlencode($phone_search);
                                echo "<a href='$back_url' class='page-link'>Trước</a>";
                            }

                           } //kết thúc xét admin
                        ?>
              </div>
              
              <div id="checkboxHistoryTable" class="history-table">
                
                <!-- Bảng lịch sử checkbox -->
                    <h1>Lịch Sử Checkbox</h1>
                
                <div class="mt-4">
                    <form action="" method="get" class="form-inline">
                        <input type="hidden" name="view" value="checkbox">

                        <label class="mr-2" id="daysFilterCheckboxLabel">Chọn thời gian:</label>
                        <select name="days_filter_checkbox" id="daysFilterCheckbox" class="form-control mr-2">
                            <option value="all">Tất cả</option>
                            <option value="1">1 ngày</option>
                            <option value="7">7 ngày</option>
                            <option value="30">30 ngày</option>
                            <option value="90">90 ngày</option>
                            <option value="custom">Chọn theo lịch</option>
                        </select>

                        <input type="date" name="from_date_checkbox" id="fromDateCheckbox" class="form-control mr-2" style="display: none;">
                        <input type="date" name="to_date_checkbox" id="toDateCheckbox" class="form-control mr-2" style="display: none;">

                        <label class="mr-2">Tìm theo SĐT:</label>
                        <input type="text" name="phone_search_checkbox" class="form-control mr-2" value="<?php echo isset($_GET['phone_search_checkbox']) ? htmlspecialchars($_GET['phone_search_checkbox']) : ''; ?>">

                        <input type="submit" value="Lọc" class="btn btn-primary">
                    </form>
                </div>

                <script>
                    // Xác định select box và tham số URL
                    var daysFilterCheckbox = document.getElementById('daysFilterCheckbox');
                    var urlParams = new URLSearchParams(window.location.search);

                    // Lấy giá trị của tham số 'days_filter_checkbox' từ URL
                    var selectedDaysFilter = urlParams.get('days_filter_checkbox');

                    // Nếu có giá trị thì thiết lập giá trị cho select box
                    if (selectedDaysFilter !== null) {
                        daysFilterCheckbox.value = selectedDaysFilter;
                    }

                    // Thêm sự kiện lắng nghe cho select box để hiển thị hoặc ẩn các trường ngày
                    daysFilterCheckbox.addEventListener('change', function() {
                        var fromDateCheckbox = document.getElementById('fromDateCheckbox');
                        var toDateCheckbox = document.getElementById('toDateCheckbox');

                        if (this.value === "custom") {
                            fromDateCheckbox.style.display = "inline-block";
                            toDateCheckbox.style.display = "inline-block";
                        } else {
                            fromDateCheckbox.style.display = "none";
                            toDateCheckbox.style.display = "none";
                        }
                    });
                </script>
                
                <?php
                    if ($user_info['level'] == 'admin') {

                      $filter_date_checkbox = "";
                      if (isset($_GET['days_filter_checkbox'])) {
                          switch ($_GET['days_filter_checkbox']) {
                              case 'all':
                                  $filter_date_checkbox = ""; // Không áp dụng bất kỳ bộ lọc nào
                                  break;
                              case '1':
                                  $filter_date_checkbox = " AND DATE(action_timestamp) = CURDATE()";
                                  break;
                              case '7':
                                  $filter_date_checkbox = " AND DATE(action_timestamp) BETWEEN CURDATE() - INTERVAL 6 DAY AND CURDATE()";
                                  break;
                              case '30':
                                  $filter_date_checkbox = " AND DATE(action_timestamp) BETWEEN CURDATE() - INTERVAL 29 DAY AND CURDATE()";
                                  break;
                              case '90':
                                  $filter_date_checkbox = " AND DATE(action_timestamp) BETWEEN CURDATE() - INTERVAL 89 DAY AND CURDATE()";
                                  break;
                              case 'custom':
                                  if (isset($_GET['from_date_checkbox']) && isset($_GET['to_date_checkbox'])) {
                                      $from_date_checkbox = $_GET['from_date_checkbox'];
                                      $to_date_checkbox = $_GET['to_date_checkbox'];
                                      $filter_date_checkbox = " AND DATE(action_timestamp) BETWEEN '$from_date_checkbox' AND '$to_date_checkbox'";
                                  }
                                  break;
                          }
                      }
                      
                      // Bước 1: Xác định trang hiện tại
                      $checkboxPage = isset($_GET['checkboxPage']) ? intval($_GET['checkboxPage']) : 1;
                      $limit = 10; // số dòng trên mỗi trang
                      $offset = ($checkboxPage - 1) * $limit;

                      // Bước 2: Lấy tổng số dòng
                      $sql_count = "SELECT COUNT(*) AS total FROM prize_actions";
                      $result_count = $conn->query($sql_count);
                      $total_records = $result_count->fetch_assoc()['total'];
                      $total_pages = ceil($total_records / $limit);

                      // Bước 3: Lấy dữ liệu dựa vào trang hiện tại và giới hạn số dòng
                      $phone_search_checkbox = isset($_GET['phone_search_checkbox']) ? $_GET['phone_search_checkbox'] : '';

                      $sql = "SELECT prize_actions.*, quay_thuong.result, quay_thuong.customer_id, customers.fullname AS customer_name, customers.phone AS customer_phone
                              FROM prize_actions
                              JOIN quay_thuong ON prize_actions.prize_id = quay_thuong.id
                              JOIN customers ON quay_thuong.customer_id = customers.id
                              WHERE (customers.phone LIKE '%$phone_search_checkbox%' OR customers.fullname LIKE '%$phone_search_checkbox%') $filter_date_checkbox
                              LIMIT $offset, $limit";
                      $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                          echo '<table class="table table-striped">';
                          echo '<thead>';
                          echo '<tr>';
                          echo '<th scope="col">Tên Người Dùng</th>';
                          echo '<th scope="col">Số Điện Thoại</th>';
                          echo '<th scope="col">Hành Động</th>';
                          echo '<th scope="col">Kết Quả</th>';
                          echo '<th scope="col">Thời Gian</th>';
                          echo '<th scope="col">Người Dùng</th>';
                          echo '<th scope="col">Cấp Độ</th>';
                          echo '</tr>';
                          echo '</thead>';
                          echo '<tbody>';
                          while ($row = $result->fetch_assoc()) {
                              echo '<tr>';
                              echo '<td>' . $row['customer_name'] . '</td>';
                              echo '<td>' . $row['customer_phone'] . '</td>';
                              echo '<td>' . $row['action_type'] . '</td>';
                              echo '<td>' . $row['result'] . '</td>';
                              echo '<td>' . $row['action_timestamp'] . '</td>';
                              echo '<td>' . $row['entered_by'] . '</td>';
                              echo '<td>' . $row['admin_level'] . '</td>';
                              echo '</tr>';
                          }
                          echo '</tbody>';
                          echo '</table>';

                          // Bước 4: Hiển thị các nút phân trang
                          echo '<nav>';
                          echo "<div>Trang: </div>";
                          echo '<ul class="pagination">';

                          $prevPage = $checkboxPage - 1;
                          $nextPage = $checkboxPage + 1;

                          // Nút "Trước" - Disabled khi đang ở trang đầu tiên
                          if ($checkboxPage == 1) {
                              echo '<li class="page-item disabled"><a class="page-link" href="#">Trước</a></li>';
                          } else {
                              echo '<li class="page-item"><a class="page-link" href="index.php?checkboxPage=' . $prevPage . 
                                  '&view=checkbox&phone_search_checkbox=' . urlencode($phone_search_checkbox) . '&days_filter_checkbox=' . urlencode($_GET['days_filter_checkbox']) . '">Trước</a></li>';
                          }

                          // Số trang cụ thể
                          for ($i = max(1, $checkboxPage - 2); $i <= min($total_pages, $checkboxPage + 2); $i++) {
                              if ($i == $checkboxPage) {
                                  echo '<li class="page-item active"><a class="page-link" href="#">' . $i . '</a></li>';
                              } else {
                                  echo '<li class="page-item"><a class="page-link" href="index.php?checkboxPage=' . $i . 
                                      '&view=checkbox&phone_search_checkbox=' . urlencode($phone_search_checkbox) . '&days_filter_checkbox=' . urlencode($_GET['days_filter_checkbox']) . '">' . $i . '</a></li>';
                              }
                          }

                          // Nút "Tiếp" - Disabled khi đang ở trang cuối
                          if ($checkboxPage == $total_pages) {
                              echo '<li class="page-item disabled"><a class="page-link" href="#">Tiếp</a></li>';
                          } else {
                              echo '<li class="page-item"><a class="page-link" href="index.php?checkboxPage=' . $nextPage . 
                                  '&view=checkbox&phone_search_checkbox=' . urlencode($phone_search_checkbox) . '&days_filter_checkbox=' . urlencode($_GET['days_filter_checkbox']) . '">Tiếp</a></li>';
                          }

                          echo '</ul>';
                          echo '</nav>';
                      } else {
                          echo 'Không có lịch sử checkbox.';

                        // Kiểm tra nếu trang hiện tại lớn hơn 1 thì hiển thị nút "Trước"
                        if($checkboxPage > 1) {
                            $previousPage = $checkboxPage - 1;
                            $previousUrl = 'index.php?checkboxPage=' . $previousPage . 
                                          '&view=checkbox&phone_search_checkbox=' . urlencode($phone_search_checkbox) . 
                                          '&days_filter_checkbox=' . urlencode($_GET['days_filter_checkbox']);

                            echo '<div class="mt-3">';
                            echo '<a href="' . $previousUrl . '" class="page-link">Trước</a>';
                            echo '</div>';
                        }
                      }
                    }
                  ?>
                  </div>
                  
                 <?php $selected_option = isset($_GET['days_filter_create']) ? $_GET['days_filter_create'] : 'all'; ?>
                  <div id="createHistoryTable" class="history-table">
                    <!-- Lịch Sử Tạo Tài Khoản -->
                    <h1>Lịch Sử Tạo Tài Khoản</h1>
                    
                    <div class="mt-4">
                        <form action="" method="get" class="form-inline">
                            <input type="hidden" name="view" value="create">

                            <label class="mr-2" id="daysFilterCreateLabel">Chọn thời gian:</label>
                            <select name="days_filter_create" id="daysFilterCreate" class="form-control mr-2">
                                <option value="all" <?php echo ($selected_option == 'all') ? 'selected' : ''; ?>>Tất cả</option>
                                <option value="1" <?php echo ($selected_option == '1') ? 'selected' : ''; ?>>1 ngày</option>
                                <option value="7" <?php echo ($selected_option == '7') ? 'selected' : ''; ?>>7 ngày</option>
                                <option value="30" <?php echo ($selected_option == '30') ? 'selected' : ''; ?>>30 ngày</option>
                                <option value="90" <?php echo ($selected_option == '90') ? 'selected' : ''; ?>>90 ngày</option>
                                <option value="custom" <?php echo ($selected_option == 'custom') ? 'selected' : ''; ?>>Chọn theo lịch</option>
                            </select>

                            <input type="date" name="from_date_create" id="fromDateCreate" class="form-control mr-2" style="display: none;">
                            <input type="date" name="to_date_create" id="toDateCreate" class="form-control mr-2" style="display: none;">

                            <input type="submit" value="Lọc" class="btn btn-primary">
                        </form>
                    </div>
                    
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var daysFilterCreate = document.getElementById('daysFilterCreate');
                            var fromDateCreate = document.getElementById('fromDateCreate');
                            var toDateCreate = document.getElementById('toDateCreate');

                            function updateDisplay() {
                                if (daysFilterCreate.value === "custom") {
                                    fromDateCreate.style.display = "inline-block";
                                    toDateCreate.style.display = "inline-block";
                                } else {
                                    fromDateCreate.style.display = "none";
                                    toDateCreate.style.display = "none";
                                }
                            }

                            daysFilterCreate.addEventListener('change', updateDisplay);
                            updateDisplay(); // Gọi ngay lập tức khi trang tải xong để hiển thị đúng trạng thái
                        });
                    </script>
                    
                    <?php
                    if ($user_info['level'] == 'admin') {
                      
                        $filter_date_create = "";
                          if (isset($_GET['days_filter_create'])) {
                              switch ($_GET['days_filter_create']) {
                                  case 'all':
                                      $filter_date_create = ""; // Không áp dụng bất kỳ bộ lọc nào
                                      break;
                                  case '1':
                                      $filter_date_create = " AND DATE(created_at) = CURDATE()";
                                      break;
                                  case '7':
                                      $filter_date_create = " AND DATE(created_at) BETWEEN CURDATE() - INTERVAL 6 DAY AND CURDATE()";
                                      break;
                                  case '30':
                                      $filter_date_create = " AND DATE(created_at) BETWEEN CURDATE() - INTERVAL 29 DAY AND CURDATE()";
                                      break;
                                  case '90':
                                      $filter_date_create = " AND DATE(created_at) BETWEEN CURDATE() - INTERVAL 89 DAY AND CURDATE()";
                                      break;
                                  case 'custom':
                                      if (isset($_GET['from_date_create']) && isset($_GET['to_date_create'])) {
                                          $from_date_create = $_GET['from_date_create'];
                                          $to_date_create = $_GET['to_date_create'];
                                          $filter_date_create = " AND DATE(created_at) BETWEEN '$from_date_create' AND '$to_date_create'";
                                      }
                                      break;
                              }
                          }

                        $sql_history_display .= $filter_date_create;
                      
                        $history_current_page = isset($_GET['history_page']) ? $_GET['history_page'] : 1;
                        $history_items_per_page = 10;
                        $history_offset = ($history_current_page - 1) * $history_items_per_page;

                        $sql_history_display = "..."; // câu truy vấn của bạn như trước
                        $sql_history_display .= " LIMIT $history_items_per_page OFFSET $history_offset";

                        $sql_count = "SELECT COUNT(*) as total FROM account_creation_history";
                        $result_count = $conn->query($sql_count);
                        $row_count = $result_count->fetch_assoc();
                        $total_items = $row_count['total'];

                        $total_pages = ceil($total_items / $history_items_per_page);

                        $sql_history_display = "SELECT a.fullname as admin_name, a.level as admin_level, c.fullname as created_name, c.level as created_level, c.phone as created_phone, h.created_at 
                                FROM account_creation_history h 
                                JOIN customers a ON h.admin_id = a.id 
                                JOIN customers c ON h.created_account_id = c.id 
                                WHERE 1=1 " . $filter_date_create . 
                                " ORDER BY h.created_at DESC";
                        $result_history = $conn->query($sql_history_display);

                        if ($result_history->num_rows > 0) {
                            echo "<table>";
                            echo "<tr><th>Tên Admin</th>
                            <th>Cấp Độ Admin</th>
                            <th>Tên Đã tạo</th>
                            <th>Cấp Độ Đã tạo</th>
                            <th>SĐT Đã tạo</th>
                            <th>Thời gian</th>
                            </tr>";
                            while ($row = $result_history->fetch_assoc()) {
                                echo "<tr>
                                <td>" . $row['admin_name'] . "</td>
                                <td>" . $row['admin_level'] . "</td>
                                <td>" . $row['created_name'] . "</td>
                                <td>" . $row['created_level'] . "</td>
                                <td>" . $row['created_phone'] . "</td>
                                <td>" . $row['created_at'] . "</td>
                                </tr>";
                            }
                            echo "</table>";
                            // Phân trang
                            echo '<nav aria-label="Page navigation">';
                            echo "<div>Trang: </div>";
                            echo '<ul class="pagination">';

                            // Thêm nút "Previous" (Nếu không phải là trang đầu tiên)
                            if ($history_current_page > 1) {
                                echo '<li class="page-item"><a class="page-link" href="?history_page=' . ($history_current_page - 1) . '">Trước</a></li>';
                            }

                            for ($i = 1; $i <= $total_pages; $i++) {
                                if ($i == $history_current_page) {
                                    echo '<li class="page-item active"><a class="page-link" href="#">' . $i . '</a></li>';
                                } else {
                                    echo '<li class="page-item"><a class="page-link" href="?history_page=' . $i . '">' . $i . '</a></li>';
                                }
                            }

                            // Thêm nút "Next" (Nếu không phải là trang cuối cùng)
                            if ($history_current_page < $total_pages) {
                                echo '<li class="page-item"><a class="page-link" href="?history_page=' . ($history_current_page + 1) . '">Tiếp</a></li>';
                            }

                            echo '</ul>';
                            echo '</nav>';
                        } else {
                            echo "Chưa có lịch sử tạo tài khoản!";
                        }
                    }
                    ?>
                </div>
                </div>
                <?php } //Kết thúc phần bảng Lịch sử ?>
              <script>
                $(document).ready(function() {
                    var currentView = "<?php echo $view; ?>";

                    if (currentView == 'redeem') {
                        $('#showRedeemHistory').hide();
                        $('#showCheckboxHistory').show();
                        $('#showCreateHistory').show();
                        $('#redeemHistoryTable').show();
                        $('#checkboxHistoryTable').hide();
                        $('#createHistoryTable').hide();
                    } else if (currentView == 'checkbox') {
                        $('#showRedeemHistory').show();
                        $('#showCheckboxHistory').hide();
                        $('#showCreateHistory').show();
                        $('#redeemHistoryTable').hide();
                        $('#checkboxHistoryTable').show();
                        $('#createHistoryTable').hide();
                    } else if (currentView == 'create') {
                        $('#showRedeemHistory').show();
                        $('#showCheckboxHistory').show();
                        $('#showCreateHistory').hide();
                        $('#redeemHistoryTable').hide();
                        $('#checkboxHistoryTable').hide();
                        $('#createHistoryTable').show();
                    }

                    $('#showRedeemHistory').click(function() {
                        $(this).hide();
                        $('#showCheckboxHistory').show();
                        $('#showCreateHistory').show();
                        $('#redeemHistoryTable').show();
                        $('#checkboxHistoryTable').hide();
                        $('#createHistoryTable').hide();
                    });

                    $('#showCheckboxHistory').click(function() {
                        $(this).hide();
                        $('#showRedeemHistory').show();
                        $('#showCreateHistory').show();
                        $('#redeemHistoryTable').hide();
                        $('#checkboxHistoryTable').show();
                        $('#createHistoryTable').hide();
                    });

                    $('#showCreateHistory').click(function() {
                        $(this).hide();
                        $('#showRedeemHistory').show();
                        $('#showCheckboxHistory').show();
                        $('#redeemHistoryTable').hide();
                        $('#checkboxHistoryTable').hide();
                        $('#createHistoryTable').show();
                    });
                });
            </script>  
        <!--Kết thúc phần bảng Lịch sử -->  
            <?php if (isset($_SESSION['message'])) {
                    echo "<div class='alert-message'>" . $_SESSION['message'] . "</div>";
                    unset($_SESSION['message']); // Xoá session message sau khi đã hiển thị
                }
             ?>      
            <div class="container vip-info  mt-5">
              
               <form method="post" action="">
                      <div class="form-group">
                          <label for="fullname">Tên đầy đủ:</label>
                          <input type="text" id="fullname" name="fullname" class="form-control" required>
                      </div>

                      <div class="form-group">
                          <label for="phone">Số điện thoại:</label>
                          <input type="text" id="phone" name="phone" class="form-control" required>
                      </div>
                    
                      <div class="form-group">
                         <label for="password">Mật khẩu:</label>
                         <input type="password" id="password" name="password" class="form-control" required>
                      </div>

                      <div class="form-group">
                          <label for="level">Cấp độ:</label>
                          <select id="level" name="level" class="form-control" required <?php echo ($user_info['level'] == 'admin1') ? 'disabled' : ''; ?>>
                              <?php if ($user_info['level'] == 'admin') { ?>
                                  <option value="admin">Admin</option>
                                  <option value="admin1">Admin1</option>
                              <?php } ?>
                              <option value="Vip1" selected>Vip1</option>
                          </select>
                          <?php if ($user_info['level'] == 'admin1') { ?>
                              <!-- Thêm trường ẩn để gửi giá trị mặc định là 'Vip1' khi select box bị disabled -->
                              <input type="hidden" name="level" value="Vip1">
                          <?php } ?>
                      </div>

                      <div class="form-group">
                          <label for="points">Điểm:</label>
                          <input type="number" id="points" name="points" class="form-control" value="0" readonly required>
                      </div>

                      <button type="submit" name="create_account" class="btn btn-primary">Duyệt</button>
                  </form>
              
            </div>

              <?php
                // Xử lý dữ liệu khi người dùng gửi thông tin
                if (isset($_POST['create_account'])) {
                    $fullname = $_POST['fullname'];
                    $phone = $_POST['phone'];
                    $level = $_POST['level'];
                    $points = $_POST['points'];
                    $password = md5($_POST['password']);  // Mã hóa mật khẩu trước khi lưu

                    // Kiểm tra số điện thoại đã tồn tại trong CSDL
                    $sql_check_phone = "SELECT phone FROM customers WHERE phone = '$phone'";
                    $result = $conn->query($sql_check_phone);

                    if ($result->num_rows > 0) {
                        // Số điện thoại đã tồn tại
                        $_SESSION['message'] = "Tài khoản với số điện thoại này đã tồn tại!";
                    } else {
                        // Tiến hành tạo tài khoản
                        $sql_insert = "INSERT INTO customers (fullname, phone, level, points, password) VALUES ('$fullname', '$phone', '$level', $points, '$password')";

                        if ($conn->query($sql_insert) === TRUE) {
                          $last_id = $conn->insert_id; // Lấy ID của tài khoản vừa được tạo

                          $current_time = new DateTime(null, new DateTimeZone('Asia/Ho_Chi_Minh'));
                          $created_at = $current_time->format('Y-m-d H:i:s');

                            // Ghi lại vào bảng account_creation_history
                            $sql_history = "INSERT INTO account_creation_history (admin_id, created_account_id, created_at) VALUES (".$user_info['id'].", $last_id, '$created_at')";
                            $conn->query($sql_history);
                          
                            $_SESSION['message'] = "Tài khoản mới đã được tạo!";
                        } else {
                            $_SESSION['message'] = "Lỗi: " . $sql_insert . " - " . $conn->error;
                        }
                    }
                }
            ?>
            <!-- Biểu mẫu lọc theo cấp độ VIP -->
            <div class="vip-info  mt-5">
              <form id="filter-form" method="get" action="">
                  <label for="vip_filter">Lọc theo cấp độ VIP:</label>
                  <select id="vip_filter" name="vip_filter" onchange="submitFilter()">
                      <option value="all" <?php if(isset($_GET['vip_filter']) && $_GET['vip_filter'] == 'all') echo 'selected'; ?>>Tất cả</option>
                      <option value="admin1" <?php if(isset($_GET['vip_filter']) && $_GET['vip_filter'] == 'admin1') echo 'selected'; ?>>admin1</option>
                      <option value="Vip1" <?php if(isset($_GET['vip_filter']) && $_GET['vip_filter'] == 'Vip1') echo 'selected'; ?>>Vip1</option>
                      <option value="Vip2" <?php if(isset($_GET['vip_filter']) && $_GET['vip_filter'] == 'Vip2') echo 'selected'; ?>>Vip2</option>
                      <option value="Vip3" <?php if(isset($_GET['vip_filter']) && $_GET['vip_filter'] == 'Vip3') echo 'selected'; ?>>Vip3</option>
                      <option value="Vip4" <?php if(isset($_GET['vip_filter']) && $_GET['vip_filter'] == 'Vip4') echo 'selected'; ?>>Vip4</option>
                      <option value="Vip5" <?php if(isset($_GET['vip_filter']) && $_GET['vip_filter'] == 'Vip5') echo 'selected'; ?>>Vip5</option>
                  </select>
                  <input type="hidden" name="page" value="1"> <!-- Reset page to 1 when filtering -->
              </form>

              <!-- Biểu mẫu tìm kiếm khách hàng VIP theo số điện thoại -->
              <form method="post" action="">
                  <label for="phone_search">Tìm khách hàng theo số điện thoại:</label>
                  <input type="text" id="phone_search" name="phone_search" placeholder="Nhập số điện thoại">
                  <button type="submit" class="btn btn-primary">Tìm kiếm</button>
              </form>
            </div>
            
            <div class="vip-info">      
            <div id="messageBox" style="margin: 10px 0px !important;"></div>
                  
            <?php
              if(isset($_SESSION['admin_message'])) {
                  echo "<div class='alert alert-success'>" . $_SESSION['admin_message'] . "</div>";
                  unset($_SESSION['admin_message']); // Xóa thông báo sau khi đã hiển thị
              }
              if(isset($_SESSION['admin_error'])) {
                  echo "<div class='alert alert-danger'>" . $_SESSION['admin_error'] . "</div>";
                  unset($_SESSION['admin_error']); // Xóa thông báo lỗi sau khi đã hiển thị
              }
              ?>
              <h2>Danh sách khách VIP từ Vip1 đến Vip5</h2>

              <?php
              if(isset($user_info['level'])) {
                  if($user_info['level'] == 'admin' || 
                    ($user_info['level'] == 'admin1' && isset($_POST['phone_search']) && !empty($_POST['phone_search']))) {
               ?>
                      
                      <table cellpadding="0" cellspacing="0" border="0" class="luckywheel centered-content">
                          <!-- ... (Table headers) ... -->
                          <tr>
                              <th style="width: 10% !important;">Full Name</th>
                              <th style="width: 13% !important;">Phone</th>
                              <th style="width: 7% !important;">Level</th>
                              <th style="width: 7% !important;">Điểm</th>
                              <th style="width: 30% !important;">Nhập mã mua hàng</th>
                              <th style="width: 20% !important;">Kết quả</th>
                              <th style="width: 13% !important;">Đã nhận</th>
                          </tr>

                          <?php
                  // Xử lý tìm kiếm khách hàng VIP theo số điện thoại
                  if (isset($_POST['phone_search']) && !empty($_POST['phone_search'])) {
                      $phone_search = $_POST['phone_search'];
                      $filter_sql = " AND phone LIKE '%$phone_search%'";
                  } else {
                      $filter_sql = "";
                  }

                  // Filter by VIP level
                  $vip_filter = isset($_GET['vip_filter']) ? $_GET['vip_filter'] : 'all';
                  if ($vip_filter != 'all') {
                      $filter_sql .= " AND level = '$vip_filter'";
                  }

                  // Pagination settings
                  $items_per_page = 999;
                  $current_page = isset($_GET['page']) ? $_GET['page'] : 1;
                  $offset = ($current_page - 1) * $items_per_page;

                  // Fetch and display VIP customers based on filter and phone search
                  $sql = "SELECT * FROM customers WHERE (level LIKE 'Vip%' OR level = 'admin1') AND level <= 'Vip5'$filter_sql LIMIT $offset, $items_per_page";
                  $result = $conn->query($sql);

                  if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["fullname"] . "</td>";
                        echo "<td>" . $row["phone"] . "</td>";
                        echo "<td>" . $row["level"] . "</td>";
                        echo "<td>" . $row["points"] . "</td>";
                        echo "<td>
                                <form method='post' action=''>
                                   <input type='hidden' name='customer_id' value='" . $row["id"] . "'>
                                   <input type='text' name='admin_redeem_code'>
                                   <input type='submit' class='btn btn-primary' name='admin_redeem_submit' value='Nhập mã'>
                                 </form>
                             </td>";

                        // Lấy kết quả quay thưởng từ bảng quay_thuong
                          $customerId = $row["id"];
                          $prize_query = "SELECT result, is_received, id FROM quay_thuong WHERE customer_id = $customerId ORDER BY quay_lan DESC LIMIT 100";
                          $prize_result = $conn->query($prize_query);

                          if ($prize_result && $prize_result->num_rows > 0) {
                              echo "<td>";
                              $checkbox_data = "";  // chuỗi để lưu checkbox
                              while ($prize_row = $prize_result->fetch_assoc()) {
                                $result_id = 'prize-result-' . $prize_row["id"];
                                $is_received = $prize_row['is_received'];

                                // Xác định kiểu màu dựa trên trạng thái đã nhận
                                $color_style = $is_received ? 'style="color: red;"' : '';

                                echo "<span id='$result_id' $color_style>Nhận: " . $prize_row["result"] . "</span><br>";

                                // Lưu checkbox vào chuỗi
                                $is_checked = $is_received ? 'checked' : '';
                                $checkbox_data .= "<input type='checkbox' class='prize-received' data-result-id='$result_id' data-prize-id='" . $prize_row['id'] . "' $is_checked> Đã nhận<br>";
                              }
                              echo "</td>";
                              // Hiển thị cột mới cho checkbox
                              echo "<td>$checkbox_data</td>";
                          } else {
                              echo "<td>Chưa quay thưởng</td>";
                              echo "<td></td>";   // Trường hợp không có kết quả quay thưởng, thêm một cột trống
                        }

                        echo "</tr>";
                      }
                  } else {
                      echo "<tr><td colspan='5'>Không tìm thấy khách hàng VIP.</td></tr>";
                  }
                  ?>
              </table>
                      <?php
                  } else {
                      echo "Bạn không có quyền xem thông tin này.";
                  }
              } else {
                  echo "Lỗi: Không thể xác định quyền của người dùng.";
              }
              ?>
              <?php
    $sql_count = "SELECT COUNT(*) AS total FROM customers WHERE level LIKE 'Vip%' AND level <= 'Vip5'$filter_sql";
    $result_count = $conn->query($sql_count);
    $row_count = $result_count->fetch_assoc();
    $total_pages = ceil($row_count['total'] / $items_per_page);

   echo "<div>Trang: </div>";
    echo "<ul class='pagination'>";

    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) { // Highlight the current page
            echo "<li class='page-item active'><a class='page-link' href='?page=$i&vip_filter=$vip_filter'>$i</a></li>";
        } else {
            echo "<li class='page-item'><a class='page-link' href='?page=$i&vip_filter=$vip_filter'>$i</a></li>";
        }
    }

    echo "</ul>";

    ?>
          </div>
       </div>
    <!-- Pagination Links -->
<?php } ?>

<script>
    function submitFilter() {
        document.getElementById("filter-form").submit();
    }
</script>


        <?php } ?>
    </div>

    <?php
    $conn->close();
    $wpConn->close();
    ?>
    <script>
        window.onload = function() {
            // Kiểm tra giá trị của cột "power_table_hidden" từ PHP và ẩn bảng nếu cần
            <?php
            if ($loggedin && $user_info['power_table_hidden'] == 1) {
                echo 'hidePowerTable();';
            }
            ?>
        };

        function hidePowerTable() {
            var powerTable = document.getElementById('power_table');
            if (powerTable) {
                powerTable.style.display = 'none';
            }
        }
    </script>
    <script>
      $(document).ready(function() {
          $(document).on('change', '.prize-received', function() {
              const prizeId = $(this).data('prize-id');
              const isReceived = $(this).is(':checked');

              const userConfirm = confirm("Bạn có chắc chắn muốn thay đổi trạng thái của giải thưởng này không?");
              if (!userConfirm) {
                  $(this).prop('checked', !isReceived);
                  return;
              }

              if (isReceived) {
                  $(`#${prizeId}`).css('color', 'red');
              } else {
                  $(`#${prizeId}`).css('color', 'black');
              }

              $.ajax({
                  type: 'POST',
                  url: './update_prize_received.php',
                  data: {
                      prizeId: prizeId,
                      isReceived: isReceived ? 1 : 0
                  },
                  success: function(response) {
                      $('#messageBox').removeClass('alert-success alert-danger');
                      const alertClass = isReceived ? 'alert-success' : 'alert-danger';
                      $('#messageBox').addClass(`alert ${alertClass}`).text(response).show();
                      
                      setTimeout(() => {
                          $('#messageBox').fadeOut();
                          location.reload(); // Tải lại trang tại đây
                      }, 1000);
                  }
              });
          });
      });
    </script>
    <script>
        // Ngăn chặn F12 (Công cụ phát triển)
          document.onkeydown = function (e) {
              if (e.keyCode == 123) {
                  return false;
              }
              if (e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) {
                  return false;
              }
              if (e.ctrlKey && e.shiftKey && e.keyCode == 'C'.charCodeAt(0)) {
                  return false;
              }
              if (e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) {
                  return false;
              }
              if (e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) {
                  return false;
              }
          };

          // Ngăn chặn click chuột phải
          document.oncontextmenu = function (e) {
              e.preventDefault(); // Ngăn chặn menu chuột phải mặc định
              return false;
          }
    </script>
</body>
</html>