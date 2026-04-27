<?php
session_start();
require_once "../config/db.php";

/* ===============================
   CEK LOGIN
================================ */
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

/* ===============================
   AMANKAN SESSION ID USER
================================ */
$id_users = isset($_SESSION['id_users']) ? $_SESSION['id_users'] : 0;

/* ===============================
   AMBIL SEMUA DATA BARANG
================================ */
$dataBarang = [];
$qBarang = mysqli_query($koneksi, "SELECT * FROM barang");
while ($b = mysqli_fetch_assoc($qBarang)) {
    $dataBarang[] = $b;
}

$kriteria = ['harga', 'moisture', 'protein', 'lemak', 'crude_fiber'];

/* ===============================
   AMBIL BOBOT
================================ */
$bobot = [];
$tipe = [];

$qBobot = mysqli_query($koneksi, "SELECT * FROM bobot_kriteria");
while ($b = mysqli_fetch_assoc($qBobot)) {
    $bobot[$b['nama_kriteria']] = $b['bobot'];
    $tipe[$b['nama_kriteria']] = $b['tipe'];
}

/* =============================== */
$dataDiproses = [];
$ranking = [];
$normalisasi = [];
$terbobot = [];
$error = "";

/* ===============================
   PROSES HITUNG
================================ */
if (isset($_POST['simpan_bobot'])) {

    if (empty($_POST['produk'])) {
        $error = "❌ Pilih minimal 1 produk!";
    } else {

        $produkDipilih = $_POST['produk'];

        foreach ($dataBarang as $d) {
            if (in_array($d['id_barang'], $produkDipilih)) {
                $dataDiproses[] = $d;
            }
        }

        $bobotInput = [
            'harga' => $_POST['harga'],
            'moisture' => $_POST['moisture'],
            'protein' => $_POST['protein'],
            'lemak' => $_POST['lemak'],
            'crude_fiber' => $_POST['crude_fiber']
        ];

        if (abs(array_sum($bobotInput) - 1) > 0.001) {
            $error = "❌ Total bobot harus = 1";
        } else {

            mysqli_query($koneksi, "DELETE FROM bobot_kriteria");

            $tipeInput = [
                'harga' => 'cost',
                'moisture' => 'benefit',
                'protein' => 'benefit',
                'lemak' => 'cost',
                'crude_fiber' => 'benefit'
            ];

            foreach ($bobotInput as $k => $v) {
                mysqli_query(
                    $koneksi,
                    "INSERT INTO bobot_kriteria (nama_kriteria,bobot,tipe)
                     VALUES ('$k','$v','{$tipeInput[$k]}')"
                );
                $bobot[$k] = $v;
                $tipe[$k] = $tipeInput[$k];
            }

            /* ===============================
               MOORA
            ================================ */

            // Normalisasi
            $pembagi = [];
            foreach ($kriteria as $k) {
                $sum = 0;
                foreach ($dataDiproses as $d) {
                    $sum += pow($d[$k], 2);
                }
                $pembagi[$k] = sqrt($sum);
            }

            foreach ($dataDiproses as $d) {
                foreach ($kriteria as $k) {
                    $normalisasi[$d['id_barang']][$k] =
                        $pembagi[$k] == 0 ? 0 : $d[$k] / $pembagi[$k];
                }
            }

            // Terbobot
            foreach ($normalisasi as $id => $row) {
                foreach ($kriteria as $k) {
                    $terbobot[$id][$k] = $row[$k] * $bobot[$k];
                }
            }

            // Hitung Yi
            $optimasi = [];
            foreach ($terbobot as $id => $row) {
                $nilai = 0;
                foreach ($kriteria as $k) {
                    $nilai += ($tipe[$k] == 'benefit') ? $row[$k] : -$row[$k];
                }
                $optimasi[$id] = $nilai;
            }

            foreach ($dataDiproses as $d) {
                $ranking[] = [
                    'id' => $d['id_barang'],
                    'nama' => $d['nama_barang'],
                    'nilai' => $optimasi[$d['id_barang']]
                ];
            }

            usort($ranking, function ($a, $b) {
                return $b['nilai'] <=> $a['nilai'];
            });

            /* ===============================
               SIMPAN RIWAYAT
            ================================ */

            if ($id_users != 0) {

                mysqli_query($koneksi, "
                    INSERT INTO riwayat_moora (id_users, tanggal)
                    VALUES ('$id_users', NOW())
                ");

                $idRiwayat = mysqli_insert_id($koneksi);

                $rank = 1;
                foreach ($ranking as $r) {
                    mysqli_query(
                        $koneksi,
                        "INSERT INTO riwayat_moora_detail
                        (id_riwayat,id_barang,nilai_yi,ranking)
                        VALUES
                        ('$idRiwayat','{$r['id']}','{$r['nilai']}','$rank')"
                    );
                    $rank++;
                }
            }
        }
    }
}

/* ===============================
   FUNCTION NAMA BARANG
================================ */
function namaBarang($id, $data)
{
    foreach ($data as $d) {
        if ($d['id_barang'] == $id) {
            return $d['nama_barang'];
        }
    }
    return "-";
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Perhitungan MOORA</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        /* === CSS DASHBOARD (ASLI) === */
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

        /* === TAMBAHAN KHUSUS TABEL (AMAN) === */
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
    </style>
</head>

<body class="admin-body">

    <div class="sidebar">
        <h2 class="logo">🐱 E-CAT</h2>
        <ul class="menu">
            <li><a href="dashboardAdmin.php">📊 Dashboard</a></li>
            <li><a href="manajemenUserAdmin.php">👤 Manajemen User</a></li>
            <li><a href="manajemenBarangAdmin.php">📦 Manajemen Barang</a></li>
            <li><a class="active" href="perhitunganAdmin.php">🧮 Metode Perhitungan</a></li>
            <li><a href="riwayatPerhitungan.php">📑 Riwayat Perhitungan</a></li>
            <li class="logout"><a href="../logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    <div class="content">
        <h1>Metode MOORA</h1>

        <div class="card">
            <h3>Pilih Produk & Input Bobot</h3>

            <?php if (!empty($error))
                echo "<p style='color:red'>$error</p>"; ?>

            <form method="POST">

                <h4>Pilih Produk:</h4>
                <div
                    style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;margin-bottom:20px;">
                    <?php foreach ($dataBarang as $d): ?>
                        <label style="border:1px solid #ddd;padding:10px;border-radius:10px;cursor:pointer;">
                            <input type="checkbox" name="produk[]" value="<?= $d['id_barang'] ?>">
                            <?= $d['nama_barang'] ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <h4>Input Bobot:</h4>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px;">
                    <?php foreach ($kriteria as $k): ?>
                        <div>
                            <label><?= ucfirst(str_replace('_', ' ', $k)) ?></label>
                            <input type="number" step="0.01" name="<?= $k ?>" required
                                style="width:100%;padding:6px;border-radius:6px;border:1px solid #ccc;">
                        </div>
                    <?php endforeach; ?>
                </div>

                <br>
                <button name="simpan_bobot"
                    style="background:#4f46e5;color:#fff;padding:8px 16px;border:none;border-radius:8px;cursor:pointer;">
                    Simpan & Hitung
                </button>

            </form>

        </div>

        <div class="card">
            <h3>Matriks Keputusan</h3>
            <table>
                <tr>
                    <th>Alternatif</th><?php foreach ($kriteria as $k)
                        echo "<th>$k</th>"; ?>
                </tr>
                <?php foreach ($dataBarang as $d): ?>
                    <tr>
                        <td><?= $d['nama_barang'] ?></td>
                        <?php foreach ($kriteria as $k)
                            echo "<td>{$d[$k]}</td>"; ?>
                    </tr>
                <?php endforeach ?>
            </table>
        </div>

        <!-- Jenis Kriteria -->
        <div class="card">
            <h3>Jenis Kriteria</h3>
            <table>
                <tr>
                    <th>Kriteria</th>
                    <th>Tipe</th>
                    <th>Bobot</th>
                </tr>
                <?php foreach ($kriteria as $k): ?>
                    <tr>
                        <td>
                            <?= $k ?>
                        </td>
                        <td>
                            <?= $tipe[$k] ?? '-' ?>
                        </td>
                        <td>
                            <?= $bobot[$k] ?? '-' ?>
                        </td>
                    </tr>
                <?php endforeach ?>
            </table>
        </div>


        <!-- Normalisasi -->
        <div class="card">
            <h3>Normalisasi</h3>
            <p style="text-align:left">
                <b>Penjelasan:</b><br>
                Normalisasi dilakukan untuk menyamakan skala nilai setiap kriteria agar dapat dibandingkan.<br>
                Rumus normalisasi:<br>
                Xij* = Xij / √(∑Xij²)<br><br>

                Keterangan:<br>
                - Xij = nilai asli alternatif terhadap kriteria<br>
                - √(∑Xij²) = akar dari jumlah kuadrat semua nilai pada kriteria tersebut<br><br>

                Nilai pada tabel ini diperoleh dari pembagian setiap nilai dengan hasil akar jumlah kuadrat pada
                masing-masing kriteria.
            </p>
            <table>
                <tr>
                    <th>Alternatif</th>
                    <?php foreach ($kriteria as $k): ?>
                        <th>
                            <?= $k ?>
                        </th>
                    <?php endforeach ?>
                </tr>
                <?php foreach ($normalisasi as $id => $row): ?>
                    <tr>
                        <td>
                            <?= namaBarang($id, $dataDiproses) ?>
                        </td>
                        <?php foreach ($kriteria as $k): ?>
                            <td>
                                <?= round($row[$k], 4) ?>
                            </td>
                        <?php endforeach ?>
                    </tr>
                <?php endforeach ?>
            </table>
        </div>


        <div class="card">
            <h3>Normalisasi × Bobot</h3>
            <p style="text-align:left">
                <b>Penjelasan:</b><br>
                Nilai normalisasi kemudian dikalikan dengan bobot masing-masing kriteria.<br>
                Rumus:<br>
                Vij = Wj × Xij*<br><br>

                Keterangan:<br>
                - Wj = bobot kriteria<br>
                - Xij* = nilai hasil normalisasi<br><br>

                Hasil pada tabel ini menunjukkan nilai setiap alternatif setelah mempertimbangkan tingkat kepentingan
                kriteria.
            </p>
            <table>
                <tr>
                    <th>Alternatif</th>
                    <?php foreach ($kriteria as $k): ?>
                        <th>
                            <?= $k ?>
                        </th>
                    <?php endforeach ?>
                </tr>

                <?php foreach ($terbobot as $id => $row): ?>
                    <tr>
                        <td>
                            <?= namaBarang($id, $dataDiproses) ?>
                        </td>
                        <?php foreach ($kriteria as $k): ?>
                            <td>
                                <?= round($row[$k], 4) ?>
                            </td>
                        <?php endforeach ?>
                    </tr>
                <?php endforeach ?>
            </table>
        </div>

        <!-- Detail Yi -->
        <div class="card">
            <h3>Detail Perhitungan Yi</h3>
            <p style="text-align:left">
                <b>Penjelasan:</b><br>
                Nilai Yi dihitung dengan menjumlahkan semua nilai kriteria bertipe benefit, kemudian dikurangi dengan
                nilai kriteria bertipe cost.<br>
                Rumus:<br>
                Yi = ∑Benefit - ∑Cost<br><br>

                Keterangan:<br>
                - Benefit = kriteria yang semakin besar semakin baik<br>
                - Cost = kriteria yang semakin kecil semakin baik<br><br>

                Pada tabel ini ditampilkan total nilai benefit, total cost, dan hasil akhir Yi dari setiap alternatif.
            </p>
            <table>
                <tr>
                    <th>Alternatif</th>
                    <th>Benefit</th>
                    <th>Cost</th>
                    <th>Yi</th>
                </tr>

                <?php foreach ($terbobot as $id => $row):
                    $benefit = 0;
                    $cost = 0;

                    foreach ($kriteria as $k) {
                        if ($tipe[$k] == 'benefit')
                            $benefit += $row[$k];
                        else
                            $cost += $row[$k];
                    }

                    $yi = $benefit - $cost;
                    ?>
                    <tr>
                        <td>
                            <?= namaBarang($id, $dataDiproses) ?>
                        </td>
                        <td>
                            <?= round($benefit, 4) ?>
                        </td>
                        <td>
                            <?= round($cost, 4) ?>
                        </td>
                        <td>
                            <?= round($yi, 4) ?>
                        </td>
                    </tr>
                <?php endforeach ?>
            </table>
        </div>


        <div class="card">
            <h3>Nilai Yi & Ranking</h3>
            <p style="text-align:left">
                <b>Penjelasan:</b><br>
                Ranking diperoleh berdasarkan nilai Yi terbesar hingga terkecil.<br>
                Alternatif dengan nilai Yi tertinggi merupakan pilihan terbaik.<br><br>

                Semakin besar nilai Yi, maka semakin baik alternatif tersebut berdasarkan kriteria yang digunakan.
            </p>
            <table>
                <tr>
                    <th>Rank</th>
                    <th>Nama</th>
                    <th>Yi</th>
                </tr>
                <?php $r = 1;
                foreach ($ranking as $h): ?>
                    <tr>
                        <td><?= $r++ ?></td>
                        <td><?= $h['nama'] ?></td>
                        <td><?= round($h['nilai'], 4) ?></td>
                    </tr>
                <?php endforeach ?>
            </table>
        </div>
    </div>
</body>

</html>