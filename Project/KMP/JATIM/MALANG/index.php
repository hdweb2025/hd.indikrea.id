<?php require_once __DIR__ . '/config.php'; ?>
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
        .sym { font-family: "Segoe UI Symbol","Noto Sans Symbols","DejaVu Sans",Arial,sans-serif; }
    </style>
</head>
<body class="bg-light">
<div class="container my-5 shadow-sm p-4 bg-white rounded">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/auth.php'; ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Letter Akrilik Koperasi Merah Putih</h2>
        <h2 class="mb-0">Kab. Malang Jatim</h2>
        <?php if (is_admin()) { ?>
        <div class="btn-group">
            <a href="/project.html" class="btn btn-outline-primary btn-sm">Project</a>
            <a href="/Project/KMP/JATIM/MALANG/admin.php" class="btn btn-success btn-sm">Tambah Project</a>
        </div>
        <?php } ?>
    </div>
    <table class="table table-bordered table-striped">
        <thead class="table-dark text-center">
            <tr>
                <th>No</th><th>Nama Desa</th><th>Kecamatan</th><th>Produksi</th><th>Terpasang</th><th>Keterangan</th><th>Mulai</th><th>Target</th><th>Hasil</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!function_exists('mysqli_query') || !isset($conn) || !$conn) {
                echo "<tr><td colspan='9' class='text-center text-danger'>Terjadi kesalahan koneksi database.</td></tr>";
            } else {
                @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS hasil_desa (id INT AUTO_INCREMENT PRIMARY KEY, desa_id INT NOT NULL, filename VARCHAR(255) NOT NULL, mime VARCHAR(100) NOT NULL, uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX(desa_id))");
                $pdfMap = [];
                $qpdf = @mysqli_query($conn, "SELECT id, desa_id, filename FROM hasil_desa WHERE mime='application/pdf' ORDER BY uploaded_at DESC");
                if ($qpdf) {
                    while($p = mysqli_fetch_assoc($qpdf)) {
                        $did = (int)$p['desa_id'];
                        if (!isset($pdfMap[$did])) { $pdfMap[$did] = $p; }
                    }
                }
                $sql = "SELECT * FROM data_desa ORDER BY id ASC";
                $res = mysqli_query($conn, $sql);
                if (!$res) {
                    echo "<tr><td colspan='9' class='text-center text-danger'>Gagal memuat data.</td></tr>";
                } else {
                    if (mysqli_num_rows($res) === 0) {
                        echo "<tr><td colspan='9' class='text-center'>Tidak ada data.</td></tr>";
                    } else {
                        $no = 1;
                        while($row = mysqli_fetch_assoc($res)) {
                            $nama = htmlspecialchars($row['nama_desa'], ENT_QUOTES, 'UTF-8');
                            $kec  = htmlspecialchars($row['kecamatan'], ENT_QUOTES, 'UTF-8');
                            $prod = ($row['produksi'] === '✓') ? '<span class="check-green sym">&#10003;</span>' : '<span class="cross-red sym">&#10007;</span>';
                            $pasang = ($row['terpasang'] === '✓') ? '<span class="check-green sym">&#10003;</span>' : '<span class="cross-red sym">&#10007;</span>';
                            $ket = htmlspecialchars((string)($row['keterangan'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $mulai = !empty($row['tgl_mulai']) ? date('d M Y', strtotime($row['tgl_mulai'])) : '-';
                            $target = !empty($row['target_selesai']) ? date('d M Y', strtotime($row['target_selesai'])) : '-';
                            $desaId = (int)$row['id'];
                            $lihatLink = '<a class="btn btn-sm btn-link" href="/Project/KMP/JATIM/MALANG/hasil.php?desa='.$desaId.'">Lihat</a>';
                            $hasilLink = '<div class="d-flex gap-2">'.$lihatLink;
                            if (isset($pdfMap[$desaId])) {
                                $hasilLink .= '<a class="btn btn-sm btn-secondary" href="/Project/KMP/JATIM/MALANG/download.php?id='.(int)$pdfMap[$desaId]['id'].'">Unduh PDF</a>';
                            }
                            $hasilLink .= '</div>';
                            echo "<tr>
                                    <td class='text-center'>{$no}</td>
                                    <td>{$nama}</td>
                                    <td>{$kec}</td>
                                    <td class='text-center'>{$prod}</td>
                                    <td class='text-center'>{$pasang}</td>
                                    <td>{$ket}</td>
                                    <td class='text-nowrap'>{$mulai}</td>
                                    <td class='text-nowrap'>{$target}</td>
                                    <td class='text-center'>{$hasilLink}</td>
                                  </tr>";
                            $no++;
                        }
                    }
                }
            }
            ?>
        </tbody>
    </table>
    <div class="mt-4 d-flex justify-content-end">
        <a href="/Project/KMP/JATIM/MALANG/export_pdf.php" target="_blank" class="btn btn-outline-dark">
            Download PDF
        </a>
    </div>
</div>
</body>
</html>
