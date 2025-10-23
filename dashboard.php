<?php
require_once 'session_check.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PSB Reconciliation</title>
    <link rel="icon" type="image/png" href="logokuda.png">
    <style>
        :root {
            --primary: #4CAF50;
            --primary-hover: #45a049;
            --accent: #007BFF;
            --bg: #f9f9f9;
            --text: #333;
            --shadow: rgba(0, 0, 0, 0.1);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", "Noto Color Emoji", "Apple Color Emoji", sans-serif;
            margin: 0;
            padding: 30px 20px;
            background-color: #007BFF;
            color: white;
            position: relative;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .container {
            max-width: 500px;
            margin: auto;
            background-color: white;
            color: black;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px var(--shadow);
        }

        form {
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 10px;
        }

        input[type="file"] {
            width: 100%;
            padding: 8px;
        }

        button {
            padding: 12px;
            width: 100%;
            background-color: var(--primary);
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1em;
            margin-top: 10px;
            transition: background-color 0.2s ease;
        }

        button:hover {
            background-color: var(--primary-hover);
        }

        .note {
            font-size: 0.9em;
            color: #555;
            background-color: #f1f1f1;
            padding: 15px;
            border-left: 4px solid var(--accent);
            border-radius: 6px;
            margin-top: 20px;
        }

        .note code {
            background-color: #eee;
            padding: 2px 4px;
            border-radius: 4px;
        }

        .note ul {
            margin-top: 10px;
            padding-left: 20px;
        }

        hr {
            margin: 30px 0;
        }

        #loadingkuda {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            width: 700px;
            transform: translate(-50%, -50%);
            z-index: 999;
        }

        .logout-fixed {
            position: absolute;
            top: 20px;
            right: 20px;
            color: white;
            font-weight: bold;
            text-decoration: none;
            background-color: rgba(200, 200, 200, 0.15);
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 14px;
        }

        .logout-fixed:hover {
            background-color: rgba(200, 200, 200, 0.3);
            text-decoration: none;
        }
    </style>
</head>
<body>

    <a href="logout.php" class="logout-fixed">🚪 Logout</a>
    <img id="loadingkuda" src="animated_kuda.gif" alt="Loading...">

    <div class="container">
        <h2>-- PSB Reconciliation --</h2>
        
            <?php if (isset($_GET['success'])): ?>
                <div style="background:#d4edda; color:#155724; padding:10px 15px; margin-bottom:20px; border-radius:6px; border:1px solid #c3e6cb;">
                    ✅ Semua tabel berhasil dibersihkan.
                </div>
            <?php endif; ?>

        <!-- Tombol Bersihkan Tabel -->
        <form action="truncate_tables.php" method="GET">
            <button type="submit" style="background-color: #dc3545;">🧹 Bersihkan Tabel</button>
        </form>

        <!-- Upload Form -->
        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <label for="csvfile">Pilih file CSV:</label>
            <input type="file" name="csvfile" id="csvfile" accept=".csv" required>
            <button type="submit">📤 Upload</button>

            <div class="note">
                <b>Catatan:</b> Gunakan nama file berikut:
                <ul>
                    <li><code>xl.csv</code> → untuk data XL</li>
                    <li><code>myxl.csv</code> → untuk data myxl</li>
                    <li><code>axis.csv</code> → untuk data axis</li>
                    <li><code>xlprio.csv</code> → untuk data xlprio</li>
                    <li><code>xlsatu.csv</code> → untuk data xlsatu</li>
                    <li><code>sidompul.csv</code> → untuk data sidompul</li>
                    <li><code>midtrans.csv</code> → untuk data Midtrans</li>
                </ul>
            </div>
        </form>

        <!-- Check Rows -->
        <form action="check_rows.php" method="GET">
            <button type="submit">🔍 Cek Jumlah Data Upload</button>
        </form>

        <!-- Proses Rekonsiliasi -->
        <form action="rekonsiliasi.php" method="POST" id="rekonForm">
            <button type="submit">⚙️ Proses Rekonsiliasi</button>
        </form>
    </div>

    <script>
        const uploadForm = document.querySelector('form[action="upload.php"]');
        const checkForm = document.querySelector('form[action="check_rows.php"]');
        const rekonForm = document.querySelector('form[action="rekonsiliasi.php"]');
        const loader = document.getElementById('loadingkuda');

        function showLoader() {
            loader.style.display = 'block';
        }

        uploadForm.addEventListener('submit', showLoader);
        checkForm.addEventListener('submit', showLoader);
        rekonForm.addEventListener('submit', showLoader);
    </script>

</body>
</html>
