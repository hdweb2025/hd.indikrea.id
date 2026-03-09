<?php
require 'vendor/autoload.php'; // Atau path ke dompdf/autoload.inc.php
include 'config.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);

// 1. Ambil Data Tabel dari View
$sql_tabel = "SELECT * FROM view_laporan_pdf";
$res_tabel = mysqli_query($conn, $sql_tabel);

// 2. Ambil Data Rekapitulasi dari View
$sql_rekap = "SELECT * FROM view_rekap_pdf";
$res_rekap = mysqli_query($conn, $sql_rekap);

// 3. Susun HTML dengan Gaya Persis Dokumen Asli
$html = '
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; }
        .header { text-align: center; font-weight: bold; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid black; padding: 4px; text-align: left; }
        th { background-color: #f2f2f2; text-align: center; }
        .center { text-align: center; }
        .rekap-table { width: 40%; margin-top: 20px; border: none; }
        .rekap-table td { border: none; padding: 2px; }
        .footer-note { font-size: 8pt; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        LETTER AKRILIK KOPERASI MERAH PUTIH<br>
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

while ($row = mysqli_fetch_assoc($res_tabel)) {
    $html .= '<tr>
        <td class="center">' . $row['No'] . '</td>
        <td>' . $row['Nama Desa / Kelurahan'] . '</td>
        <td>' . $row['Kecamatan'] . '</td>
        <td class="center">' . $row['Produksi'] . '</td>
        <td class="center">' . $row['Terpasang'] . '</td>
        <td>' . $row['Ket'] . '</td>
    </tr>';
}

$html .= '</tbody></table>';

// Tambahkan Bagian Rekapitulasi di bawah tabel
$html .= '<table class="rekap-table">';
while ($rekap = mysqli_fetch_assoc($res_rekap)) {
    $html .= '<tr>
        <td>' . $rekap['Label'] . '</td>
        <td>: ' . $rekap['Jumlah'] . '</td>
    </tr>';
}
$html .= '</table>';

$html .= '<div class="footer-note">Update: ' . date('l, d F Y') . '</div>';
$html .= '</body></html>';

// Proses Render PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output File PDF
$dompdf->stream("Laporan_KMP_Jatim_" . date('Ymd') . ".pdf", array("Attachment" => 1));
?>