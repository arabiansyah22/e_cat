<?php
require_once "../config/db.php";
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

/* ===============================
   DATA RIWAYAT TRANSAKSI
================================ */
$dataRiwayat = mysqli_query($koneksi, "
    SELECT 
        t.id_transaksi,
        t.tanggal,
        COALESCE(u.username, t.nama_pembeli) AS nama,
        GROUP_CONCAT(b.nama_barang SEPARATOR '<br>') AS daftar_barang,
        GROUP_CONCAT(dt.qty SEPARATOR '<br>') AS daftar_qty,
        GROUP_CONCAT(CONCAT('Rp', FORMAT(dt.harga,0)) SEPARATOR '<br>') AS daftar_harga,
        GROUP_CONCAT(CONCAT('Rp', FORMAT(dt.subtotal,0)) SEPARATOR '<br>') AS daftar_subtotal,
        t.total,
        t.status
    FROM transaksi t
    LEFT JOIN users u ON t.id_users = u.id_users
    JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
    JOIN barang b ON dt.id_barang = b.id_barang
    GROUP BY t.id_transaksi
    ORDER BY t.tanggal DESC
");

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Riwayat Transaksi | E-CAT</title>
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
            padding: 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background: #f1f1f1;
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
            <li><a href="validasiAdmin.php">💰 Validasi Transaksi</a></li>
            <li><a class="active" href="riwayatAdmin.php">💰 Riwayat Transaksi</a></li>
            <li><a href="perhitunganAdmin.php">🧮 Metode Perhitungan</a></li>
            <li><a href="riwayatPerhitungan.php">📑 Riwayat Perhitungan</a></li>
            <li class="logout"><a href="../logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    <div class="content">
        <h1>Riwayat Transaksi</h1>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Barang</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                        <th>Total</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1;
                    while ($r = mysqli_fetch_assoc($dataRiwayat)) { ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $r['nama'] ?></td>
                            <td><?= $r['daftar_barang'] ?></td>
                            <td style="text-align:center;"><?= $r['daftar_qty'] ?></td>
                            <td><?= $r['daftar_harga'] ?></td>
                            <td><?= $r['daftar_subtotal'] ?></td>
                            <td><b>Rp<?= number_format($r['total']) ?></b></td>
                            <td><?= $r['tanggal'] ?></td>
                            <td>
                                <?php
                                if ($r['status'] == 'selesai') {
                                    echo '<span style="color:green;font-weight:600;">Selesai</span>';
                                } elseif ($r['status'] == 'dibatalkan') {
                                    echo '<span style="color:red;font-weight:600;">Dibatalkan</span>';
                                } else {
                                    echo '<span style="color:orange;font-weight:600;">Pending</span>';
                                }
                                ?>
                            </td>
                        </tr>

                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>