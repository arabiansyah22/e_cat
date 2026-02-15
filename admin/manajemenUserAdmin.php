<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

// ambil data user
$users = mysqli_query($koneksi, "SELECT * FROM users ORDER BY id_users ASC");

// ambil data user untuk edit
$editUser = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = mysqli_query($koneksi, "SELECT * FROM users WHERE id_users=$id");
    $editUser = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen User | E-CAT</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- INLINE CSS -->
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
            position: fixed;
            overflow-y: auto;          
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

        /* ===== CONTENT ===== */
        .content {
            margin-left: 260px;
            padding: 30px;
            width: calc(100% - 260px);
        }

        h1 {
            margin-top: 0;
        }

        /* ===== CARD ===== */
        .card {
            background: #fff;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }

        .card h3 {
            margin-top: 0;
            font-size: 18px;
        }

        /* ===== FORM ===== */
        .form-group {
            margin-top: 16px;
        }

        .form-group label {
            font-size: 13px;
            display: block;
            margin-bottom: 6px;
        }

        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 14px;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 26px;
        }

        .btn-login {
            flex: 1;
            padding: 12px;
            background: #4ade80;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-login:hover {
            background: #22c55e;
        }

        .btn-back {
            padding: 12px;
            background: #ccc;
            color: #333;
            text-decoration: none;
            border-radius: 12px;
            text-align: center;
            font-weight: 600;
        }

        .btn-back:hover {
            background: #b5b5b5;
        }

        /* ===== TABLE ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-top: 10px;
        }

        table th, table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        table th {
            background: #f0f0f0;
            font-weight: 600;
        }

        table tr:hover {
            background: #f9f9f9;
        }

        table a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 500;
        }

        table a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body class="admin-body">

    <div class="sidebar">
        <h2 class="logo">🐱 E-CAT</h2>
        <ul class="menu">
            <li><a href="dashboardAdmin.php">📊 Dashboard</a></li>
            <li><a class="active" href="manajemenUserAdmin.php">👤 Manajemen User</a></li>
            <li><a href="manajemenBarangAdmin.php">📦 Manajemen Barang</a></li>
            <li><a  href="transaksiAdmin.php">💰 Transaksi Manual</a></li>
            <li><a href="validasiAdmin.php">💰 Validasi Transaksi</a></li>
            <li><a href="riwayatAdmin.php">💰 Riwayat Transaksi</a></li>
            <li><a href="perhitunganAdmin.php">🧮 Metode Perhitungan</a></li>
            <li><a shref="riwayatPerhitungan.php">📑 Riwayat Perhitungan</a></li>
            <li class="logout"><a href="../logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    <div class="content">
        <h1>Manajemen User</h1>

        <!-- FORM -->
        <div class="card">
            <h3><?= $editUser ? "Edit User" : "Tambah User" ?></h3>

            <form method="POST" action="users.php">
                <?php if ($editUser): ?>
                    <input type="hidden" name="id" value="<?= $editUser['id_users'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required
                        value="<?= $editUser['username'] ?? '' ?>"
                        <?= $editUser ? 'readonly' : '' ?>>
                </div>

                <div class="form-group">
                    <label>Password <?= $editUser ? "(kosongkan jika tidak diubah)" : "" ?></label>
                    <input type="password" name="password" <?= $editUser ? 'required' : '' ?>>
                </div>

                <div class="form-actions">
                    <button class="btn-login" name="<?= $editUser ? 'update' : 'tambah' ?>">
                        <?= $editUser ? 'Update' : 'Tambah' ?>
                    </button>

                    <?php if ($editUser): ?>
                        <a href="manajemenUserAdmin.php" class="btn-back">Kembali</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- TABEL -->
        <div class="card" style="margin-top:20px;">
            <h3>Daftar User</h3>

            <table>
                <tr>
                    <th>No</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>

                <?php $no = 1; while ($u = mysqli_fetch_assoc($users)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $u['username'] ?></td>
                        <td><?= $u['password'] ?></td>
                        <td><?= $u['created_at'] ?></td>
                        <td>
                            <a href="?edit=<?= $u['id_users'] ?>">✏️ Edit</a> |
                            <a href="users.php?hapus=<?= $u['id_users'] ?>"
                               onclick="return confirm('Hapus user ini?')">🗑️ Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>

</body>
</html>
