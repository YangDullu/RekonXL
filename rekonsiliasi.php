<?php
include 'session_check.php';
require 'vendor/autoload.php'; // pastikan folder vendor hasil ekstrak ada

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Koneksi ke database
$host = 'localhost';
$db = 'xl_jpa';
$user = 'root';
$pass = 'Sud4hm4nd1?';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("? Koneksi gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Jalankan stored procedure
$result = $conn->query("CALL recon_xl()");
if (!$result) {
    die("? Gagal menjalankan prosedur rekonsiliasi: " . $conn->error);
}

// Siapkan spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Rekonsiliasi');

// Header kolom sesuai hasil stored procedure
$headers = ['Transaction ID', 'MyXL', 'Axis', 'XL Prio', 'XL Satu', 'Midtrans', 'Sidompul', 'FINAL CHECK STATUS PG', 
            'PSB REMARKS'];
$sheet->fromArray($headers, null, 'A1');

// Buat style untuk border
$borderStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => '000000'],
        ],
    ],
];

// Warna header (kuning + bold)
$sheet->getStyle('B1:I1')->applyFromArray([
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFFFFF00'], // Yellow
    ],
    'font' => [
        'bold' => true,
    ],
]);

// Warna header (pink + bold)
$sheet->getStyle('I1')->applyFromArray([
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFFF66CC'], // pink
    ],
    'font' => [
        'bold' => true,
    ],
]);

// Isi data
$rowIndex = 2;
while ($row = $result->fetch_assoc()) {
    $sheet->setCellValue("A{$rowIndex}", $row['transactionid']);
    $sheet->setCellValue("B{$rowIndex}", $row['myxl']);
    $sheet->setCellValue("C{$rowIndex}", $row['axis']);
    $sheet->setCellValue("D{$rowIndex}", $row['xlprio']);
    $sheet->setCellValue("E{$rowIndex}", $row['xlsatu']);
    $sheet->setCellValue("F{$rowIndex}", $row['midtrans']);
    $sheet->setCellValue("G{$rowIndex}", $row['sidompul']);
    $sheet->setCellValue("H{$rowIndex}", $row['Final_Check_Status_PG']);
    $sheet->setCellValue("I{$rowIndex}", $row['PSB_REMARKS']);
    $rowIndex++;
}

// Total baris data (termasuk header)
$lastRow = $rowIndex - 1;

// Terapkan warna latar kuning untuk A2:H...
$sheet->getStyle("B2:H{$lastRow}")->applyFromArray([
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFFFFF00'], // Yellow
    ],
]);

// Terapkan warna latar pink untuk kolom I2:I...
$sheet->getStyle("I2:I{$lastRow}")->applyFromArray([
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFFF66CC'], // Pink
    ],
]);

// Terapkan border ke semua sel yang digunakan
$sheet->getStyle("A1:I{$lastRow}")->applyFromArray($borderStyle);

// (Optional) Auto size kolom
foreach (range('A', 'I') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$conn->close();

// Output sebagai file Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Hasil_rekonsiliasi.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
