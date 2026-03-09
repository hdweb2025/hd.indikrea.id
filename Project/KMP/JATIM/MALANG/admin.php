<?php 
include 'config.php'; 
include $_SERVER['DOCUMENT_ROOT'] . '/auth.php';
require_admin();
if ($conn) {
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS hasil_desa (id INT AUTO_INCREMENT PRIMARY KEY, desa_id INT NOT NULL, filename VARCHAR(255) NOT NULL, mime VARCHAR(100) NOT NULL, uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX(desa_id))");
}
$msg = '';
if (isset($_GET['update_id'], $_GET['col'], $_GET['val']) && $conn) {
    $id = (int)$_GET['update_id'];
    $col = $_GET['col'];
    $val = $_GET['val'];
    $allowed_cols = ['produksi','terpasang'];
    $allowed_vals = ['✓','✗'];
    if (in_array($col, $allowed_cols, true) && in_array($val, $allowed_vals, true)) {
        $stmt = mysqli_prepare($conn, "UPDATE data_desa SET $col = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $val, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header("Location: admin.php");
        exit;
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['desa_id']) && isset($_FILES['file']) && $conn) {
    $desa_id = (int)$_POST['desa_id'];
    $f = $_FILES['file'];
    if ($f['error'] === UPLOAD_ERR_OK) {
        $mime = function_exists('mime_content_type') ? mime_content_type($f['tmp_name']) : '';
        if ($mime === '') {
            $extm = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (in_array($extm, ['jpg','jpeg'])) $mime = 'image/jpeg';
            elseif ($extm === 'png') $mime = 'image/png';
            elseif ($extm === 'pdf') $mime = 'application/pdf';
        }
        $allowed = ['image/jpeg','image/png','application/pdf'];
        if (in_array($mime, $allowed, true)) {
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            $base = pathinfo($f['name'], PATHINFO_FILENAME);
            $base = preg_replace('/[^a-zA-Z0-9._-]/','_', $base);
            $safeName = $base . '_' . time() . '.' . $ext;
            $dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/uploads/malang/' . $desa_id;
            if (!is_dir($dir)) { mkdir($dir, 0775, true); }
            $target = $dir . '/' . $safeName;
            if (move_uploaded_file($f['tmp_name'], $target)) {
                $stmt = mysqli_prepare($conn, "INSERT INTO hasil_desa (desa_id, filename, mime) VALUES (?,?,?)");
                mysqli_stmt_bind_param($stmt, "iss", $desa_id, $safeName, $mime);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $msg = 'Upload berhasil';
            } else {
                $msg = 'Gagal memindahkan file';
            }
        } else {
            $msg = 'Tipe file tidak diizinkan';
        }
    } else {
        $msg = 'Upload gagal';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Kelola Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3>Panel Update & Upload</h3>
    <?php if ($msg) { echo '<div class="alert alert-info">'.$msg.'</div>'; } ?>
    <table class="table table-sm align-middle">
        <thead>
            <tr>
                <th>Desa</th><th>Produksi</th><th>Terpasang</th><th>Upload</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $res = $conn ? mysqli_query($conn, "SELECT * FROM data_desa ORDER BY id ASC") : false;
            if ($res) {
                while($row = mysqli_fetch_assoc($res)){
                    $id = (int)$row['id'];
                    echo "<tr>
                        <td>".htmlspecialchars($row['nama_desa'], ENT_QUOTES, 'UTF-8')."</td>
                        <td>
                            <a href='admin.php?update_id={$id}&col=produksi&val=✓' class='btn btn-xs btn-success'>✓</a>
                            <a href='admin.php?update_id={$id}&col=produksi&val=✗' class='btn btn-xs btn-danger'>✗</a>
                        </td>
                        <td>
                            <a href='admin.php?update_id={$id}&col=terpasang&val=✓' class='btn btn-xs btn-success'>✓</a>
                            <a href='admin.php?update_id={$id}&col=terpasang&val=✗' class='btn btn-xs btn-danger'>✗</a>
                        </td>
                        <td>
                            <form method='post' enctype='multipart/form-data' class='d-flex gap-2'>
                                <input type='hidden' name='desa_id' value='{$id}'>
                                <input type='file' name='file' class='form-control form-control-sm' accept='.jpg,.jpeg,.png,.pdf' required>
                                <button class='btn btn-sm btn-primary' type='submit'>Upload</button>
                            </form>
                        </td>
                    </tr>";
                }
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html> 
