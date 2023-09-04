<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Danh sách khách VIP</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://gomhang.vn/wp-content/plugins/vip-wheel-plugin/style.css" type="text/css" />
        <link rel="stylesheet" href="https://gomhang.vn/wp-content/plugins/vip-wheel-plugin/main.css" type="text/css" />
        <script type="text/javascript" src="https://gomhang.vn/wp-content/plugins/vip-wheel-plugin/Winwheel.js"></script>
        <script src="http://cdnjs.cloudflare.com/ajax/libs/gsap/latest/TweenMax.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
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
    $loggedin = false;
    $user_info = null;

    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
        $loggedin = true;
        $user_info = $_SESSION['user_info'];
    }

    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = md5($_POST['password']); // Assuming passwords are stored in MD5

        $sql = "SELECT * FROM customers WHERE phone = '$username' AND password = '$password'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $loggedin = true;
            $user_info = $result->fetch_assoc();
        }
    }

    // Logout
    if (isset($_GET['logout'])) {
        session_destroy();

        // Xóa lịch sử trình duyệt và chuyển hướng người dùng đến trang đăng nhập
        echo '<script>window.location.replace("./index.php");</script>';
        exit;
    }

    // Lưu lại thông tin người dùng vào session sau khi đăng nhập
    if ($loggedin) {
        $_SESSION['loggedin'] = true;
        $_SESSION['user_info'] = $user_info;
    }
    ?>
    <div class="container mt-5">
        <?php if (!$loggedin) { ?>
            <div class="login-form">
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
        <?php } else { ?>
            <div class="vip-info">
                <h1>Thông tin khách VIP</h1>
                <table>
                    <tr>
                        <th>Full Name</th>
                        <th>Phone</th>
                        <th>Level</th>
                        <th>Points</th>
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
                        echo "<td>" . $user_info['quay_count'] . " lượt</td>";

                        echo "</tr>";
                    }
                    ?>
                </table>
                <a href="?logout=true" class="btn btn-danger">Đăng xuất</a>
            </div>
            <?php if ($user_info['level'] >= 'Vip2' && $user_info['level'] <= 'Vip5') { ?>
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
        <script>
            // Create new wheel object specifying the parameters at creation time.
            let theWheel = new Winwheel({
                'outerRadius'     : 212,        // Set outer radius so wheel fits inside the background.
                'innerRadius'     : 75,         // Make wheel hollow so segments don't go all way to center.
                'textFontSize'    : 24,         // Set default font size for the segments.
                'textOrientation' : 'vertical', // Make text vertial so goes down from the outside of wheel.
                'textAlignment'   : 'outer',    // Align text to outside of wheel.
                'numSegments'     : 12,         // Specify number of segments.
                'segments'        :             // Define segments including colour and text.
                [                               // font size and test colour overridden on backrupt segments.
                   {'fillStyle' : '#ee1c24', 'text' : 'Cam'},
                   {'fillStyle' : '#3cb878', 'text' : 'Quýt'},
                   {'fillStyle' : '#f6989d', 'text' : 'Mít'},
                   {'fillStyle' : '#00aef0', 'text' : 'Dừa'},
                   {'fillStyle' : '#f26522', 'text' : 'Bưởi'},
                   {'fillStyle' : '#00aef0', 'text' : 'Dứa'},
                   {'fillStyle' : '#e70697', 'text' : 'Táo'},
                   {'fillStyle' : '#000000', 'text' : 'Chúc may mắn', 'textFontSize' : 12, 'textFillStyle' : '#ffffff'},
                   {'fillStyle' : '#a186be', 'text' : 'Na'},
                   {'fillStyle' : '#fff200', 'text' : 'Xoài'},
                   {'fillStyle' : '#00aef0', 'text' : 'Đào'},
                   {'fillStyle' : '#ffffff', 'text' : 'Mất lượt', 'textFontSize' : 12}
                ],
                'animation' :           // Specify the animation to use.
                {
                    'type'     : 'spinToStop',
                    'duration' : 10,    // Duration in seconds.
                    'spins'    : 3,     // Default number of complete spins.
                    'callbackFinished' : alertPrize,
                    'callbackSound'    : playSound,   // Function to call when the tick sound is to be triggered.
                    'soundTrigger'     : 'pin'        // Specify pins are to trigger the sound, the other option is 'segment'.
                },
                'pins' :                // Turn pins on.
                {
                    'number'     : 24,
                    'fillStyle'  : 'silver',
                    'outerRadius': 4,
                }
            });

            // Loads the tick audio sound in to an audio object.
            let audio = new Audio('https://gomhang.vn/wp-content/plugins/vip-wheel-plugin/tick.mp3');

            // This function is called when the sound is to be played.
            function playSound()
            {
                // Stop and rewind the sound if it already happens to be playing.
                audio.pause();
                audio.currentTime = 0;

                // Play the sound.
                audio.play();
            }

            // Vars used by the code in this page to do power controls.
            let wheelPower    = 0;
            let wheelSpinning = false;

            // -------------------------------------------------------
            // Function to handle the onClick on the power buttons.
            // -------------------------------------------------------
            function powerSelected(powerLevel)
            {
                // Ensure that power can't be changed while wheel is spinning.
                if (wheelSpinning == false) {
                    // Reset all to grey incase this is not the first time the user has selected the power.
                    document.getElementById('pw1').className = "";
                    document.getElementById('pw2').className = "";
                    document.getElementById('pw3').className = "";

                    // Now light up all cells below-and-including the one selected by changing the class.
                    if (powerLevel >= 1) {
                        document.getElementById('pw1').className = "pw1";
                    }

                    if (powerLevel >= 2) {
                        document.getElementById('pw2').className = "pw2";
                    }

                    if (powerLevel >= 3) {
                        document.getElementById('pw3').className = "pw3";
                    }

                    // Set wheelPower var used when spin button is clicked.
                    wheelPower = powerLevel;

                    // Light up the spin button by changing it's source image and adding a clickable class to it.
                    document.getElementById('spin_button').src = "https://gomhang.vn/wp-content/plugins/vip-wheel-plugin/spin_on.png";
                    document.getElementById('spin_button').className = "clickable";
                }
            }

            // -------------------------------------------------------
            // Click handler for spin button.
            // -------------------------------------------------------
            function startSpin()
            {
                // Ensure that spinning can't be clicked again while already running.
                if (wheelSpinning == false) {
                    // Based on the power level selected adjust the number of spins for the wheel, the more times is has
                    // to rotate with the duration of the animation the quicker the wheel spins.
                    if (wheelPower == 1) {
                        theWheel.animation.spins = 3;
                    } else if (wheelPower == 2) {
                        theWheel.animation.spins = 6;
                    } else if (wheelPower == 3) {
                        theWheel.animation.spins = 10;
                    }

                    // Disable the spin button so can't click again while wheel is spinning.
                    document.getElementById('spin_button').src       = "https://gomhang.vn/wp-content/plugins/vip-wheel-plugin/spin_off.png";
                    document.getElementById('spin_button').className = "";

                    // Begin the spin animation by calling startAnimation on the wheel object.
                    theWheel.startAnimation();

                    // Set to true so that power can't be changed and spin button re-enabled during
                    // the current animation. The user will have to reset before spinning again.
                    wheelSpinning = true;
                    
                }
            }
              // -------------------------------------------------------
            // Function for reset button.
            // -------------------------------------------------------
            function resetWheel()
            {
                theWheel.stopAnimation(false);  // Stop the animation, false as param so does not call callback function.
                theWheel.rotationAngle = 0;     // Re-set the wheel angle to 0 degrees.
                theWheel.draw();                // Call draw to render changes to the wheel.

                document.getElementById('pw1').className = "";  // Remove all colours from the power level indicators.
                document.getElementById('pw2').className = "";
                document.getElementById('pw3').className = "";

                wheelSpinning = false;          // Reset to false to power buttons and spin can be clicked again.
            }

            // -------------------------------------------------------
            // Called when the spin animation has finished by the callback feature of the wheel because I specified callback in the parameters.
             // -------------------------------------------------------
            function alertPrize(indicatedSegment) {
                    let prizeResult = indicatedSegment.text;
                    let deductSpin = false;  // Khởi tạo biến deductSpin

                    if (indicatedSegment.text == 'Mất lượt') {
                        alert('Xin lỗi bạn đã bị mất lượt.');
                        deductSpin = true; // Đặt deductSpin thành true để trừ lượt quay
                    } else if (indicatedSegment.text == 'Chúc may mắn') {
                        alert('Chúc bạn may mắn lần sau.');
                        deductSpin = true; // Đặt deductSpin thành true để trừ lượt quay
                    } else {
                        alert("Chúc mừng bạn đã được giảm giá " + prizeResult);
                    }

                    // Gửi yêu cầu AJAX để cập nhật kết quả quay thưởng và/hoặc trừ lượt quay vào cơ sở dữ liệu
                    $.ajax({
                        type: "POST",
                        url: "./update_prize.php",
                        data: {
                            prizeResult: prizeResult,
                            deductSpin: deductSpin  // Truyền biến deductSpin
                        },
                        success: function(response) {
                            alert(response);
                            location.reload();
                        }
                    });
                }
        </script>
                     <!--code quay thưởng -->
                </div>
            <?php } else { ?>
                <div class="not-qualified">
                    <!-- Thông báo cho khách hàng không phải Vip2 đến Vip5 -->
                    <p>Xin lỗi, bạn không thuộc đối tượng khách hàng được quay thưởng.</p>
                    <p>Khách VIP từ Vip2 đến Vip5 mới được hưởng chế độ này.</p>
                </div>
            <?php } ?>


           <?php if ($user_info['level'] == 'admin') { ?>
            <h2>Danh sách khách VIP từ Vip1 đến Vip5</h2>
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
                          <select id="level" name="level" class="form-control" required>
                              <option value="admin">Admin</option>
                              <option value="Vip1">Vip1</option>
                              <option value="Vip2">Vip2</option>
                              <option value="Vip3">Vip3</option>
                              <option value="Vip4">Vip4</option>
                              <option value="Vip5">Vip5</option>
                          </select>
                      </div>

                      <div class="form-group">
                          <label for="points">Điểm:</label>
                          <input type="number" id="points" name="points" class="form-control" required>
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
            <table cellpadding="0" cellspacing="0" border="0" class="luckywheel">
                <!-- ... (Table headers) ... -->
                <tr>
                    <th>Full Name</th>
                    <th>Phone</th>
                    <th>Level</th>
                    <th>Points</th>
                    <th style="width: 30% !important;">Kết quả</th>
                    <th style="width: 30% !important;">Đã nhận</th>
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
                $sql = "SELECT * FROM customers WHERE level LIKE 'Vip%' AND level <= 'Vip5'$filter_sql LIMIT $offset, $items_per_page";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                  while ($row = $result->fetch_assoc()) {
                      echo "<tr>";
                      echo "<td>" . $row["fullname"] . "</td>";
                      echo "<td>" . $row["phone"] . "</td>";
                      echo "<td>" . $row["level"] . "</td>";
                      echo "<td>" . $row["points"] . "</td>";

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
    <!-- Pagination Links -->
    <?php
    $sql_count = "SELECT COUNT(*) AS total FROM customers WHERE level LIKE 'Vip%' AND level <= 'Vip5'$filter_sql";
    $result_count = $conn->query($sql_count);
    $row_count = $result_count->fetch_assoc();
    $total_pages = ceil($row_count['total'] / $items_per_page);

    echo "<div>Trang: ";
    for ($i = 1; $i <= $total_pages; $i++) {
        echo "<a href='?page=$i&vip_filter=$vip_filter'>$i</a> ";
    }
    echo "</div>";
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
    // Đảm bảo rằng trang đã được tải hoàn thành trước khi thực hiện các thao tác

    // Lắng nghe sự kiện khi checkbox được thay đổi
    $(document).on('change', '.prize-received', function() {
        // Lấy các thông tin cần thiết từ thuộc tính data của checkbox
        const prizeId = $(this).data('prize-id');
        const isReceived = $(this).is(':checked');

        // Thay đổi màu sắc của các cột Kết quả và Kết quả quay thưởng tương ứng
        if (isReceived) {
            $(`#${prizeId}`).css('color', 'red');
        } else {
            $(`#${prizeId}`).css('color', 'black');
        }

        // Thực hiện AJAX request để cập nhật trạng thái đã nhận của kết quả
        $.ajax({
            type: 'POST',
            url: './update_prize_received.php', // Thay đổi đường dẫn tương ứng
            data: {
                prizeId: prizeId,
                isReceived: isReceived ? 1 : 0
            },
            success: function(response) {
                // Xử lý phản hồi từ server nếu cần
                alert(response);
            }
        });
    });
});
    </script>
</body>
</html>