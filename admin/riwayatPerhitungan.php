<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

/* ===============================
   AMBIL RIWAYAT
================================ */
$riwayat = mysqli_query($koneksi, "
    SELECT * FROM riwayat_moora
    ORDER BY tanggal DESC
");
/* ===============================
   HAPUS RIWAYAT
================================ */
if (isset($_GET['hapus'])) {
    $idHapus = (int) $_GET['hapus'];

    // Hapus detail dulu
    mysqli_query($koneksi, "
        DELETE FROM riwayat_moora_detail
        WHERE id_riwayat = $idHapus
    ");

    // Hapus header riwayat
    mysqli_query($koneksi, "
        DELETE FROM riwayat_moora
        WHERE id_riwayat = $idHapus
    ");

    header("Location: riwayatPerhitungan.php");
    exit;
}


/* ===============================
   DETAIL RIWAYAT (JIKA DIKLIK)
================================ */
$detail = null;
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $detail = mysqli_query($koneksi, "
        SELECT rmd.*, b.nama_barang
        FROM riwayat_moora_detail rmd
        JOIN barang b ON rmd.id_barang = b.id_barang
        WHERE rmd.id_riwayat = $id
        ORDER BY rmd.ranking ASC
    ");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Riwayat Perhitungan MOORA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif
        }

        body.admin-body {
            margin: 0;
            background: #f4f6f9;
            display: flex
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
            font-weight: 600
        }

        .menu {
            list-style: none;
            padding: 0
        }

        .menu li {
            margin-bottom: 12px
        }

        .menu a {
            display: block;
            padding: 12px 15px;
            border-radius: 10px;
            color: #fff;
            text-decoration: none
        }

        .menu a.active,
        .menu a:hover {
            background: rgba(255, 255, 255, .2)
        }

        .menu .logout a {
            background: rgba(239, 68, 68, .9)
        }

        .content {
            margin-left: 260px;
            padding: 30px;
            width: calc(100% - 260px)
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .08);
            margin-bottom: 25px
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center
        }

        th {
            background: #f1f5f9
        }

        a.btn {
            padding: 6px 12px;
            background: #4f46e5;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px
        }

        a.btn-danger {
            padding: 6px 12px;
            background: #ef4444;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px
        }

        a.btn-danger:hover {
            background: #dc2626;
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
            <li><a href="riwayatAdmin.php">💰 Riwayat Transaksi</a></li>
            <li><a href="perhitunganAdmin.php">🧮 Metode Perhitungan</a></li>
            <li><a class="active" href="riwayatPerhitungan.php">📑 Riwayat Perhitungan</a></li>
            <li class="logout"><a href="../logout.php">🚪 Logout</a></li>
        </ul>
    </div>

    <div class="content">
        <h1>Riwayat Perhitungan MOORA</h1>

        <div class="card">
            <h3>Daftar Riwayat</h3>
            <table>
                <tr>
                    <th>No</th>
                    <th>Tanggal Perhitungan</th>
                    <th>Aksi</th>
                </tr>
                <?php $no = 1;
                while ($r = mysqli_fetch_assoc($riwayat)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= date('d-m-Y H:i', strtotime($r['tanggal'])) ?></td>
                        <td>
                            <a class="btn" href="?id=<?= $r['id_riwayat'] ?>">Detail</a>
                            <a class="btn-danger" href="?hapus=<?= $r['id_riwayat'] ?>"
                                onclick="return confirm('Yakin ingin menghapus riwayat ini?')">
                                Hapus
                            </a>
                        </td>
                    </tr>
                <?php endwhile ?>
            </table>

        </div>

        <?php if ($detail): ?>
            <div class="card">
                <h3>Detail Hasil Perhitungan</h3>
                <table>
                    <tr>
                        <th>Ranking</th>
                        <th>Nama Barang</th>
                        <th>Nilai Yi</th>
                    </tr>
                    <?php while ($d = mysqli_fetch_assoc($detail)): ?>
                        <tr>
                            <td>
                                <?= $d['ranking'] ?>
                            </td>
                            <td>
                                <?= $d['nama_barang'] ?>
                            </td>
                            <td>
                                <?= round($d['nilai_yi'], 4) ?>
                            </td>
                        </tr>
                    <?php endwhile ?>
                </table>
            </div>
        <?php endif; ?>

    </div>
</body>

</html>