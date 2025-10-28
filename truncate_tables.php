<?php
        require_once 'session_check.php';

        $host = 'localhost';
        $user = 'root'; // Ganti jika beda
        $pass = 'Sud4hm4nd1?';     // Ganti jika ada password
        $db   = 'xl_jpa';

        $conn = new mysqli($host, $user, $pass, $db);
        if ($conn->connect_error) {
            die("Koneksi gagal: " . $conn->connect_error);
        }

       // Proses truncate jika form disubmit
        $truncateMessages = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['tables']) && is_array($_POST['tables'])) {
                foreach ($_POST['tables'] as $table) {
                    if (preg_match('/^\w+$/', $table)) {
                        $sql = "TRUNCATE TABLE `$table`";
                        if ($conn->query($sql)) {
                            $truncateMessages[] = "✅ Tabel <strong>$table</strong> berhasil dibersihkan.";
                        } else {
                            $truncateMessages[] = "❌ Gagal membersihkan tabel <strong>$table</strong>: " . $conn->error;
                        }
                    }
                }
            } else {
                $truncateMessages[] = "⚠️ Tidak ada tabel yang dipilih.";
            }
        }
        ?>

        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <title>Pembersihan Tabel</title>
            <link rel="icon" type="image/png" href="logokuda.png">
            <style>
                body {
                    font-family: 'Segoe UI', sans-serif;
                    background-color: #f0f0f0;
                    padding: 40px;
                }
                .container {
                    background-color: #fff;
                    padding: 30px;
                    border-radius: 8px;
                    max-width: 600px;
                    margin: auto;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                }
                h2 {
                    margin-bottom: 20px;
                }
                .message {
                    margin-bottom: 20px;
                    padding: 15px;
                    border-radius: 5px;
                }
                .success {
                    background-color: #d4edda;
                    border: 1px solid #c3e6cb;
                    color: #155724;
                }
                .error {
                    background-color: #f8d7da;
                    border: 1px solid #f5c6cb;
                    color: #721c24;
                }
                .warning {
                    background-color: #fff3cd;
                    border: 1px solid #ffeeba;
                    color: #856404;
                }
                label {
                    display: block;
                    margin: 5px 0;
                }
                button {
                    background-color: #dc3545;
                    color: white;
                    padding: 10px 20px;
                    border-radius: 6px;
                    border: none;
                    font-weight: bold;
                    cursor: pointer;
                }
                button:hover {
                    background-color: #c82333;
                }
                a {
                    display: inline-block;
                    margin-top: 20px;
                    text-decoration: none;
                    background-color: #007bff;
                    color: white;
                    padding: 10px 20px;
                    border-radius: 6px;
                    font-weight: bold;
                }
                a:hover {
                    background-color: #0056b3;
                }
            </style>
        </head>
        <body>
        <div class="container">
                <h2>🧹 Pilih Tabel yang Ingin Dibersihkan</h2>

                <?php
                // Tampilkan hasil truncate jika ada
                foreach ($truncateMessages as $msg) {
                    $class = 'success';
                    if (str_contains($msg, 'Gagal')) $class = 'error';
                    elseif (str_contains($msg, 'Tidak ada')) $class = 'warning';

                    echo "<div class='message $class'>$msg</div>";
                }
                ?>

                <form method="POST" action="truncate_tables.php" onsubmit="return confirm('Yakin ingin menghapus data dari tabel yang dipilih?');">
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" id="select-all"> Pilih Semua
                        </label>
                    </div>

                    <?php
                    $result = $conn->query("SHOW TABLES");
                    if ($result->num_rows > 0) {
                        $excludedTables = ['users']; // Tabel yang tidak ingin ditampilkan

                        while ($row = $result->fetch_array()) {
                            $table = $row[0];
                            if (in_array($table, $excludedTables)) continue;

                            echo "<div class='checkbox-group'><label><input type='checkbox' name='tables[]' value='$table'> $table</label></div>";
                        }
                    } else {
                        echo "<p><em>Tidak ada tabel di database.</em></p>";
                    }
                    ?>

                    <button type="submit" class="submit-btn">🧹 Bersihkan Tabel Terpilih</button>
                </form>

                <a href="dashboard.php" class="back-btn">⬅️ Kembali</a>
            </div>

            <!-- JavaScript: Pilih Semua -->
            <script>
                document.getElementById('select-all').addEventListener('change', function () {
                    const checkboxes = document.querySelectorAll('input[name="tables[]"]');
                    checkboxes.forEach(cb => cb.checked = this.checked);
                });
            </script>

            <!-- CSS Styling -->
            <style>
                .container {
                    max-width: 500px;
                    margin: 30px auto;
                    font-family: 'Segoe UI', sans-serif;
                }

                h2 {
                    margin-bottom: 20px;
                    font-size: 22px;
                }

                .checkbox-group {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    margin: 6px 0;
                }

                label {
                    font-size: 16px;
                    cursor: pointer;
                }

                .submit-btn {
                    display: block;
                    width: 100%;
                    margin-top: 20px;
                    padding: 10px;
                    background-color: #dc3545;
                    color: white;
                    border: none;
                    border-radius: 6px;
                    font-size: 16px;
                    font-weight: bold;
                    cursor: pointer;
                }

                .submit-btn:hover {
                    background-color: #b52a36;
                }

                .back-btn {
                    display: block;
                    text-align: center;
                    margin-top: 15px;
                    padding: 10px 20px;
                    background-color: #007bff;
                    color: white;
                    text-decoration: none;
                    border-radius: 6px;
                    font-weight: bold;
                }

                .back-btn:hover {
                    background-color: #0056b3;
                }

                input[type="checkbox"]:checked {
                    accent-color: #11b199ff;
                }

                /* Optional: warna pesan */
                .message.success { color: green; }
                .message.error { color: red; }
                .message.warning { color: orange; }
            </style>
        </div>
    </body>
</html>