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

if (!class_exists('Dompdf\\Dompdf')) {
    http_response_code(500);
    echo "Dompdf tidak ditemukan. Pastikan terpasang via Composer dan vendor/autoload.php dapat diakses.";
    exit;
}

use Dompdf\Dompdf;
use Dompdf\Options;

date_default_timezone_set('Asia/Jakarta');

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans'); // dukung UTF-8 (✓/✗)
$dompdf = new Dompdf($options);

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

// Susun HTML
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
    $prod = htmlspecialchars((string)$row['Produksi'], ENT_QUOTES, 'UTF-8');
    $ter = htmlspecialchars((string)$row['Terpasang'], ENT_QUOTES, 'UTF-8');
    $ket = htmlspecialchars((string)($row['Ket'] ?? ''), ENT_QUOTES, 'UTF-8');
    $html .= '<tr>
        <td class="center">'.$no.'</td>
        <td>'.$desa.'</td>
        <td>'.$kec.'</td>
        <td class="center">'.$prod.'</td>
        <td class="center">'.$ter.'</td>
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

// Render PDF
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Laporan_KMP_Jatim_" . date('Ymd') . ".pdf", ["Attachment" => 1]);
exit;
