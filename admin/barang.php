<?php
session_start();
require_once "../config/db.php";

// TAMBAH
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $moisture = $_POST['moisture'];
    $protein = $_POST['protein'];
    $lemak = $_POST['lemak'];
    $crude_fiber = $_POST['crude_fiber'];

    $gambar = null;
    if (!empty($_FILES['gambar']['name'])) {
        $gambar = time() . "_" . $_FILES['gambar']['name'];
        move_uploaded_file($_FILES['gambar']['tmp_name'], "uploads/barang/" . $gambar);
    }

    mysqli_query($koneksi, "INSERT INTO barang VALUES (
        null,'$nama','$harga','$stok','$gambar',NOW(), '$moisture','$protein','$lemak','$crude_fiber'
    )");

    header("Location: manajemenBarangAdmin.php");
}

// UPDATE
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $moisture = $_POST['moisture'];
    $protein = $_POST['protein'];
    $lemak = $_POST['lemak'];
    $crude_fiber = $_POST['crude_fiber'];

    if (!empty($_FILES['gambar']['name'])) {
        $gambar = time() . "_" . $_FILES['gambar']['name'];
        move_uploaded_file($_FILES['gambar']['tmp_name'], "uploads/barang/" . $gambar);
        mysqli_query($koneksi, "UPDATE barang SET gambar='$gambar' WHERE id_barang=$id");
    }

    mysqli_query($koneksi, "UPDATE barang SET
        nama_barang='$nama',
        harga='$harga',
        stok='$stok',
        moisture='$moisture',
        protein='$protein',
        lemak='$lemak',
        crude_fiber='$crude_fiber'
        WHERE id_barang=$id");

    header("Location: manajemenBarangAdmin.php");
}

// HAPUS
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];

    // Cek apakah barang dipakai di detail_transaksi
    $cek = mysqli_query($koneksi, "
        SELECT id_barang 
        FROM detail_transaksi 
        WHERE id_barang = $id
    ");

    if (mysqli_num_rows($cek) > 0) {
        echo "<script>
            alert('Barang tidak bisa dihapus karena sudah ada di transaksi!');
            window.location='manajemenBarangAdmin.php';
        </script>";
        exit;
    }

    mysqli_query($koneksi, "DELETE FROM barang WHERE id_barang=$id");
    header("Location: manajemenBarangAdmin.php");
}

