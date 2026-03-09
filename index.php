<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Laporan Produksi Koperasi MP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .check-green { color: green; font-weight: bold; }
        .cross-red { color: red; font-weight: bold; }
    </style>
</head>
<body class="bg-light">
<div class="container my-5 shadow-sm p-4 bg-white rounded">
    <h2 class="text-center mb-4">Laporan Pemasangan Letter Akrilik</h2>
    <table class="table table-bordered table-striped">
        <thead class="table-dark text-center">
            <tr>
                <th>No</th><th>Nama Desa</th><th>Kecamatan</th><th>Produksi</th><th>Terpasang</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM data_desa ORDER BY id ASC";
            $res = mysqli_query($conn, $sql);
            $no = 1;
            while($row = mysqli_fetch_assoc($res)) {
                $prod = ($row['produksi'] == '✓') ? '<span class="check-green">✓</span>' : '<span class="cross-red">✗</span>';
                $pasang = ($row['terpasang'] == '✓') ? '<span class="check-green">✓</span>' : '<span class="cross-red">✗</span>';
                echo "<tr>
                        <td class='text-center'>{$no}</td>
                        <td>{$row['nama_desa']}</td>
                        <td>{$row['kecamatan']}</td>
                        <td class='text-center'>{$prod}</td>
                        <td class='text-center'>{$pasang}</td>
                      </tr>";
                $no++;
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html>