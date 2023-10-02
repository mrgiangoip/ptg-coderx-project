    <?php
    // Tính tổng tiền Việt vào
    $sql1 = "SELECT SUM(amount_vn) as total_amount_vn FROM vn_to_cn_transfer";
    $result1 = $conn->query($sql1);
    $row1 = $result1->fetch_assoc();
    $total_vn_to_cn_vn = $row1['total_amount_vn'];

    // Tính tổng tiền Việt ra
    $sql1 = "SELECT SUM(converted_amount) as total_converted_amount FROM cn_to_vn_transfer";
    $result1 = $conn->query($sql1);
    $row1 = $result1->fetch_assoc();
    $total_vn = $row1['total_converted_amount'];

    // Tính tổng tiền CN vào
    $sql2 = "SELECT SUM(amount_to_transfer) as total_cn_amount FROM cn_to_vn_transfer";
    $result2 = $conn->query($sql2);
    $row2 = $result2->fetch_assoc();
    $total_cn = $row2['total_cn_amount'];

    // Tính tổng tiền CN ra
    $sql2 = "SELECT SUM(amount_cn) as total_amount_cn FROM vn_to_cn_transfer";
    $result2 = $conn->query($sql2);
    $row2 = $result2->fetch_assoc();
    $total_vn_to_cn_cn = $row2['total_amount_cn'];
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
        <div class="row">
            <div class="col-md-3">
                <?php
                // Truy vấn danh sách ngân hàng VN
                $sql = "SELECT bank_name_vn, total_amount_vn FROM bank_balance_vn";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    echo '<ul class="list-group mb-4">';
                    while ($row = $result->fetch_assoc()) {
                        // Sử dụng hàm number_format để định dạng số tiền với dấu chấm phân cách hàng nghìn
                        $formatted_amount = number_format($row['total_amount_vn']);
                        echo '<li class="list-group-item">' . $row['bank_name_vn'] . ': ' . $formatted_amount .'</li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p>Không có ngân hàng nào trong cơ sở dữ liệu.</p>';
                } ?>

                <button id="toggleFormButton" class="btn btn-secondary mb-2">+</button>

                <form id="bankForm" action="process_bank_vn.php" method="POST" style="display: none;">
                    <div class="form-group">
                        <label for="bank_name">NH VN:</label>
                        <input type="text" class="form-control" name="bank_name" required>
                    </div>
                    <div class="form-group">
                        <label for="total_amount">Số Tiền:</label>
                        <input type="number" class="form-control" name="total_amount" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Thêm</button>
                </form>
            </div>

            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header">Tổng Việt vào</div>
                    <div class="card-body">
                        <h5 class="card-title"><?= number_format($total_vn_to_cn_vn) ?> VND</h5>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header">Tổng Việt ra</div>
                    <div class="card-body">
                        <h5 class="card-title"><?= number_format($total_vn) ?> VND</h5>
                    </div>
                </div>
            </div>
        </div> <!-- end of .row -->
    </div>
    <div class="container mt-5">
          <h2 class="mb-4">NH CN</h2>
          <div class="row">
              <div class="col-md-3">
                  <?php
                  $sql2 = "SELECT bank_name_cn, total_amount_cn FROM bank_balance_cn";
                  $result2 = $conn->query($sql2);
                  if ($result2->num_rows > 0) {
                      echo '<ul class="list-group mb-4">';
                      while ($row2 = $result2->fetch_assoc()) {
                          $formatted_amount = number_format($row2['total_amount_cn']);
                          echo '<li class="list-group-item">' . $row2['bank_name_cn'] . ': ' . $formatted_amount . '</li>';
                      }
                      echo '</ul>';
                  } else {
                      echo '<p class="mb-4">Không có ngân hàng nào trong cơ sở dữ liệu.</p>';
                  }
                  ?>

                  <!-- Form để thêm ngân hàng CN mới -->
                  <button id="toggleFormButton2" class="btn btn-secondary mb-2">+</button>

                  <form id="bankForm2" action="process_bank_cn.php" method="POST" style="display: none;">
                      <div class="form-group">
                          <label for="bank_name_cn">NH CN:</label>
                          <input type="text" class="form-control" name="bank_name_cn" required>
                      </div>
                      <div class="form-group">
                          <label for="total_amount_cn">Số Tiền:</label>
                          <input type="number" step="0.01" class="form-control" name="total_amount_cn" required>
                      </div>
                      <button type="submit" class="btn btn-primary mt-2">Thêm</button>
                  </form>
              </div>

              <div class="col-md-3">
                  <div class="card text-white bg-warning mb-3">
                      <div class="card-header">Tổng Tệ ra</div>
                      <div class="card-body">
                          <h5 class="card-title"><?= number_format($total_vn_to_cn_cn) ?> CNY</h5>
                      </div>
                  </div>
              </div>

              <div class="col-md-3">
                  <div class="card text-white bg-danger mb-3">
                      <div class="card-header">Tổng Tệ vào</div>
                      <div class="card-body">
                          <h5 class="card-title"><?= number_format($total_cn) ?> CNY</h5>
                      </div>
                  </div>
              </div>
          </div> <!-- end of .row -->
      </div> <!-- end of .container -->
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