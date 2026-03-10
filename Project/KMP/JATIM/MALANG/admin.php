<?php 
include 'config.php'; 
include $_SERVER['DOCUMENT_ROOT'] . '/auth.php';
require_admin();
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
if ($conn) {
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS hasil_desa (id INT AUTO_INCREMENT PRIMARY KEY, desa_id INT NOT NULL, filename VARCHAR(255) NOT NULL, mime VARCHAR(100) NOT NULL, uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX(desa_id))");
}
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status' && $conn) {
    $token = $_POST['csrf'] ?? '';
    if (hash_equals($_SESSION['csrf'], $token)) {
        $id = (int)($_POST['desa_id'] ?? 0);
        $col = $_POST['col'] ?? '';
        $val = $_POST['val'] ?? '';
        $allowed_cols = ['produksi','terpasang'];
        $allowed_vals = ['✓','✗'];
        if ($id > 0 && in_array($col, $allowed_cols, true) && in_array($val, $allowed_vals, true)) {
            $stmt = mysqli_prepare($conn, "UPDATE data_desa SET $col = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $val, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            header("Location: admin.php");
            exit;
        }
    }
}
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
    <h3 class="mb-3">Panel Update & Upload</h3>
    <?php if ($msg) { echo '<div class="alert alert-info">'.$msg.'</div>'; } ?>
    <table class="table table-sm align-middle table-bordered">
        <thead>
            <tr>
                <th style="width:30%">Desa</th>
                <th class="text-center" style="width:20%">Produksi</th>
                <th class="text-center" style="width:20%">Terpasang</th>
                <th style="width:30%">Upload</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $res = $conn ? mysqli_query($conn, "SELECT * FROM data_desa ORDER BY id ASC") : false;
            if ($res) {
                while($row = mysqli_fetch_assoc($res)){
                    $id = (int)$row['id'];
                    $nama = htmlspecialchars($row['nama_desa'], ENT_QUOTES, 'UTF-8');
                    $pOn = ($row['produksi'] === '✓');
                    $tOn = ($row['terpasang'] === '✓');
                    echo "<tr>";
                    echo "<td>{$nama}</td>";
                    echo "<td class='text-center'>
                            <div class='mb-1'>
                                <span class='badge ".($pOn?'bg-success':'bg-secondary')."'>".($pOn?'✓':'✗')."</span>
                            </div>
                            <div class='d-inline-flex gap-1'>
                                <form method='post'>
                                  <input type='hidden' name='csrf' value='{$_SESSION['csrf']}'>
                                  <input type='hidden' name='action' value='update_status'>
                                  <input type='hidden' name='desa_id' value='{$id}'>
                                  <input type='hidden' name='col' value='produksi'>
                                  <input type='hidden' name='val' value='✓'>
                                  <button class='btn btn-sm btn-success' ".($pOn?'disabled':'')." type='submit'>✓</button>
                                </form>
                                <form method='post'>
                                  <input type='hidden' name='csrf' value='{$_SESSION['csrf']}'>
                                  <input type='hidden' name='action' value='update_status'>
                                  <input type='hidden' name='desa_id' value='{$id}'>
                                  <input type='hidden' name='col' value='produksi'>
                                  <input type='hidden' name='val' value='✗'>
                                  <button class='btn btn-sm btn-danger' ".(!$pOn?'disabled':'')." type='submit'>✗</button>
                                </form>
                            </div>
                          </td>";
                    echo "<td class='text-center'>
                            <div class='mb-1'>
                                <span class='badge ".($tOn?'bg-success':'bg-secondary')."'>".($tOn?'✓':'✗')."</span>
                            </div>
                            <div class='d-inline-flex gap-1'>
                                <form method='post'>
                                  <input type='hidden' name='csrf' value='{$_SESSION['csrf']}'>
                                  <input type='hidden' name='action' value='update_status'>
                                  <input type='hidden' name='desa_id' value='{$id}'>
                                  <input type='hidden' name='col' value='terpasang'>
                                  <input type='hidden' name='val' value='✓'>
                                  <button class='btn btn-sm btn-success' ".($tOn?'disabled':'')." type='submit'>✓</button>
                                </form>
                                <form method='post'>
                                  <input type='hidden' name='csrf' value='{$_SESSION['csrf']}'>
                                  <input type='hidden' name='action' value='update_status'>
                                  <input type='hidden' name='desa_id' value='{$id}'>
                                  <input type='hidden' name='col' value='terpasang'>
                                  <input type='hidden' name='val' value='✗'>
                                  <button class='btn btn-sm btn-danger' ".(!$tOn?'disabled':'')." type='submit'>✗</button>
                                </form>
                            </div>
                          </td>";
                    echo "<td>
                            <form method='post' enctype='multipart/form-data' class='d-flex gap-2'>
                                <input type='hidden' name='desa_id' value='{$id}'>
                                <input type='file' name='file' class='form-control form-control-sm' accept='.jpg,.jpeg,.png,.pdf' required>
                                <button class='btn btn-sm btn-primary' type='submit'>Upload</button>
                                <a class='btn btn-sm btn-outline-secondary' href='/Project/KMP/JATIM/MALANG/hasil.php?desa={$id}' target='_blank'>Lihat</a>
                            </form>
                          </td>";
                    echo "</tr>";
                }
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html> 
