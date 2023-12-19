    <?php
    // Tính tổng tiền Việt vào
    $query_vn_vao = "SELECT bank_name_vn, SUM(amount_vn) as total_amount_vn FROM vn_to_cn_transfer GROUP BY bank_name_vn";
    $result_vn_vao = $conn->query($query_vn_vao);

    // Tính tổng tiền Việt ra
    $sql_vn_ra = "SELECT bank_vietnam, SUM(converted_amount) as total_vn FROM cn_to_vn_transfer GROUP BY bank_vietnam";
    $result_vn_ra = $conn->query($sql_vn_ra);

    // Tính tổng tiền CN vào
    $sql_cn_vao = "SELECT bank_china, SUM(amount_to_transfer) as total_cn FROM cn_to_vn_transfer GROUP BY bank_china";
    $result_cn_vao = $conn->query($sql_cn_vao);

    // Tính tổng tiền CN ra
    $query_cn_ra = "SELECT bank_name_cn, SUM(amount_cn) as total_amount_cn FROM vn_to_cn_transfer GROUP BY bank_name_cn";
    $result_cn_ra = $conn->query($query_cn_ra);
    if (isset($_POST['update'])) {
      while ($row_vn_vao = $result_vn_vao->fetch_assoc()) {
          $bank_name_vn = $row_vn_vao['bank_name_vn'];
          $total_vn_vao = $row_vn_vao['total_amount_vn'];

          $total_vn_ra = 0;
          while ($row_vn_ra = $result_vn_ra->fetch_assoc()) {
              if ($bank_name_vn == $row_vn_ra['bank_vietnam']) {
                  $total_vn_ra = $row_vn_ra['total_vn'];
                  break;
              }
          }

          $new_balance_vn = $total_vn_vao - $total_vn_ra;
          $update_sql_vn = "UPDATE bank_balance_vn SET total_amount_vn = total_amount_vn + $new_balance_vn WHERE bank_name_vn = '$bank_name_vn'";
          $conn->query($update_sql_vn);
      }

      while ($row_cn_vao = $result_cn_vao->fetch_assoc()) {
          $bank_name_cn = $row_cn_vao['bank_china'];
          $total_cn_vao = $row_cn_vao['total_cn'];

          $total_cn_ra = 0;
          while ($row_cn_ra = $result_cn_ra->fetch_assoc()) {
              if ($bank_name_cn == $row_cn_ra['bank_name_cn']) {
                  $total_cn_ra = $row_cn_ra['total_amount_cn'];
                  break;
              }
          }

          $new_balance_cn = $total_cn_vao - $total_cn_ra;
          $update_sql_cn = "UPDATE bank_balance_cn SET total_amount_cn = total_amount_cn + $new_balance_cn WHERE bank_name_cn = '$bank_name_cn'";
          $conn->query($update_sql_cn);
      }
    }
    
    $sql_vn = "SELECT SUM(total_amount_vn) AS total_vn FROM bank_balance_vn";
    $sql_cn = "SELECT SUM(total_amount_cn) AS total_cn FROM bank_balance_cn";

    $result_vn = $conn->query($sql_vn);
    $result_cn = $conn->query($sql_cn);

    $total_vn = $result_vn->fetch_assoc()['total_vn'];
    $total_cn = $result_cn->fetch_assoc()['total_cn'];
    
    $sql_vn_in = "SELECT SUM(amount_vn) AS total_vn_in FROM vn_to_cn_transfer";
    $sql_cn_out = "SELECT SUM(amount_cn) AS total_cn_out FROM vn_to_cn_transfer";
    $sql_cn_in = "SELECT SUM(amount_to_transfer) AS total_cn_in FROM cn_to_vn_transfer";
    $sql_vn_out = "SELECT SUM(converted_amount) AS total_vn_out FROM cn_to_vn_transfer";

    $total_vn_in = $conn->query($sql_vn_in)->fetch_assoc()['total_vn_in'];
    $total_cn_out = $conn->query($sql_cn_out)->fetch_assoc()['total_cn_out'];
    $total_cn_in = $conn->query($sql_cn_in)->fetch_assoc()['total_cn_in'];
    $total_vn_out = $conn->query($sql_vn_out)->fetch_assoc()['total_vn_out'];
    ?>
    <style>
    .slide-container {
        overflow: hidden;
        display: flex; /* Dùng để xếp các thẻ div cạnh nhau */
        transition: max-width 0.5s ease;
        max-width: 0; /* Ban đầu sẽ ẩn */
    }

    .slide-container.show-content {
        max-width: 1000px; /* Bạn có thể điều chỉnh con số này tuỳ theo nội dung của bạn */
    }
    </style>
