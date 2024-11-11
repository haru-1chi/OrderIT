<?php
session_start();
require_once 'config/db.php';
require_once 'template/navbar.php';
?>

<!doctype html>
<html lang="en">

<head>
  <title>เข้าสู่ระบบ | IT ORDER PRO</title>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Bootstrap CSS v5.2.1 -->
  <?php bs5() ?>
  <link rel="stylesheet" href="css/style.css">

  <style>
    * {
      margin: 0;
      padding: 0;
    }

    body {
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login-container {
      width: 90%;
      border-radius: 20px;
    }

    .shadows {
      box-shadow: -6px 7px 34px 1px rgba(0, 0, 0, 0.48);
      -webkit-box-shadow: -6px 7px 34px 1px rgba(0, 0, 0, 0.48);
      -moz-box-shadow: -6px 7px 34px 1px rgba(0, 0, 0, 0.48);
    }
  </style>
</head>

<body>

  <main>

    <div class="container p-5 shadows mt-5 login-container">
      <div class="d-flex justify-content-center mb-3">
        <img width="200px" height="100%" src="image/logo.png" alt="">
      </div>

      <h3 class="text-center mb-3">เข้าสู่ระบบ</h3>
      <?php if (isset($_SESSION['error'])) { ?>
        <div class="alert alert-danger" role="alert">
          <?php
          echo $_SESSION['error'];
          unset($_SESSION['error']);
          ?>
        </div>
      <?php } ?>

      <?php if (isset($_SESSION['warning'])) { ?>
        <div class="alert alert-warning" role="alert">
          <?php
          echo $_SESSION['warning'];
          unset($_SESSION['warning']);
          ?>
        </div>
      <?php } ?>

      <?php if (isset($_SESSION['success'])) { ?>
        <div class="alert alert-success" role="alert">
          <?php
          echo $_SESSION['success'];
          unset($_SESSION['success']);
          ?>
        </div>
      <?php } ?>
      <form action="system/LoginSystem.php" method="POST">
        <div class="form-floating mb-3">
          <input type="text" class="form-control" id="floatingInput" name="username" placeholder="name@example.com">
          <label for="floatingInput">ผู้ใช้งาน</label>
        </div>
        <div class="form-floating mb-3">
          <input type="password" class="form-control" id="floatingPassword" name="password" placeholder="Password">
          <label for="floatingPassword">รหัสผ่าน</label>
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

  <?php SC5() ?>
</body>

</html>