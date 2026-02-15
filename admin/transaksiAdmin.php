<?php
require_once "../config/db.php";

/* ===============================
   PROSES TAMBAH TRANSAKSI
================================ */
if (isset($_POST['simpan_transaksi'])) {

    $nama_pembeli = $_POST['nama_pembeli'];
    $id_barang = $_POST['id_barang']; // array
    $qty = $_POST['qty']; // array

    // simpan transaksi dulu (total = 0)
    mysqli_query(
        $koneksi,
        "INSERT INTO transaksi (id_users, nama_pembeli, total)
         VALUES (NULL, '$nama_pembeli', 0)"
    );

    $id_transaksi = mysqli_insert_id($koneksi);
    $totalTransaksi = 0;

    // loop barang
    for ($i = 0; $i < count($id_barang); $i++) {

        $qHarga = mysqli_query(
            $koneksi,
            "SELECT harga FROM barang WHERE id_barang='{$id_barang[$i]}'"
        );
        $barang = mysqli_fetch_assoc($qHarga);

        $harga = $barang['harga'];
        $subtotal = $harga * $qty[$i];
        $totalTransaksi += $subtotal;

        mysqli_query(
            $koneksi,
            "INSERT INTO detail_transaksi
            (id_transaksi, id_barang, qty, harga, subtotal)
            VALUES
            ('$id_transaksi', '{$id_barang[$i]}', '{$qty[$i]}', '$harga', '$subtotal')"
        );
    }

    // update total transaksi
    mysqli_query(
        $koneksi,
        "UPDATE transaksi SET total='$totalTransaksi'
         WHERE id_transaksi='$id_transaksi'"
    );

    header("Location: transaksiAdmin.php");
    exit;
}

/* ===============================
   PROSES HAPUS TRANSAKSI
================================ */
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];

    mysqli_query($koneksi, "DELETE FROM detail_transaksi WHERE id_transaksi=$id");
    mysqli_query($koneksi, "DELETE FROM transaksi WHERE id_transaksi=$id");

    header("Location: transaksiAdmin.php");
    exit;
}

/* ===============================
   DATA
================================ */
$dataBarangList = mysqli_query($koneksi, "SELECT * FROM barang");

$dataTransaksi = mysqli_query($koneksi, "
    SELECT 
        t.id_transaksi,
        t.tanggal,
        COALESCE(u.username, t.nama_pembeli) AS nama,
        t.total,
        GROUP_CONCAT(b.nama_barang SEPARATOR '<br>') AS daftar_barang,
        GROUP_CONCAT(dt.qty SEPARATOR '<br>') AS daftar_qty,
        GROUP_CONCAT(CONCAT('Rp', FORMAT(dt.harga,0)) SEPARATOR '<br>') AS daftar_harga
    FROM transaksi t
    LEFT JOIN users u ON t.id_users = u.id_users
    LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
    LEFT JOIN barang b ON dt.id_barang = b.id_barang
    GROUP BY t.id_transaksi
    ORDER BY t.id_transaksi DESC
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Transaksi Manual | E-CAT</title>
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

        /* ===== SIDEBAR ===== */
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

        /* ===== CONTENT ===== */
        .content {
            margin-left: 260px;
            padding: 30px;
            width: calc(100% - 260px);
        }

        h1 {
            margin-top: 0;
        }

        /* ===== CARDS ===== */
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
            font-size: 16px;
            color: #555;
        }

        .card p {
            font-size: 28px;
            font-weight: 600;
            margin-top: 10px;
            color: #4f46e5;
        }

        .table-transaksi {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .table-transaksi th,
        .table-transaksi td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .table-transaksi th {
            background: #f1f5f9;
            font-weight: 600;
            text-align: left;
        }

        .table-transaksi tr:hover {
            background: #f9fafb;
        }

        .btn-hapus {
            color: #dc2626;
            font-weight: 600;
            text-decoration: none;
        }

        .btn-hapus:hover {
            text-decoration: underline;
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
            <li><a class="active" href="transaksiAdmin.php">💰 Transaksi Manual</a></li>
            <li><a href="validasiAdmin.php">💰 Validasi Transaksi</a></li>
            <li><a href="riwayatAdmin.php">💰 Riwayat Transaksi</a></li>
            <li><a href="perhitunganAdmin.php">🧮 Metode Perhitungan</a></li>
            <li><a href="riwayatPerhitungan.php">📑 Riwayat Perhitungan</a></li>
            <li class="logout"><a href="../logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    <div class="content">
        <h1>Transaksi Manual</h1>

        <div class="card">
            <form method="POST">

                <label>Nama Pembeli</label>
                <input type="text" name="nama_pembeli" required style="width:100%;padding:8px;"><br><br>

                <label>Barang</label>

                <div id="listBarang">
                    <div class="barang-row">
                        <select name="id_barang[]" required style="width:65%;padding:8px;">
                            <option value="">-- Pilih Barang --</option>
                            <?php
                            mysqli_data_seek($dataBarangList, 0);
                            while ($b = mysqli_fetch_assoc($dataBarangList)) {
                                ?>
                                <option value="<?= $b['id_barang'] ?>">
                                    <?= $b['nama_barang'] ?> - Rp<?= number_format($b['harga']) ?>
                                </option>
                            <?php } ?>
                        </select>

                        <input type="number" name="qty[]" min="1" required style="width:30%;padding:8px;">
                    </div>
                </div>

                <button type="button" onclick="tambahBarang()">+ Tambah Barang</button><br><br>

                <button type="submit" name="simpan_transaksi"
                    style="padding:10px 20px;background:#4f46e5;color:#fff;border:none;border-radius:8px;">
                    Simpan Transaksi
                </button>
            </form>
        </div>

        <div class="card">
            <h3>Data Transaksi</h3>
            <table class="table-transaksi">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Barang</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1;
                    while ($t = mysqli_fetch_assoc($dataTransaksi)) { ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $t['nama'] ?></td>
                            <td><?= $t['daftar_barang'] ?></td>
                            <td style="text-align:center;"><?= $t['daftar_qty'] ?></td>
                            <td><?= $t['daftar_harga'] ?></td>
                            <td><?= $t['tanggal'] ?></td>
                            <td><b>Rp<?= number_format($t['total']) ?></b></td>
                            <td>
                                <a href="?hapus=<?= $t['id_transaksi'] ?>" onclick="return confirm('Hapus transaksi?')"
                                    class="btn-hapus">
                                    Hapus
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

        </div>
    </div>

    <script>
        function tambahBarang() {
            const row = document.querySelector(".barang-row").cloneNode(true);
            row.querySelectorAll("input").forEach(i => i.value = "");
            row.querySelector("select").selectedIndex = 0;
            document.getElementById("listBarang").appendChild(row);
        }
    </script>

</body>

</html>