<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Laporan Produksi Koperasi MP</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
            if (!$res) {
                echo "<tr><td colspan='5' class='text-center text-danger'>Gagal memuat data.</td></tr>";
            } else {
                if (mysqli_num_rows($res) === 0) {
                    echo "<tr><td colspan='5' class='text-center'>Tidak ada data.</td></tr>";
                } else {
                    $no = 1;
                    while($row = mysqli_fetch_assoc($res)) {
                        $nama = htmlspecialchars($row['nama_desa'], ENT_QUOTES, 'UTF-8');
                        $kec  = htmlspecialchars($row['kecamatan'], ENT_QUOTES, 'UTF-8');
                        $prod = ($row['produksi'] === '✓') ? '<span class="check-green">✓</span>' : '<span class="cross-red">✗</span>';
                        $pasang = ($row['terpasang'] === '✓') ? '<span class="check-green">✓</span>' : '<span class="cross-red">✗</span>';
                        echo "<tr>
                                <td class='text-center'>{$no}</td>
                                <td>{$nama}</td>
                                <td>{$kec}</td>
                                <td class='text-center'>{$prod}</td>
                                <td class='text-center'>{$pasang}</td>
                              </tr>";
                        $no++;
                    }
                }
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html>
