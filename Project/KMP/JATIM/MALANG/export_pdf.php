<?php
// Autoload Dompdf dengan beberapa kemungkinan lokasi
$autoloads = [
    __DIR__ . '/vendor/autoload.php',
    $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php',
    __DIR__ . '/dompdf/autoload.inc.php'
];
foreach ($autoloads as $a) {
    if (is_file($a)) { require_once $a; break; }
}
include __DIR__ . '/config.php';

date_default_timezone_set('Asia/Jakarta');

// Ambil data dari view; jika gagal, fallback dari data_desa
$res_tabel = @mysqli_query($conn, "SELECT * FROM view_laporan_pdf");
$res_rekap = @mysqli_query($conn, "SELECT * FROM view_rekap_pdf");

$rows = [];
if ($res_tabel && mysqli_num_rows($res_tabel) > 0) {
    while ($r = mysqli_fetch_assoc($res_tabel)) { $rows[] = $r; }
} else {
    // Fallback: bangun dari data_desa
    $q = @mysqli_query($conn, "SELECT id, nama_desa, kecamatan, produksi, terpasang, keterangan FROM data_desa ORDER BY id ASC");
    $no = 1;
    if ($q) {
        while($r = mysqli_fetch_assoc($q)) {
            $rows[] = [
                'No' => $no++,
                'Nama Desa / Kelurahan' => $r['nama_desa'],
                'Kecamatan' => $r['kecamatan'],
                'Produksi' => $r['produksi'],
                'Terpasang' => $r['terpasang'],
                'Ket' => $r['keterangan'] ?? ''
            ];
        }
    }
}

$rekaps = [];
if ($res_rekap && mysqli_num_rows($res_rekap) > 0) {
    while ($rk = mysqli_fetch_assoc($res_rekap)) { $rekaps[] = $rk; }
} else {
    // Fallback rekap sederhana
    $total = count($rows);
    $prod = 0; $pasang = 0;
    foreach($rows as $r) {
        if (($r['Produksi'] ?? '') === '✓') $prod++;
        if (($r['Terpasang'] ?? '') === '✓') $pasang++;
    }
    $rekaps = [
        ['Label' => 'Total Desa', 'Jumlah' => $total],
        ['Label' => 'Sudah Produksi', 'Jumlah' => $prod],
        ['Label' => 'Sudah Terpasang', 'Jumlah' => $pasang]
    ];
}

// Susun HTML (inti konten)
$html = '
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10pt; }
        .header { text-align: center; font-weight: bold; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #000; padding: 4px; }
        th { background-color: #f2f2f2; text-align: center; }
        .center { text-align: center; }
        .rekap-table { width: 50%; margin-top: 12px; border: none; }
        .rekap-table td { border: none; padding: 2px 4px; }
        .footer-note { font-size: 8pt; margin-top: 10px; }
        .sym { font-family: "DejaVu Sans", "Noto Sans Symbols", Arial, sans-serif; }
    </style>
</head>
<body>
    <div class="header">
        LETTER AKRILIK KOPERASI MERAH PUTIH<br/>
        KAB. MALANG JATIM
    </div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Desa / Kelurahan</th>
                <th>Kecamatan</th>
                <th>Produksi</th>
                <th>Terpasang</th>
                <th>Ket</th>
            </tr>
        </thead>
        <tbody>';

foreach ($rows as $row) {
    $no = htmlspecialchars((string)$row['No'], ENT_QUOTES, 'UTF-8');
    $desa = htmlspecialchars((string)$row['Nama Desa / Kelurahan'], ENT_QUOTES, 'UTF-8');
    $kec = htmlspecialchars((string)$row['Kecamatan'], ENT_QUOTES, 'UTF-8');
    $rawProd = (string)($row['Produksi'] ?? '');
    $rawTer = (string)($row['Terpasang'] ?? '');
    $isProd = in_array($rawProd, ['✓','✔','Y','Ya','1'], true);
    $isTer = in_array($rawTer, ['✓','✔','Y','Ya','1'], true);
    $prod = $isProd ? '&#10003;' : '&#10007;';
    $ter = $isTer ? '&#10003;' : '&#10007;';
    $ket = htmlspecialchars((string)($row['Ket'] ?? ''), ENT_QUOTES, 'UTF-8');
    $html .= '<tr>
        <td class="center">'.$no.'</td>
        <td>'.$desa.'</td>
        <td>'.$kec.'</td>
        <td class="center sym">'.$prod.'</td>
        <td class="center sym">'.$ter.'</td>
        <td>'.$ket.'</td>
    </tr>';
}

$html .= '</tbody></table>';

// Rekapitulasi
$html .= '<table class="rekap-table">';
foreach ($rekaps as $rekap) {
    $label = htmlspecialchars((string)$rekap['Label'], ENT_QUOTES, 'UTF-8');
    $jumlah = htmlspecialchars((string)$rekap['Jumlah'], ENT_QUOTES, 'UTF-8');
    $html .= '<tr><td>'.$label.'</td><td>: '.$jumlah.'</td></tr>';
}
$html .= '</table>';

$html .= '<div class="footer-note">Update: ' . date('l, d F Y') . '</div>';
$html .= '</body></html>';

// Jika Dompdf tersedia, render PDF. Jika tidak, tampilkan HTML siap cetak (fallback)
$hasDompdf = class_exists('Dompdf\\Dompdf');
if ($hasDompdf) {
    // Inisialisasi Dompdf tanpa mereferensikan tipe secara statik (aman untuk linter)
    $optionsClass = '\\Dompdf\\Options';
    $dompdfClass = '\\Dompdf\\Dompdf';
    $options = new $optionsClass();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans'); // dukung UTF-8 (✓/✗)
    $dompdf = new $dompdfClass($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("Laporan_KMP_Jatim_" . date('Ymd') . ".pdf", ["Attachment" => 1]);
    exit;
} else {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>Laporan KMP Jatim - Cetak</title>';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">';
    echo '<style>@media print {.no-print{display:none}}</style></head><body class="bg-light">';
    echo '<div class="container my-3">';
    echo '<div class="d-flex justify-content-between align-items-center mb-2 no-print">';
    echo '<div class="fw-bold">Laporan KMP Jatim</div>';
    echo '<div class="d-flex gap-2"><a class="btn btn-secondary btn-sm" href="/Project/KMP/JATIM/MALANG/index.php">Kembali</a><button class="btn btn-dark btn-sm" onclick="window.print()">Cetak / Simpan PDF</button></div>';
    echo '</div>';
    echo '<div class="bg-white p-3 rounded shadow-sm">';
    echo $html;
    echo '</div></div></body></html>';
    exit;
}
