<?php
require_once "../config/db.php";

session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

/* =====================
   TOTAL USER
===================== */
$qUser = mysqli_query($koneksi, "SELECT COUNT(*) AS total_user FROM users");
$totalUser = mysqli_fetch_assoc($qUser)['total_user'];

/* =====================
   TOTAL BARANG
===================== */
$qBarang = mysqli_query($koneksi, "SELECT COUNT(*) AS total_barang FROM barang");
$totalBarang = mysqli_fetch_assoc($qBarang)['total_barang'];

/* =====================
   TRANSAKSI
===================== */
$pending = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM transaksi WHERE status='pending'"))[0];
$proses = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM transaksi WHERE status='diproses'"))[0];
$selesai = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM transaksi WHERE status='selesai'"))[0];
$income = mysqli_fetch_row(mysqli_query($koneksi, "SELECT SUM(total) FROM transaksi WHERE status='selesai'"))[0] ?? 0;

/* =====================
   MOORA
===================== */
$totalMoora = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM riwayat_moora"))[0];
$lastMoora = mysqli_fetch_row(mysqli_query($koneksi, "SELECT MAX(tanggal) FROM riwayat_moora"))[0];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | E-CAT</title>
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
            margin: 0;
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
            transition: 0.3s;
        }

        .menu a:hover,
        .menu a.active {
            background: rgba(255, 255, 255, 0.2);
        }

        .menu .logout a {
            background: rgba(239, 68, 68, 0.9);
        }

        .menu .logout a:hover {
            background: rgba(220, 38, 38, 1);
        }

        .content {
            margin-left: 260px;
            padding: 30px;
            width: calc(100% - 260px);
        }

        h1 {
            margin-top: 0;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .card h3 {
            margin: 0;
            font-size: 15px;
            color: #555;
        }

        .card p {
            font-size: 26px;
            font-weight: 600;
            margin-top: 10px;
            color: #4f46e5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f1f3f8;
            text-align: left;
        }
    </style>
</head>

<body class="admin-body">

    <div class="sidebar">
        <h2 class="logo">E-CAT</h2>
        <ul class="menu">
            <li><a class="active" href="dashboardAdmin.php"> Dashboard</a></li>
            <li><a href="manajemenUserAdmin.php">Manajemen User</a></li>
            <li><a href="manajemenBarangAdmin.php">Manajemen Barang</a></li>
            <li><a href="transaksiAdmin.php">Transaksi Manual</a></li>
            <li><a href="validasiAdmin.php">Validasi Transaksi</a></li>
            <li><a href="riwayatAdmin.php">Riwayat Transaksi</a></li>
            <li><a href="perhitunganAdmin.php">Metode Perhitungan</a></li>
            <li><a href="riwayatPerhitungan.php">📑 Riwayat Perhitungan</a></li>
            <li class="logout"><a href="../logout.php"> Logout</a></li>
        </ul>
    </div>

    <div class="content">
        <h1>Dashboard</h1>
        <p>Selamat datang, <b><?= $_SESSION['user']['username']; ?></b></p>

        <!-- KARTU UTAMA -->
        <div class="cards">
            <div class="card">
                <h3>Total User</h3>
                <p><?= $totalUser ?></p>
            </div>
            <div class="card">
                <h3>Total Barang</h3>
                <p><?= $totalBarang ?></p>
            </div>
            <div class="card">
                <h3>Transaksi Pending</h3>
                <p><?= $pending ?></p>
            </div>
            <div class="card">
                <h3>Transaksi Selesai</h3>
                <p><?= $selesai ?></p>
            </div>
            <div class="card">
                <h3>Total Pendapatan</h3>
                <p>Rp <?= number_format($income, 0, ',', '.') ?></p>
            </div>
            <div class="card">
                <h3>Total Perhitungan MOORA</h3>
                <p><?= $totalMoora ?></p>
            </div>
            <div class="card">
                <h3>MOORA Terakhir</h3>
                <p><?= $lastMoora ? date('d-m-Y', strtotime($lastMoora)) : '-' ?></p>
            </div>
        </div>

        <!-- TOP 3 MOORA -->
        <div class="card" style="margin-top:30px;">
            <h3>Top 3 Barang Terbaik (MOORA)</h3>
            <table>
                <tr>
                    <th>Rank</th>
                    <th>Nama Barang</th>
                    <th>Nilai Yi</th>
                </tr>
                <?php
                $top = mysqli_query($koneksi, "
                SELECT b.nama_barang, d.nilai_yi
                FROM riwayat_moora_detail d
                JOIN barang b ON d.id_barang = b.id_barang
                WHERE d.id_riwayat = (SELECT MAX(id_riwayat) FROM riwayat_moora)
                ORDER BY d.nilai_yi DESC
                LIMIT 3
            ");
                $rank = 1;
                while ($t = mysqli_fetch_assoc($top)) {
                    ?>
                    <tr>
                        <td><?= $rank++ ?></td>
                        <td><?= $t['nama_barang'] ?></td>
                        <td><?= round($t['nilai_yi'], 4) ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>

</body>

</html>