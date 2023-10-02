<div class="container mt-5">
        <h2>NH VN</h2>

        <?php
        // Truy vấn danh sách ngân hàng VN
        $sql = "SELECT bank_name_vn, total_amount_vn FROM bank_balance_vn";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo '<ul class="list-group">';
            while ($row = $result->fetch_assoc()) {
                // Sử dụng hàm number_format để định dạng số tiền với dấu chấm phân cách hàng nghìn
                $formatted_amount = number_format($row['total_amount_vn']);
                echo '<li class="col-md-2 list-group-item">' . $row['bank_name_vn'] . ': ' . $formatted_amount .'</li>';
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

            <button type="submit" class="btn btn-primary">Thêm</button>
        </form>
    </div>
  	<div class="container mt-5">
      <h2>NH CN</h2>

      <?php
	  $sql2 = "SELECT bank_name_cn, total_amount_cn FROM bank_balance_cn";
      $result2 = $conn->query($sql2);
      if ($result2->num_rows > 0) {
          echo '<ul class="list-group">';
          while ($row2 = $result2->fetch_assoc()) {
              // Sử dụng hàm number_format để định dạng số tiền với dấu chấm phân cách hàng nghìn
              $formatted_amount = number_format($row2['total_amount_cn']);
              echo '<li class="col-md-2 list-group-item">' . $row2['bank_name_cn'] . ': ' . $formatted_amount . '</li>';
          }
          echo '</ul>';
      } else {
          echo '<p>Không có ngân hàng nào trong cơ sở dữ liệu.</p>';
      }
      ?>

      <!-- Form để thêm ngân hàng CN mới -->
      <button id="toggleFormButton2" class="btn">+</button>
      <form id="bankForm2" action="process_bank_cn.php" method="POST" style="display: none;" class="mt-4">
          <div class="col-md-2 form-group">
              <label for="bank_name_cn">NH CN:</label>
              <input type="text" class="form-control" name="bank_name_cn" required>
          </div>
          <div class="col-md-2 form-group">
              <label for="total_amount_cn">Số Tiền:</label>
              <input type="number" step="0.01" class="form-control" name="total_amount_cn" required>
          </div>
          <button type="submit" class="btn btn-primary">Thêm</button>
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