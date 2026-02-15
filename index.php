<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* TAMBAH CART */
if (isset($_POST['add_cart'])) {

    $id_barang = (int) $_POST['id_barang'];
    $qty = (int) $_POST['qty'];

    $barang = mysqli_fetch_assoc(
        mysqli_query($koneksi, "SELECT * FROM barang WHERE id_barang='$id_barang'")
    );

    if ($barang && $qty > 0 && $qty <= $barang['stok']) {

        if (isset($_SESSION['cart'][$id_barang])) {
            $_SESSION['cart'][$id_barang]['qty'] += $qty;
        } else {
            $_SESSION['cart'][$id_barang] = [
                'nama' => $barang['nama_barang'],
                'harga' => $barang['harga'],
                'qty' => $qty
            ];
        }
    }
}

/* HAPUS CART */
if (isset($_GET['hapus'])) {
    unset($_SESSION['cart'][$_GET['hapus']]);
}

/* CHECKOUT */
if (isset($_POST['checkout'])) {

    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);

    $total = 0;
    foreach ($_SESSION['cart'] as $c) {
        $total += $c['harga'] * $c['qty'];
    }

    mysqli_query($koneksi, "
        INSERT INTO transaksi (id_users, nama_pembeli, tanggal, total, status)
        VALUES (NULL, '$nama - $no_hp', NOW(), '$total', 'pending')
    ");

    $id_transaksi = mysqli_insert_id($koneksi);

    foreach ($_SESSION['cart'] as $id_barang => $c) {
        $sub = $c['harga'] * $c['qty'];

        mysqli_query($koneksi, "
            INSERT INTO detail_transaksi
            (id_transaksi, id_barang, qty, harga, subtotal)
            VALUES
            ('$id_transaksi', '$id_barang', '{$c['qty']}', '{$c['harga']}', '$sub')
        ");
    }

    $_SESSION['cart'] = [];
    echo "<script>alert('Checkout berhasil!');</script>";
}

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
            background: #f4f6f9;
            scroll-behavior: smooth
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
            font-weight: 500;
            color: #333
        }

        nav a:hover {
            color: #4f46e5
        }

        .btn {
            padding: 10px 14px;
            border: none;
            background: #4ade80;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer
        }

        .container {
            max-width: 1200px;
            margin: 140px auto;
            padding: 20px
        }

        /* SECTION */
        .section {
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            margin-bottom: 40px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .08)
        }

        /* PRODUK */
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

        /* MODAL */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .5);
            z-index: 2000;
        }

        .modal-content {
            background: #fff;
            width: 90%;
            max-width: 700px;
            margin: 5% auto;
            padding: 20px;
            border-radius: 16px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px
        }

        .close {
            cursor: pointer;
            font-size: 22px
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center
        }

        #tentang,
        #produk,
        #kontak {
            scroll-margin-top: 100px;
        }
    </style>
</head>

<body>

    <header>
        <div class="header-inner">
            <h2 style="color:#4f46e5">E-CAT SHOP</h2>

            <nav>
                <a href="#tentang">Tentang Toko</a>
                <a href="#produk">Produk</a>
                <a href="#kontak">Kontak</a>
            </nav>

            <button class="btn" onclick="openCart()">
                🛒 Keranjang (<?= count($_SESSION['cart']) ?>)
            </button>
        </div>
    </header>

    <div class="container">

        <!-- TENTANG -->
        <section class="section" id="tentang">
            <h2>Tentang E-CAT SHOP</h2>
            <p>
                E-CAT SHOP adalah toko makanan dan perlengkapan kucing terpercaya.
                Kami menyediakan produk berkualitas dengan harga terjangkau.
            </p>
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
                        <p>Rp <?= number_format($b['harga']) ?></p>
                        <p>Stok: <?= $b['stok'] ?></p>

                        <form method="POST">
                            <input type="hidden" name="id_barang" value="<?= $b['id_barang'] ?>">
                            <input type="number" name="qty" value="1" min="1" max="<?= $b['stok'] ?>">
                            <br><br>
                            <button name="add_cart" class="btn">+ Keranjang</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

        <!-- KONTAK -->
        <section class="section" id="kontak">
            <h2>Kontak</h2>
            <p>📍 Alamat: Jakarta</p>
            <p>📞 WhatsApp: 08xxxxxxxx</p>
            <p>📧 Email: ecatshop@gmail.com</p>
        </section>

    </div>

    <!-- MODAL CART -->
    <div class="modal" id="cartModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>🛒 Keranjang</h2>
                <span class="close" onclick="closeCart()">×</span>
            </div>

            <?php if (!empty($_SESSION['cart'])): ?>
                <table>
                    <tr>
                        <th>Produk</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                    <?php
                    $total = 0;
                    foreach ($_SESSION['cart'] as $id => $c):
                        $sub = $c['qty'] * $c['harga'];
                        $total += $sub;
                        ?>
                        <tr>
                            <td><?= $c['nama'] ?></td>
                            <td><?= $c['qty'] ?></td>
                            <td><?= number_format($c['harga']) ?></td>
                            <td><?= number_format($sub) ?></td>
                            <td><a href="?hapus=<?= $id ?>">❌</a></td>
                        </tr>
                    <?php endforeach; ?>
                </table>

                <h3>Total: Rp <?= number_format($total) ?></h3>

                <form method="POST">
                    <input type="text" name="nama" placeholder="Nama" required>
                    <input type="text" name="no_hp" placeholder="No HP" required>
                    <br><br>
                    <button name="checkout" class="btn">Checkout</button>
                </form>

            <?php else: ?>
                <p>Keranjang kosong</p>
            <?php endif; ?>

        </div>
    </div>

    <script>
        function openCart() {
            document.getElementById('cartModal').style.display = 'block';
        }

        function closeCart() {
            document.getElementById('cartModal').style.display = 'none';
        }

        // ULTRA SMOOTH SCROLL (ANTI KASAR TOTAL)
        const headerOffset = 110; // tinggi header
        const duration = 700; // makin gede makin halus

        function smoothScrollTo(targetY) {
            const startY = window.pageYOffset;
            const distance = targetY - startY;
            let startTime = null;

            function animation(currentTime) {
                if (!startTime) startTime = currentTime;
                const timeElapsed = currentTime - startTime;
                const progress = Math.min(timeElapsed / duration, 1);

                // easing (biar ga kaku)
                const ease = progress < 0.5
                    ? 2 * progress * progress
                    : 1 - Math.pow(-2 * progress + 2, 2) / 2;

                window.scrollTo(0, startY + distance * ease);

                if (timeElapsed < duration) {
                    requestAnimationFrame(animation);
                }
            }

            requestAnimationFrame(animation);
        }

        document.querySelectorAll('nav a').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();

                const target = document.querySelector(this.getAttribute('href'));
                if (!target) return;

                const targetPosition =
                    target.getBoundingClientRect().top +
                    window.pageYOffset -
                    headerOffset;

                smoothScrollTo(targetPosition);
            });
        });
    </script>



</body>

</html>