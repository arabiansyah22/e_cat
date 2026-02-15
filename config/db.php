<?php
$koneksi = mysqli_connect("localhost", "root", "", "e_cat");

if (mysqli_connect_errno()) {
    echo "Koneksi database gagal : " . mysqli_connect_error();
}
?>