<?php 
include 'config.php'; 
include $_SERVER['DOCUMENT_ROOT'] . '/auth.php';
require_admin();
// Logika Update Sederhana
if(isset($_GET['update_id'])){
    $id = $_GET['update_id'];
    $col = $_GET['col'];
    $val = $_GET['val'];
    mysqli_query($conn, "UPDATE data_desa SET $col = '$val' WHERE id = $id");
    header("Location: admin.php");
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
    <h3>Panel Update Produksi & Pasang</h3>
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Desa</th><th>Produksi</th><th>Terpasang</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $res = mysqli_query($conn, "SELECT * FROM data_desa");
            while($row = mysqli_fetch_assoc($res)){
                echo "<tr>
                    <td>{$row['nama_desa']}</td>
                    <td>
                        <a href='admin.php?update_id={$row['id']}&col=produksi&val=✓' class='btn btn-xs btn-success'>✓</a>
                        <a href='admin.php?update_id={$row['id']}&col=produksi&val=✗' class='btn btn-xs btn-danger'>✗</a>
                    </td>
                    <td>
                        <a href='admin.php?update_id={$row['id']}&col=terpasang&val=✓' class='btn btn-xs btn-success'>✓</a>
                        <a href='admin.php?update_id={$row['id']}&col=terpasang&val=✗' class='btn btn-xs btn-danger'>✗</a>
                    </td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html> 
