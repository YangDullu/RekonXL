<?php
echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Hasil Upload</title>
    <link rel="icon" type="image/png" href="logokuda.png">
</head>
<body>
';
include 'session_check.php';
ini_set('max_execution_time', 0);
require 'vendor/autoload.php'; // pastikan sudah install PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$host = "localhost";
$user = "root";
$pass = "Sud4hm4nd1?";
$db   = "xl_jpa";

$conn = new mysqli($host, $user, $pass, $db);
$conn->options(MYSQLI_OPT_LOCAL_INFILE, true);

if ($conn->connect_error) {
    die("❌ Koneksi gagal: " . $conn->connect_error);
}

// Mapping nama file ke tabel
$map = [
    'xl'        => 'xl_psb',
    'myxl'      => 'myxl',
    'axis'      => 'axis',
    'xlprio'    => 'xlprio',
    'xlsatu'    => 'xlsatu',
    'sidompul'  => 'sidompul',
    'midtrans'  => 'midtrans'
];

if (isset($_FILES['csvfile'])) {
    $file = $_FILES['csvfile'];
    $filename = $file['name'];
    $tmpName = $file['tmp_name'];
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $key = strtolower(pathinfo($filename, PATHINFO_FILENAME));
    $table = $map[$key] ?? die("❌ Nama file '$key' tidak dikenali dalam mapping tabel.");

    // Cek tabel ada
    $cek = $conn->query("SHOW TABLES LIKE '$table'");
    if ($cek->num_rows === 0) {
        die("❌ Tabel '$table' tidak ditemukan di database.");
    }

    // Simpan file
    $uploadPath = $uploadDir . $filename;
    if (!move_uploaded_file($tmpName, $uploadPath)) {
        die("❌ Gagal memindahkan file ke folder uploads/");
    }

    // Konversi XLSX ke CSV jika perlu
        if ($extension === 'xlsx') {
        $spreadsheet = IOFactory::load($uploadPath);
        $worksheet = $spreadsheet->getActiveSheet();

        $highestRow = $worksheet->getHighestDataRow();
        $highestColumn = $worksheet->getHighestDataColumn();
        $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        $csvPath = $uploadDir . $key . '.csv';
        $fp = fopen($csvPath, 'w');

        // Pakai delimiter ;
        $delimiter = ';';

        for ($row = 1; $row <= $highestRow; $row++) {
            $line = [];
            for ($col = 1; $col <= $highestColIndex; $col++) {
            $cell = Coordinate::stringFromColumnIndex($col) . $row;
            $value = $worksheet->getCell($cell)->getFormattedValue();
            $line[] = $value;
            }
            fputcsv($fp, $line, $delimiter);
        }

        fclose($fp);
        $uploadPath = $csvPath;
        $extension = 'csv';
    }

    // Deteksi delimiter
    $firstLine = file($uploadPath)[0] ?? '';
    $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

    // Baca header
    $handle = fopen($uploadPath, 'r');
    $header = fgetcsv($handle, 0, $delimiter);
    fclose($handle);

    if (!$header) {
        die("❌ Gagal membaca header kolom dari file.");
    }

    // Normalisasi header
    $header = array_map(function($col) {
        return strtolower(str_replace(' ', '_', trim($col)));
    }, $header);

    // Ambil kolom dari DB
    $existingCols = [];
    $res = $conn->query("SHOW COLUMNS FROM `$table`");
    while ($row = $res->fetch_assoc()) {
        $existingCols[] = strtolower($row['Field']);
    }

    // Susun kolom LOAD DATA
    $columnListParts = [];
    foreach ($header as $col) {
        $columnListParts[] = in_array($col, $existingCols) ? "`$col`" : "@dummy";
    }

    $columnList = implode(",", $columnListParts);
    $escapedPath = $conn->real_escape_string($uploadPath);

    // LOAD DATA
    $query = "
        LOAD DATA LOCAL INFILE '$escapedPath'
        INTO TABLE `$table`
        CHARACTER SET utf8mb4
        FIELDS TERMINATED BY '$delimiter'
        ENCLOSED BY '\"'
        LINES TERMINATED BY '\n'
        IGNORE 1 LINES
        ($columnList)
    ";

    if ($conn->query($query)) {
        $affected = $conn->affected_rows;
        echo "✅ <b>Upload selesai ke tabel:</b> <code>$table</code><br>";
        echo "✔️ <b>$affected</b> baris berhasil di-insert <br>";
    } else {
        die("❌ Gagal insert: " . $conn->error);
    }

    // Trim karakter aneh
    if (in_array($table, ['myxl', 'axis', 'xlprio', 'xlsatu', 'sidompul'])) {
        $conn->query("UPDATE `$table` SET `reference` = TRIM(REPLACE(REPLACE(`reference`, '\r', ''), '\n', ''))");
    } elseif ($table === 'xl_psb') {
        $conn->query("UPDATE xl_psb SET `transactionid` = TRIM(REPLACE(REPLACE(`transactionid`, '\r', ''), '\n', ''))");
    } elseif ($table === 'midtrans') {
        $conn->query("UPDATE midtrans SET `order_id` = TRIM(REPLACE(REPLACE(`order_id`, '\r', ''), '\n', ''))");
    }

    // Tombol kembali
    echo '<a href="dashboard.php" style="
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
    ">⬅️ Kembali ke Upload</a>';
}
?>