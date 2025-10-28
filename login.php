<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login - PSB Reconciliation</title>
  <link rel="icon" type="image/png" href="logokuda.png">
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #e4e4e4;
      display: flex;
      height: 100vh;
    }

    .left {
      flex: 1;
      background: #fff;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 50px;
    }

    .left img.logo {
      width: 500px;
      margin-bottom: 10px;
    }

    .left .tagline {
      font-size: 18px;
      color: #222;
      margin-top: 20px;
      text-align: center;
    }

    .right {
      flex: 1;
      background: #0058ff;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px;
    }

    .form-box {
      background: white;
      padding: 30px 25px;
      border-radius: 12px;
      box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 380px;
    }

    form {
      width: 100%;
    }

    h2 {
      margin-bottom: 30px;
      font-size: 26px;
      color: #333;
    }

    label {
      display: block;
      font-weight: bold;
      margin-bottom: 6px;
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 12px;
      padding-right: 44px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }

    .password-group {
      position: relative;
    }

    .password-group input[type="password"],
    .password-group input[type="text"] {
      padding: 12px 44px 12px 12px;
    }

    .password-group img.toggle-icon {
      position: absolute;
      right: 14px;
      top: 30%;
      transform: translateY(-50%);
      width: 22px;
      height: 22px;
      cursor: pointer;
      opacity: 0.7;
      filter: grayscale(40%);
    }

    .checkbox-container {
      margin-top: 10px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    button {
      width: 100%;
      padding: 12px;
      background-color: #ff0000;
      border: none;
      color: white;
      font-weight: bold;
      font-size: 15px;
      border-radius: 6px;
      cursor: pointer;
    }

    button:hover {
      background-color: #fe0707;
    }

    .error {
      color: red;
      font-size: 14px;
      margin-bottom: 15px;
    }

    @media (max-width: 768px) {
      .left {
        display: none;
      }

      .right {
        flex: 1;
        width: 100%;
      }
    }
  </style>
</head>
<body>

<div class="left">
  <img src="jatelindo_logo.png" alt="Jatelindo" class="logo">
  <div class="tagline">--PSB Reconciliation JUNED BABI--</div>
</div>

<div class="right">
  <div class="form-box">
    <form method="POST" action="auth.php">
      <h2>Log in</h2>
      <?php if (isset($_GET['error'])): ?>
        <div class="error">❌ <?= htmlspecialchars($_GET['error']) ?></div>
      <?php endif; ?>

      <label for="username">Username</label>
      <input type="text" name="username" id="username" placeholder="******" required>

      <label for="password">Password</label>
      <div class="password-group">
        <input type="password" name="password" id="password" placeholder="******" required>
        <img src="eye.svg" id="togglePassword" class="toggle-icon" alt="Toggle Password">
      </div>

      <div class="checkbox-container">
        <input type="checkbox" id="notRobot" required>
        <label for="notRobot">Saya bukan robot</label>
      </div>

      <button type="submit">Masuk</button>
    </form>
  </div>
</div>

<script>
  const toggleIcon = document.getElementById('togglePassword');
  const passwordField = document.getElementById('password');

  toggleIcon.addEventListener('click', () => {
    const isPassword = passwordField.type === 'password';
    passwordField.type = isPassword ? 'text' : 'password';
    toggleIcon.src = isPassword ? 'eye-off.svg' : 'eye.svg';
  });
</script>

</body>
</html>
