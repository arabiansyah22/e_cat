<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

/* ================= TAMBAH USER ================= */
if (isset($_POST['tambah'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek apakah username sudah ada
    $cekStmt = mysqli_prepare($koneksi, "SELECT id_users FROM users WHERE username=?");
    mysqli_stmt_bind_param($cekStmt, "s", $username);
    mysqli_stmt_execute($cekStmt);
    mysqli_stmt_store_result($cekStmt);

    if (mysqli_stmt_num_rows($cekStmt) > 0) {
        // Username sudah ada
        echo "<script>alert('Username sudah digunakan, silakan pilih yang lain.'); window.location='manajemenUserAdmin.php';</script>";
        exit;
    }

    // Jika username belum ada, lanjut insert
    $stmt = mysqli_prepare($koneksi, "INSERT INTO users (username, password) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);

    header("Location: manajemenUserAdmin.php");
    exit;
}

/* ================= UPDATE USER ================= */
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];

    if (!empty($_POST['password'])) {
        $password = $_POST['password'];
        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE users SET username=?, password=? WHERE id_users=?"
        );
        mysqli_stmt_bind_param($stmt, "ssi", $username, $password, $id);
    } else {
        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE users SET username=? WHERE id=?"
        );
        mysqli_stmt_bind_param($stmt, "si", $username, $id);
    }

    mysqli_stmt_execute($stmt);
    header("Location: manajemenUserAdmin.php");
    exit;
}

/* ================= HAPUS USER ================= */
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    $stmt = mysqli_prepare($koneksi, "DELETE FROM users WHERE id_users=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    header("Location: manajemenUserAdmin.php");
    exit;
}
