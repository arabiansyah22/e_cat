<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

// ambil data barang
$barangs = mysqli_query($koneksi, "SELECT * FROM barang ORDER BY id_barang ASC");

// data edit
$editBarang = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $result = mysqli_query($koneksi, "SELECT * FROM barang WHERE id_barang=$id");
    $editBarang = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Manajemen Barang | E-CAT</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            font-family: Poppins
        }

        body {
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
            margin-bottom: 30px
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
            margin-bottom: 20px
        }

        .form-group {
            margin-top: 14px
        }

        .form-group label {
            font-size: 13px;
            display: block;
            margin-bottom: 6px
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 10px
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 20px
        }

        .btn-login {
            flex: 1;
            padding: 12px;
            background: #4ade80;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer
        }

        .btn-back {
            padding: 12px;
            background: #ccc;
            border-radius: 12px;
            text-decoration: none;
            color: #333;
            text-align: center
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: center
        }

        th {
            background: #f1f5f9
        }

        td:nth-child(3) {
            text-align: left
        }

        td img {
            border-radius: 8px
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <h2 class="logo">E-CAT</h2>
        <ul class="menu">
            <li><a href="dashboardAdmin.php">Dashboard</a></li>
            <li><a href="manajemenUserAdmin.php">Manajemen User</a></li>
            <li><a class="active" href="manajemenBarangAdmin.php">Manajemen Barang</a></li>
            <li><a href="transaksiAdmin.php">Transaksi Manual</a></li>
            <li><a href="validasiAdmin.php">Validasi Transaksi</a></li>
            <li><a href="riwayatAdmin.php">Riwayat Transaksi</a></li>
            <li><a href="perhitunganAdmin.php">Metode Perhitungan</a></li>
            <li><a href="riwayatPerhitungan.php">📑 Riwayat Perhitungan</a></li>
            <li class="logout"><a href="../logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="content">
        <h1>Manajemen Barang</h1>

        <div class="card">
            <h3><?= $editBarang ? "Edit Barang" : "Tambah Barang" ?></h3>

            <form method="POST" action="barang.php" enctype="multipart/form-data">
                <?php if ($editBarang): ?>
                    <input type="hidden" name="id" value="<?= $editBarang['id_barang'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Nama Barang</label>
                    <input type="text" name="nama" required value="<?= $editBarang['nama_barang'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label>Harga</label>
                    <input type="number" name="harga" required value="<?= $editBarang['harga'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label>Moisture (%)</label>
                    <input type="number" step="0.01" name="moisture" required
                        value="<?= $editBarang['moisture'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label>Protein (%)</label>
                    <input type="number" step="0.01" name="protein" required
                        value="<?= $editBarang['protein'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label>Lemak (%)</label>
                    <input type="number" step="0.01" name="lemak" required value="<?= $editBarang['lemak'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label>Crude Fiber (%)</label>
                    <input type="number" step="0.01" name="crude_fiber" required
                        value="<?= $editBarang['crude_fiber'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label>Stok</label>
                    <input type="number" name="stok" required value="<?= $editBarang['stok'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label>Gambar</label>
                    <input type="file" name="gambar">
                </div>

                <div class="form-actions">
                    <button class="btn-login" name="<?= $editBarang ? 'update' : 'tambah' ?>">
                        <?= $editBarang ? 'Update' : 'Tambah' ?>
                    </button>
                    <?php if ($editBarang): ?>
                        <a href="manajemenBarangAdmin.php" class="btn-back">Kembali</a>
                    <?php endif; ?>
                </div>

            </form>
        </div>

        <div class="card">
            <h3>Daftar Barang</h3>

            <table>
                <tr>
                    <th>No</th>
                    <th>Gambar</th>
                    <th>Nama</th>
                    <th>Harga</th>
                    <th>Moisture</th>
                    <th>Protein</th>
                    <th>Lemak</th>
                    <th>Crude Fiber</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>

                <?php $no = 1;
                while ($b = mysqli_fetch_assoc($barangs)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <?php if ($b['gambar']): ?>
                                <img src="uploads/barang/<?= $b['gambar'] ?>" width="60">
                            <?php endif; ?>
                        </td>
                        <td><?= $b['nama_barang'] ?></td>
                        <td>Rp<?= number_format($b['harga']) ?></td>
                        <td><?= $b['moisture'] ?></td>
                        <td><?= $b['protein'] ?></td>
                        <td><?= $b['lemak'] ?></td>
                        <td><?= $b['crude_fiber'] ?></td>
                        <td><?= $b['stok'] ?></td>
                        <td>
                            <a href="?edit=<?= $b['id_barang'] ?>">✏️</a> |
                            <a href="barang.php?hapus=<?= $b['id_barang'] ?>"
                                onclick="return confirm('Hapus barang?')">🗑️</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

    </div>
</body>

</html>