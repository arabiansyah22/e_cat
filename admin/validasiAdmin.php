<?php
require_once "../config/db.php";
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

/* ===============================
   AMBIL DATA TRANSAKSI BELUM VALID
================================ */
$transaksi = mysqli_query($koneksi, "
    SELECT 
        t.id_transaksi,
        t.tanggal,
        COALESCE(u.username, t.nama_pembeli) AS nama,
        GROUP_CONCAT(b.nama_barang SEPARATOR ', ') AS nama_barang,
        GROUP_CONCAT(dt.qty SEPARATOR ', ') AS qty,
        t.total,
        t.status
    FROM transaksi t
    LEFT JOIN users u ON t.id_users = u.id_users
    LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
    LEFT JOIN barang b ON dt.id_barang = b.id_barang
    WHERE t.status = 'pending'
    GROUP BY t.id_transaksi
    ORDER BY t.tanggal DESC
");

/* ===============================
   PROSES VALIDASI
================================ */
if (isset($_POST['validasi'])) {
    $id = (int) $_POST['id_transaksi'];
    $status = $_POST['status'];

    // Jika disetujui → kurangi stok
    if ($status == 'selesai') {

        // Ambil detail barang dalam transaksi
        $detail = mysqli_query($koneksi, "
            SELECT id_barang, qty
            FROM detail_transaksi
            WHERE id_transaksi = $id
        ");

        while ($d = mysqli_fetch_assoc($detail)) {
            $id_barang = $d['id_barang'];
            $qty = $d['qty'];

            // Kurangi stok
            mysqli_query($koneksi, "
                UPDATE barang
                SET stok = stok - $qty
                WHERE id_barang = $id_barang
            ");
        }
    }

    // Update status transaksi
    mysqli_query($koneksi, "
        UPDATE transaksi
        SET status = '$status'
        WHERE id_transaksi = $id
    ");

    header("Location: validasiAdmin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Validasi Transaksi | E-CAT</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body.admin-body {
            margin: 0;
            background: #f4f6f9;
            display: flex;
        }

        .sidebar {
            width: 240px;
            height: 100vh;
            background: linear-gradient(180deg, #4f46e5, #4338ca);
            color: #fff;
            padding: 20px 20px 40px 20px;
            /* bawah ditambah */
            position: fixed;
            overflow-y: auto;
            /* 👈 INI KUNCINYA */
        }


        .logo {
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .menu {
            list-style: none;
            padding: 0;
        }

        .menu li {
            margin-bottom: 12px;
        }

        .menu a {
            display: block;
            padding: 12px 15px;
            border-radius: 10px;
            color: #fff;
            text-decoration: none;
        }

        .menu a.active,
        .menu a:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .menu .logout a {
            background: rgba(239, 68, 68, 0.9);
        }

        .content {
            margin-left: 260px;
            padding: 30px;
            width: calc(100% - 260px);
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f1f1f1;
        }

        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-validasi {
            background: #4ade80;
        }

        /* ================= MODAL ================= */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }

        .modal-content {
            background: #fff;
            padding: 25px;
            width: 360px;
            border-radius: 16px;
            animation: zoom .2s;
        }

        @keyframes zoom {
            from {
                transform: scale(.9);
                opacity: 0
            }

            to {
                transform: scale(1);
                opacity: 1
            }
        }

        .modal h3 {
            margin-top: 0;
        }

        select {
            width: 100%;
            padding: 10px;
            border-radius: 10px;
            margin-top: 10px;
            border: 1px solid #ddd;
        }

        .modal-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .btn-cancel {
            background: #e5e7eb;
        }

        .btn-submit {
            background: #4f46e5;
            color: #fff;
        }
    </style>
</head>

<body class="admin-body">

    <div class="sidebar">
        <h2 class="logo">🐱 E-CAT</h2>
        <ul class="menu">
            <li><a href="dashboardAdmin.php">📊 Dashboard</a></li>
            <li><a href="manajemenUserAdmin.php">👤 Manajemen User</a></li>
            <li><a href="manajemenBarangAdmin.php">📦 Manajemen Barang</a></li>
            <li><a href="transaksiAdmin.php">💰 Transaksi Manual</a></li>
            <li><a class="active" href="validasiAdmin.php">💰 Validasi Transaksi</a></li>
            <li><a href="riwayatAdmin.php">💰 Riwayat Transaksi</a></li>
            <li><a href="perhitunganAdmin.php">🧮 Metode Perhitungan</a></li>
            <li><a href="riwayatPerhitungan.php">📑 Riwayat Perhitungan</a></li>
            <li class="logout"><a href="../logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    <div class="content">
        <h1>Validasi Transaksi</h1>

        <div class="card">
            <table>
                <tr>
                    <th>Tanggal</th>
                    <th>Nama</th>
                    <th>Nama Barang</th>
                    <th>Qty</th>
                    <th>Total</th>
                    <th>Aksi</th>
                </tr>

                <?php while ($t = mysqli_fetch_assoc($transaksi)): ?>
                    <tr>
                        <td><?= $t['tanggal'] ?></td>
                        <td><?= $t['nama'] ?></td>
                        <td>
                            <?php
                            $barang = explode(', ', $t['nama_barang']);
                            foreach ($barang as $b) {
                                echo "$b<br>";
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            $qty = explode(', ', $t['qty']);
                            foreach ($qty as $q) {
                                echo "$q<br>";
                            }
                            ?>
                        </td>

                        <td>Rp <?= number_format($t['total']) ?></td>
                        <td>
                            <button class="btn btn-validasi" onclick="openModal('<?= $t['id_transaksi'] ?>')">
                                Validasi
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>

    <!-- ================= MODAL ================= -->
    <div class="modal" id="modalValidasi">
        <div class="modal-content">
            <h3>Validasi Transaksi</h3>

            <form method="POST">
                <input type="hidden" name="id_transaksi" id="id_transaksi">

                <label>Status</label>
                <select name="status" required>
                    <option value="selesai">Disetujui</option>
                    <option value="dibatalkan">Ditolak</option>
                </select>

                <div class="modal-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeModal()">Batal</button>
                    <button type="submit" name="validasi" class="btn btn-submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById('id_transaksi').value = id;
            document.getElementById('modalValidasi').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('modalValidasi').style.display = 'none';
        }
    </script>

</body>

</html>