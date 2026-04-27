<?php
session_start();
require_once "config/db.php";

$barangs = mysqli_query($koneksi, "SELECT * FROM barang ORDER BY id_barang ASC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>E-CAT SHOP</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            font-family: Poppins
        }

        body {
            margin: 0;
            background: #f4f6f9
        }

        header {
            position: fixed;
            top: 0;
            width: 100%;
            background: #f4f6f9;
            border-bottom: 1px solid #ddd;
            z-index: 999
        }

        .header-inner {
            max-width: 1200px;
            margin: auto;
            padding: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center
        }

        nav a {
            margin: 0 10px;
            text-decoration: none;
            color: #333
        }

        nav a:hover {
            color: #4f46e5
        }

        .container {
            max-width: 1200px;
            margin: 140px auto;
            padding: 20px
        }

        .section {
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            margin-bottom: 40px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .08)
        }

        .products {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px
        }

        .product-card {
            background: #fff;
            padding: 16px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .08);
            text-align: center
        }

        .product-card img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 12px
        }

        .price {
            font-weight: 600;
            color: #4f46e5;
            margin-top: 5px;
        }

        .stok {
            font-size: 13px;
            color: #666;
        }
    </style>
</head>

<body>

    <header>
        <div class="header-inner">
            <h2 style="color:#4f46e5">E-CAT SHOP</h2>
            <nav>
                <a href="#tentang">Tentang</a>
                <a href="#produk">Produk</a>
                <a href="#kontak">Kontak</a>
            </nav>
        </div>
    </header>

    <div class="container">

        <!-- TENTANG -->
        <section class="section" id="tentang">
            <h2>Tentang Toko</h2>
            <p>E-CAT SHOP adalah toko makanan dan perlengkapan kucing terpercaya.</p>
        </section>

        <!-- PRODUK -->
        <section class="section" id="produk">
            <h2>Produk</h2><br>
            <div class="products">

                <?php while ($b = mysqli_fetch_assoc($barangs)): ?>
                    <div class="product-card">
                        <img
                            src="<?= $b['gambar'] ? 'admin/uploads/barang/' . $b['gambar'] : 'https://via.placeholder.com/300x200' ?>">

                        <h3><?= $b['nama_barang'] ?></h3>
                        <p class="price">Rp <?= number_format($b['harga']) ?></p>
                        <p class="stok">Stok: <?= $b['stok'] ?></p>
                    </div>
                <?php endwhile; ?>

            </div>
        </section>

        <!-- KONTAK -->
        <section class="section" id="kontak">
            <h2>Kontak</h2>
            <p>📍 Jakarta</p>
            <p>📞 08xxxxxxxx</p>
            <p>📧 ecatshop@gmail.com</p>
        </section>

    </div>

</body>

</html>