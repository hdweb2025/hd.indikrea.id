<?php
$host = "localhost";
$user = "koperasi_mp";
$pass = "CpAz:x!2";
$db   = "koperasi_mp";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) { die("Koneksi Gagal: " . mysqli_connect_error()); }
mysqli_set_charset($conn, 'utf8mb4');
?>
