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