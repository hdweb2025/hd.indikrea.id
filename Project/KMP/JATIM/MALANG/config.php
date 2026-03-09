<?php
$host = "localhost";
$user = "koperasi_mp";
$pass = "CpAz:x!2";
$db   = "koperasi_mp";

$conn = @mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    $db_error = "DB connection failed";
    error_log("DB connection failed: " . mysqli_connect_error());
} else {
    mysqli_set_charset($conn, 'utf8mb4');
}
?>
