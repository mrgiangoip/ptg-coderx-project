<div class="container mt-5">
  <button id="ggnb" class="btn btn-info mb-3">Giao Dịch Nội Bộ</button>
  <div id="internalTransactions" style="display: none;">
    <div class="container mt-5">
      <h2>CN to CN</h2>

          <?php
          // Truy vấn danh sách giao dịch
          $sql_transactions = "SELECT from_bank, to_bank, transfer_amount, transaction_date FROM cn_to_cn_transfer ORDER BY transaction_date DESC";
          $result_transactions = $conn->query($sql_transactions);

          if ($result_transactions->num_rows > 0) {
              echo '<table class="table table-striped">';
              echo '
              <thead>
                <tr>
                  <th>NH Gửi</th>
                  <th>Số Tiền</th>
                  <th>NH Nhận</th>
                </tr>
              </thead>';
              echo '<tbody>';

              while ($row_trans = $result_transactions->fetch_assoc()) {
                  $formatted_amount_trans = number_format($row_trans['transfer_amount']);
                  $transaction_date_trans = date("d-m-Y H:i:s", strtotime($row_trans['transaction_date']));
                  echo "
                  <tr>
                    <td>{$row_trans['from_bank']}</td>
                    <td>{$formatted_amount_trans}</td>
                    <td>{$row_trans['to_bank']}</td>
                  </tr>";
              }

              echo '</tbody></table>';
          } else {
              echo '<p>Không có giao dịch nào được ghi lại.</p>';
          }
          ?>
          <button id="cnToCnButton" class="btn btn-secondary mb-2">+</button>
          <form id="cnToCnform" action="transfer_money_cn.php" method="POST" style="display: none;">
              <div class="row">
                  <div class="col-md-2 form-group">
                      <label for="from_bank_cn">NH Gửi:</label>
                      <select class="form-control" name="from_bank_cn" required>
                          <option value="GICBC">GICBC</option>
                          <option value="GABC">GABC</option>
                      </select>
                  </div>

                  <div class="col-md-2 form-group">
                      <label for="transfer_amount_cn">Số Tiền Chuyển:</label>
                      <input type="number" class="form-control" name="transfer_amount_cn" id="transfer_amount_input2" required>
                      <span id="transfer_amount_cn" style="font-weight: bold;"></span>
                  </div>

                  <div class="col-md-2 form-group">
                      <label for="to_bank_cn">NH Nhận:</label>
                      <select class="form-control" name="to_bank_cn" required>
                          <option value="GICBC">GICBC</option>
                          <option value="GABC">GABC</option>
                      </select>
                  </div>
              </div>
              <button type="submit" class="btn btn-success">Ok</button>
          </form>
      </div>

      <div class="container mt-5">
          <h2>VN to VN</h2>

          <?php
          // Truy vấn danh sách giao dịch
          $sql = "SELECT from_bank, to_bank, transfer_amount, transaction_date FROM vn_to_vn_transfer ORDER BY transaction_date DESC";
          $result = $conn->query($sql);

          if ($result->num_rows > 0) {
              echo '<table class="table table-striped">';
              echo '<thead>
                      <tr>
                        <th>NH Gửi</th>
                        <th>Số Tiền</th>
                        <th>NH Nhận</th>
                      </tr>
                    </thead>';
              echo '<tbody>';

              while ($row = $result->fetch_assoc()) {
                  $formatted_amount = number_format($row['transfer_amount']);
                  $transaction_date = date("d-m-Y H:i:s", strtotime($row['transaction_date']));
                  echo "
                  <tr>
                    <td>{$row['from_bank']}</td>
                    <td>{$formatted_amount}</td>
                    <td>{$row['to_bank']}</td>
                  </tr>";
              }

              echo '</tbody></table>';
          } else {
              echo '<p>Không có giao dịch nào được ghi lại.</p>';
          }
          ?>
       <button id="vnToVnButton" class="btn btn-secondary mb-2">+</button>
          <form id="vnToVnform" action="transfer_money_vn.php" method="POST" style="display: none;">
              <div class="row">
                  <div class="col-md-2 form-group">
                      <label for="from_bank">NH Gửi:</label>
                      <select class="form-control" name="from_bank" required>
                          <option value="GTCB">GTCB</option>
                          <option value="GVCB">GVCB</option>
                          <option value="GTCB">GACB</option>
                      </select>
                  </div>

                  <div class="col-md-2 form-group">
                      <label for="transfer_amount">Số Tiền Chuyển:</label>
                      <input type="number" class="form-control" name="transfer_amount" id="transfer_amount_input" required>
                      <span id="transfer_amount_vn" style="font-weight: bold;"></span>
                  </div>

                  <div class="col-md-2 form-group">
                      <label for="to_bank">NH Nhận:</label>
                      <select class="form-control" name="to_bank" required>
                          <option value="GTCB">GTCB</option>
                          <option value="GVCB">GVCB</option>
                          <option value="GTCB">GACB</option>    
                      </select>
                  </div>
              </div>

              <button type="submit" class="btn btn-success">Ok</button>
          </form>
        </div>
    </div>
 </div>

<script>
    document.getElementById('ggnb').addEventListener('click', function() {
        var transactionsDiv = document.getElementById('internalTransactions');
        transactionsDiv.style.display = transactionsDiv.style.display === 'none' ? 'block' : 'none';
    });
</script>
            <script>
                // Sử dụng JavaScript để định dạng số tiền VN nhận có dấu chấm khi nhập và hiển thị ngoài ô
                document.getElementById('transfer_amount_input').addEventListener('input', function (e) {
                    // Lấy giá trị nhập vào
                    let inputValue = e.target.value;

                    // Định dạng giá trị với dấu chấm
                    let formattedValue = inputValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

                    // Hiển thị giá trị đã định dạng bên ngoài ô
                    document.getElementById('transfer_amount_vn').textContent = formattedValue;
                });
              
                // Sử dụng JavaScript để định dạng số tiền CN nhận có dấu chấm khi nhập và hiển thị ngoài ô
                document.getElementById('transfer_amount_input2').addEventListener('input', function (e) {
                    // Lấy giá trị nhập vào
                    let inputValue = e.target.value;

                    // Định dạng giá trị với dấu chấm
                    let formattedValue = inputValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

                    // Hiển thị giá trị đã định dạng bên ngoài ô
                    document.getElementById('transfer_amount_cn').textContent = formattedValue;
                });
            </script>
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    const vnToVnButton = document.getElementById("vnToVnButton");
                    const vnToVnform = document.getElementById("vnToVnform");

                    vnToVnButton.addEventListener("click", function () {
                        if (vnToVnform.style.display === "none") {
                            vnToVnform.style.display = "block";
                            vnToVnButton.textContent = "-";
                        } else {
                            vnToVnform.style.display = "none";
                            vnToVnButton.textContent = "+";
                        }
                    });
                });
                document.addEventListener("DOMContentLoaded", function () {
                    const cnToCnButton = document.getElementById("cnToCnButton");
                    const cnToCnform = document.getElementById("cnToCnform");

                    cnToCnButton.addEventListener("click", function () {
                        if (cnToCnform.style.display === "none") {
                            cnToCnform.style.display = "block";
                            cnToCnButton.textContent = "-";
                        } else {
                            cnToCnform.style.display = "none";
                            cnToCnButton.textContent = "+";
                        }
                    });
                });
            </script>