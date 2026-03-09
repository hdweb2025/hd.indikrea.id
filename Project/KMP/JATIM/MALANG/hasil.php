<?php
require_once __DIR__ . '/config.php';
$desa_id = isset($_GET['desa']) ? (int)$_GET['desa'] : 0;
$desa = null;
if ($conn && $desa_id > 0) {
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS hasil_desa (id INT AUTO_INCREMENT PRIMARY KEY, desa_id INT NOT NULL, filename VARCHAR(255) NOT NULL, mime VARCHAR(100) NOT NULL, uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX(desa_id))");
    $r = mysqli_query($conn, "SELECT * FROM data_desa WHERE id=".$desa_id." LIMIT 1");
    if ($r) { $desa = mysqli_fetch_assoc($r); }
}
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Hasil Pekerjaan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-5">
  <div class="bg-white p-4 rounded shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="mb-0">Hasil Pekerjaan</h3>
      <a class="btn btn-outline-secondary btn-sm" href="/Project/KMP/JATIM/MALANG/index.php">Kembali</a>
    </div>
    <?php if (!$conn || !$desa) { ?>
      <div class="alert alert-danger">Data tidak ditemukan.</div>
    <?php } else { 
      $nama = htmlspecialchars($desa['nama_desa'], ENT_QUOTES, 'UTF-8');
      $kec = htmlspecialchars($desa['kecamatan'], ENT_QUOTES, 'UTF-8');
      $qfiles = mysqli_query($conn, "SELECT * FROM hasil_desa WHERE desa_id=".$desa_id." ORDER BY uploaded_at DESC");
      $imgs = [];
      $pdfs = [];
      if ($qfiles) {
        while($f = mysqli_fetch_assoc($qfiles)) {
          if (strpos($f['mime'], 'image/') === 0) $imgs[] = $f; else if ($f['mime'] === 'application/pdf') $pdfs[] = $f;
        }
      }
    ?>
      <div class="mb-3">
        <div><strong>Desa:</strong> <?php echo $nama; ?></div>
        <div><strong>Kecamatan:</strong> <?php echo $kec; ?></div>
      </div>
      <h5 class="mt-4">Galeri</h5>
      <?php if (count($imgs) === 0) { ?>
        <div class="text-muted">Belum ada gambar.</div>
      <?php } else { ?>
        <div class="row g-3">
          <?php foreach($imgs as $g) {
            $src = '/assets/uploads/malang/'.$desa_id.'/'.rawurlencode($g['filename']);
          ?>
            <div class="col-6 col-md-3">
              <a href="<?php echo $src; ?>" target="_blank">
                <img src="<?php echo $src; ?>" class="img-fluid border rounded">
              </a>
            </div>
          <?php } ?>
        </div>
      <?php } ?>
      <h5 class="mt-4">Dokumen PDF</h5>
      <?php if (count($pdfs) === 0) { ?>
        <div class="text-muted">Belum ada dokumen.</div>
      <?php } else { ?>
        <ul class="list-group">
          <?php foreach($pdfs as $p) { ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span><?php echo htmlspecialchars($p['filename'], ENT_QUOTES, 'UTF-8'); ?></span>
              <a class="btn btn-sm btn-primary" href="/Project/KMP/JATIM/MALANG/download.php?id=<?php echo (int)$p['id']; ?>">Unduh</a>
            </li>
          <?php } ?>
        </ul>
      <?php } ?>
    <?php } ?>
  </div>
</div>
</body>
</html>