<div class="container mt-5">
      <button id="toggleDayButton" class="btn btn-warning mb-3">Đầu và cuối ngày</button>
      <div id="dayContent" style="display: none;">
          <div class="container mt-5">
      <h2 class="mb-4">NH VN</h2>

      <table class="table table-bordered table-striped">
          <thead>
              <tr>
                  <th>Tên NH</th>
                  <th>Tổng</th>
                  <th>Việt Vào</th>
                  <th>Việt Ra</th>
              </tr>
          </thead>
          <tbody>
              <?php
              $sql = "SELECT bank_name_vn, total_amount_vn FROM bank_balance_vn";
              $result = $conn->query($sql);
              if ($result->num_rows > 0) {
                  while ($row = $result->fetch_assoc()) {
                      $bank_name = $row['bank_name_vn'];
                      $balance = number_format($row['total_amount_vn']);

                      // Get Money In for this bank
                      $money_in = 0;
                      $result_vn_vao->data_seek(0); // Reset result pointer
                      while ($row_vao = $result_vn_vao->fetch_assoc()) {
                          if ($row_vao['bank_name_vn'] == $bank_name) {
                              $money_in = number_format($row_vao['total_amount_vn']);
                              break;
                          }
                      }

                      // Get Money Out for this bank
                      $money_out = 0;
                      $result_vn_ra->data_seek(0); // Reset result pointer
                      while ($row_ra = $result_vn_ra->fetch_assoc()) {
                          if ($row_ra['bank_vietnam'] == $bank_name) {
                              $money_out = number_format($row_ra['total_vn']);
                              break;
                          }
                      }

                      echo "<tr>";
                      echo "<td>$bank_name</td>";
                      echo "<td>$balance</td>";
                      echo "<td>$money_in</td>";
                      echo "<td>$money_out</td>";
                      echo "</tr>";
                  }
              } else {
                  echo '<tr><td colspan="4">Không có ngân hàng nào trong cơ sở dữ liệu.</td></tr>';
              }
              ?>
                    <tr>
                      <th>Tổng Tiền Việt</th>
                      <th><?= number_format($total_vn) ?></th>
                      <th><?= number_format($total_vn_in) ?></th>
                      <th><?= number_format($total_vn_out) ?></th>
                    </tr>
          </tbody>
      </table>
      <div class="col-md-4">
        <button id="toggleFormButton" class="btn btn-secondary mb-2">+</button>
          <div id="bankForm" class="card" style="display: none;">
            <div class="card-body">
              <form action="process_bank_vn.php" method="POST">
                  <div class="form-group">
                      <label for="bank_name">NH VN:</label>
                      <input type="text" class="form-control" name="bank_name" required>
                  </div>
                  <div class="form-group">
                      <label for="total_amount">Số Tiền:</label>
                      <input type="number" class="form-control" name="total_amount" id="amount_vn_input_4" required>
                      <span id="formatted_amount_vn_4" style="font-weight: bold;"></span>
                  </div>
                  <button type="submit" class="btn btn-primary mt-2">Thêm</button>
              </form>
            </div>
          </div> 
      </div>
    </div>

    <div class="container mt-5">
        <h2 class="mb-4">NH TQ</h2>

          <table class="table table-bordered table-striped">
              <thead>
                  <tr>
                      <th>Tên NH</th>
                      <th>Tổng</th>
                      <th>Tệ Vào</th>
                      <th>Tệ Ra</th>
                  </tr>
              </thead>
              <tbody>
                  <?php
                  $sql2 = "SELECT bank_name_cn, total_amount_cn FROM bank_balance_cn";
                  $result2 = $conn->query($sql2);

                  if ($result2->num_rows > 0) {
                      while ($row2 = $result2->fetch_assoc()) {
                          $bank_name_cn = $row2['bank_name_cn'];
                          $balance_cn = number_format($row2['total_amount_cn']);

                          // Get Money In for this bank
                          $money_in_cn = 0;
                          $result_cn_vao->data_seek(0); // Reset result pointer
                          while ($row_vao = $result_cn_vao->fetch_assoc()) {
                              if ($row_vao['bank_china'] == $bank_name_cn) {
                                  $money_in_cn = number_format($row_vao['total_cn']);
                                  break;
                              }
                          }

                          // Get Money Out for this bank
                          $money_out_cn = 0;
                          $result_cn_ra->data_seek(0); // Reset result pointer
                          while ($row_ra = $result_cn_ra->fetch_assoc()) {
                              if ($row_ra['bank_name_cn'] == $bank_name_cn) {
                                  $money_out_cn = number_format($row_ra['total_amount_cn']);
                                  break;
                              }
                          }

                          echo "<tr>";
                          echo "<td>$bank_name_cn</td>";
                          echo "<td>$balance_cn</td>";
                          echo "<td>$money_in_cn</td>";
                          echo "<td>$money_out_cn</td>";
                          echo "</tr>";
                      }
                  } else {
                      echo '<tr><td colspan="4">Không có ngân hàng nào trong cơ sở dữ liệu.</td></tr>';
                  }
                  ?>
                  <tr>
                     <th>Tổng Tiền TQ</th>
                     <th><?= number_format($total_cn) ?></th>
                     <th><?= number_format($total_cn_in) ?></th>
                     <th><?= number_format($total_cn_out) ?></th>
                  </tr>
              </tbody>
          </table>
          <div class="col-md-4">
              <button id="toggleFormButton2" class="btn btn-secondary mb-2">+</button>
              <div id="bankForm2" class="card" style="display: none;">
                  <div class="card-body">
                      <form action="process_bank_cn.php" method="POST">
                          <div class="form-group">
                              <label for="bank_name_cn">NH CN:</label>
                              <input type="text" class="form-control" name="bank_name_cn" required>
                          </div>
                          <div class="form-group">
                              <label for="total_amount_cn">Số Tiền:</label>
                              <input type="number" step="0.01" class="form-control" name="total_amount_cn" id="amount_vn_input_3" required>
                              <span id="formatted_amount_vn_3" style="font-weight: bold;"></span>
                          </div>
                          <button type="submit" class="btn btn-primary mt-2">Thêm</button>
                      </form>
                  </div>
              </div>
          </div>
      </div>
    </div>
