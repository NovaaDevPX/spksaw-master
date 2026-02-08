<?php
require "vendor/autoload.php";
require "preferensi-fungsi.php";
require "include/conn.php";
require "include/nama-bulan.php";

use Dompdf\Dompdf;
use Dompdf\Options;

// Ambil periode dari URL
$period = isset($_GET['period']) ? $_GET['period'] : 'all';

// Validasi format periode
if ($period !== 'all' && !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) {
  $period = 'all';
}

// Ambil data
$periodList = getPeriodList($db);

// Tentukan judul periode
$title = "Semua Periode";
if ($period !== 'all') {
  $yr = substr($period, 0, 4);
  $mn = substr($period, 5, 2);
  $mnName = $namaBulan[$mn] ?? $mn;
  $title = $mnName . " " . $yr;
}

// =============================
// PROSES PERHITUNGAN
// =============================
ob_start();
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Laporan Preferensi</title>
  <style>
    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 12px;
    }

    h2,
    h3 {
      text-align: center;
      margin: 0;
    }

    .info {
      text-align: center;
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    th,
    td {
      border: 1px solid #000;
      padding: 6px;
      text-align: center;
    }

    th {
      background: #f2f2f2;
    }

    .left {
      text-align: left;
    }
  </style>
</head>

<body>

  <h2>LAPORAN HASIL PERANGKINGAN</h2>
  <div class="info">
    Periode: <strong><?= htmlspecialchars($title) ?></strong>
  </div>

  <?php
  // =============================
  // SINGLE PERIODE
  // =============================
  if ($period !== 'all') {

    list($values, $alternatif) = getEvaluasi($db, $period);
    list($krit, $bobot) = getKriteria($db);

    if (!empty($values)) {
      $R = hitungNormalisasi($db, $values, $krit, $bobot);
      $P = hitungNilaiAkhir($R);
      $ranking = perangkingan($P, $alternatif);
    } else {
      $ranking = [];
    }
  ?>

    <table>
      <thead>
        <tr>
          <th style="width:10%">Peringkat</th>
          <th class="left">Alternatif</th>
          <th style="width:20%">Nilai Akhir (P)</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($ranking)): ?>
          <tr>
            <td colspan="3">Belum ada data.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($ranking as $row): ?>
            <tr>
              <td><?= $row['ranking'] ?></td>
              <td class="left"><?= htmlspecialchars($row['name']) ?></td>
              <td><?= $row['nilai'] ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

    <?php
    // =============================
    // SEMUA PERIODE
    // =============================
  } else {

    $grouped = [];
    foreach ($periodList as $p) {
      $y = substr($p, 0, 4);
      if (!isset($grouped[$y])) $grouped[$y] = [];
      $grouped[$y][] = $p;
    }
    krsort($grouped);

    foreach ($grouped as $year => $periodsOfYear):
    ?>

      <h3 style="margin-top:20px;">Tahun <?= $year ?></h3>

      <?php
      foreach ($periodsOfYear as $p):

        $m = substr($p, 5, 2);
        $bulanNama = $namaBulan[$m] ?? $m;

        list($valuesP, $alternatifP) = getEvaluasi($db, $p);
        list($krit, $bobot) = getKriteria($db);

        if (!empty($valuesP)) {
          $RP = hitungNormalisasi($db, $valuesP, $krit, $bobot);
          $PP = hitungNilaiAkhir($RP);
          $rankingP = perangkingan($PP, $alternatifP);
        } else {
          $rankingP = [];
        }
      ?>

        <h4><?= $bulanNama ?></h4>
        <table>
          <thead>
            <tr>
              <th style="width:10%">Peringkat</th>
              <th class="left">Alternatif</th>
              <th style="width:20%">Nilai Akhir (P)</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($rankingP)): ?>
              <tr>
                <td colspan="3">Belum ada data.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($rankingP as $r): ?>
                <tr>
                  <td><?= $r['ranking'] ?></td>
                  <td class="left"><?= htmlspecialchars($r['name']) ?></td>
                  <td><?= $r['nilai'] ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>

  <?php
      endforeach;
    endforeach;
  }
  ?>

</body>

</html>

<?php
$html = ob_get_clean();

// =============================
// GENERATE PDF
// =============================
$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Nama file
$filename = "Laporan-Preferensi-" . str_replace(" ", "-", $title) . ".pdf";

// Output
$dompdf->stream($filename, ["Attachment" => true]);
exit;
