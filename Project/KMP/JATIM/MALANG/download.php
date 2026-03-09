<?php
require_once __DIR__ . '/config.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$conn || $id <= 0) { http_response_code(404); exit; }
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS hasil_desa (id INT AUTO_INCREMENT PRIMARY KEY, desa_id INT NOT NULL, filename VARCHAR(255) NOT NULL, mime VARCHAR(100) NOT NULL, uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX(desa_id))");
$q = mysqli_query($conn, "SELECT * FROM hasil_desa WHERE id=".$id." LIMIT 1");
if (!$q || mysqli_num_rows($q) === 0) { http_response_code(404); exit; }
$f = mysqli_fetch_assoc($q);
if ($f['mime'] !== 'application/pdf') { http_response_code(403); exit; }
$path = $_SERVER['DOCUMENT_ROOT'].'/assets/uploads/malang/'.$f['desa_id'].'/'.$f['filename'];
if (!is_file($path)) { http_response_code(404); exit; }
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="'.basename($path).'"');
header('Content-Length: '.filesize($path));
readfile($path);
exit;