</div>
    <script>
    document.getElementById('toggleDayButton').addEventListener('click', function() {
        var dayDiv = document.getElementById('dayContent');
        dayDiv.style.display = dayDiv.style.display === 'none' ? 'block' : 'none';
    });
    </script>
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
        document.addEventListener("DOMContentLoaded", function () {
            const toggleFormButton2 = document.getElementById("toggleFormButton2");
            const bankForm2 = document.getElementById("bankForm2");

            toggleFormButton2.addEventListener("click", function () {
                if (bankForm2.style.display === "none") {
                    bankForm2.style.display = "block";
                    toggleFormButton2.textContent = "-";
                } else {
                    bankForm2.style.display = "none";
                    toggleFormButton2.textContent = "+";
                }
            });
        });
    </script>
    <script>
       // Sử dụng JavaScript để định dạng số tiền VN nhận có dấu chấm khi nhập và hiển thị ngoài ô
       document.getElementById('amount_vn_input_3').addEventListener('input', function (e) {
           // Lấy giá trị nhập vào
           let inputValue = e.target.value;

           // Định dạng giá trị với dấu chấm
           let formattedValue = inputValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

           // Hiển thị giá trị đã định dạng bên ngoài ô
           document.getElementById('formatted_amount_vn_3').textContent = formattedValue;
       });
   </script>
   <script>
       // Sử dụng JavaScript để định dạng số tiền VN nhận có dấu chấm khi nhập và hiển thị ngoài ô
       document.getElementById('amount_vn_input_4').addEventListener('input', function (e) {
           // Lấy giá trị nhập vào
           let inputValue = e.target.value;

           // Định dạng giá trị với dấu chấm
           let formattedValue = inputValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

           // Hiển thị giá trị đã định dạng bên ngoài ô
           document.getElementById('formatted_amount_vn_4').textContent = formattedValue;
       });
   </script>
   