
<?php
session_start();
$conn = new mysqli("localhost", "root", "", "xl_jpa");
if ($conn->connect_error) {
    die("? DB error: " . $conn->connect_error);
}

$username = $_POST['username'];
$password = hash('sha256', $_POST['password']);

$query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $_SESSION['login'] = true;
    $_SESSION['username'] = $username;
    header("Location: dashboard.php");
} else {
    header("Location: login.php?error=Username atau password salah");
}
