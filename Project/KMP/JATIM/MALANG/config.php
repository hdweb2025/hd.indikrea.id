<?php
$host = "localhost";
$user = "u573188607_koperasi_mp";
$pass = "@q;~@p;bw0Rp";
$db   = "u573188607_koperasi_mp";

$conn = @mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    $db_error = "DB connection failed";
    error_log("DB connection failed: " . mysqli_connect_error());
} else {
    mysqli_set_charset($conn, 'utf8mb4');
}
?>
