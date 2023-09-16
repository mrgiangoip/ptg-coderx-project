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
        </div>
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
            <!-- Biểu mẫu lọc theo cấp độ VIP -->
            <div class="container vip-info  mt-5">
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
            <div class="container vip-info  mt-5">
                  <?php
                       if ($user_info['level'] == 'admin') {
                        $records_per_page = 20;

                        // Lấy tổng số bản ghi
                        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM redeem_history WHERE admin_level IN (?, ?)");
                        $stmt->bind_param("ss", $level1, $level2);
                        $level1 = 'admin';
                        $level2 = 'admin1';
                        $stmt->execute();
                        $resultCount = $stmt->get_result();
                        $rowTotal = $resultCount->fetch_assoc();
                        $total_pages = ceil($rowTotal['total'] / $records_per_page);

                        $current_page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? intval($_GET['page']) : 1;
                        $current_page = max(1, min($total_pages, $current_page));

                        $start_from = ($current_page - 1) * $records_per_page;

                        // Lấy dữ liệu
                        $stmt = $conn->prepare("SELECT r.id, r.customer_id, r.redeem_code, r.redeemed_at, r.entered_by, r.admin_level, c.fullname
                        FROM redeem_history r
                        JOIN customers c ON r.customer_id = c.id
                        WHERE r.admin_level IN (?, ?)
                        LIMIT ?, ?");
                        $stmt->bind_param("ssii", $level1, $level2, $start_from, $records_per_page);
                        $stmt->execute();
                        $resultRedeemHistory = $stmt->get_result();

                        if ($resultRedeemHistory->num_rows > 0) {
                            echo "<h2>Lịch sử mã mua hàng:</h2>";
                            echo '<table class="table table-striped">';
                            echo "<thead><tr><th>Tên Khách Hàng</th><th>Mã Mua Hàng</th><th>Thời Gian Nhập</th><th>Tên Người Nhập</th><th>Cấp Độ</th></tr></thead>";
                            echo "<tbody>";

                            while ($row = $resultRedeemHistory->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row["fullname"] . "</td>";
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
                            for ($page = 1; $page <= $total_pages; $page++) {
                                $activeClass = $page == $current_page ? "active" : "";
                                echo "<li class='page-item $activeClass'><a class='page-link' href='./index.php?page=$page'>$page</a></li> ";
                            }
                            echo "</ul>";
                        } else {
                            echo "Không có dữ liệu lịch sử mã mua hàng.";
                        }
                       } //kết thúc xét admin
                    ?>
            </div>
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
             <div class="vip-info">
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
          </div>
    <!-- Pagination Links -->
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
                      }, 500);
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