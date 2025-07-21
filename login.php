<?php
session_start();
require_once 'config/db.php';
require_once 'template/navbar.php';
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <title>เข้าสู่ระบบ | IT ORDER PRO</title>

  <?php bs5() ?>

  <link rel="stylesheet" href="css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    * {
      margin: 0;
      padding: 0;
    }

    .login-container {
      width: 100%;
      border-radius: 20px;
    }

    .shadows {
      box-shadow: -6px 7px 24px 1px rgba(0, 0, 0, 0.48);
      -webkit-box-shadow: -0px 7px 14px 1px rgba(0, 0, 0, 0.25);
      -moz-box-shadow: -6px 7px 34px 1px rgba(0, 0, 0, 0.48);
    }
  </style>
</head>

<body class="d-flex align-items-center justify-content-center vh-100">
  <main>
    <div class="container p-5 shadows login-container">
      <div class="d-flex justify-content-center mb-3">
        <img width="200px" height="100%" src="image/logo.png" alt="IT ORDER PRO Logo">
      </div>

      <h3 class="text-center mb-3">เข้าสู่ระบบ</h3>

      <?php foreach (['error', 'warning', 'success'] as $type): ?>
        <?php if (isset($_SESSION[$type])): ?>
          <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?>" role="alert">
            <?= htmlspecialchars($_SESSION[$type], ENT_QUOTES, 'UTF-8') ?>
            <?php unset($_SESSION[$type]); ?>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>

      <form action="system/LoginSystem.php" method="POST" autocomplete="off">

       <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <div class="form-floating mb-3">
          <input type="text" class="form-control" id="username" name="username" placeholder="name@example.com" required>
          <label for="username">ผู้ใช้งาน</label>
        </div>

        <div class="form-floating mb-4 position-relative">
          <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
          <label for="password">รหัสผ่าน</label>

          <button type="button" class="btn btn-outline-secondary btn-sm position-absolute top-50 end-0 translate-middle-y me-2 border-0"
            id="togglePassword" tabindex="-1" style="z-index: 10;" aria-label="Toggle password visibility">
            <i class="bi bi-eye-slash" id="togglePasswordIcon"></i>
          </button>
        </div>

        <div class="d-grid gap-3">
          <button type="submit" name="submit" class="btn btn-lg btn-success p-3">เข้าสู่ระบบ</button>
        </div>
      </form>
    </div>
  </main>

  <!-- Bootstrap JavaScript Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous">
  </script>
  <script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('togglePasswordIcon');

    togglePassword.addEventListener('click', function() {
      const isPassword = passwordInput.type === 'password';
      passwordInput.type = isPassword ? 'text' : 'password';
      toggleIcon.classList.toggle('bi-eye');
      toggleIcon.classList.toggle('bi-eye-slash');
    });
  </script>

  <?php SC5() ?>
</body>

</html>