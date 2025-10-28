<?php
echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cek Data</title>
    <link rel="icon" type="image/png" href="logokuda.png">
</head>
<body>
';
include 'session_check.php'; 
$host = 'localhost';
$db = 'xl_jpa';
$user = 'root';
$pass = 'Sud4hm4nd1?';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

function getRowCount($conn, $tableName) {
    $result = $conn->query("SELECT COUNT(*) AS total FROM `$tableName`");
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}

echo "<h3>📋 Jumlah data dalam tabel:</h3>";
echo "📊 xl_psb : " . getRowCount($conn, 'xl_psb') . " baris<br>";
echo "📊 myxl : " . getRowCount($conn, 'myxl') . " baris<br>";
echo "📊 axis : " . getRowCount($conn, 'axis') . " baris<br>";
echo "📊 xlprio : " . getRowCount($conn, 'xlprio') . " baris<br>";
echo "📊 xlsatu : " . getRowCount($conn, 'xlsatu') . " baris<br>";
echo "📊 sidompul : " . getRowCount($conn, 'sidompul') . " baris<br>";
echo "📊 midtrans : " . getRowCount($conn, 'midtrans') . " baris<br>";
?>

<a href="dashboard.php" style="
    display: inline-block;
    background-color: #4CAF50;
    color: white;
    padding: 6px 14px;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: bold;
    font-family: sans-serif;
    margin-top: 20px;
">⬅️ Kembali ke Upload</a>
