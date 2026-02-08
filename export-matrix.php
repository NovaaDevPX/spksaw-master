<?php
require "vendor/autoload.php";
require "include/conn.php";
require "include/nama-bulan.php";

use Dompdf\Dompdf;
use Dompdf\Options;

/* ================================
   AMBIL PERIOD
================================ */

$period = isset($_GET["period"]) ? $_GET["period"] : null;

if (!$period || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) {
  die("Periode tidak valid.");
}

$periodEsc = $db->real_escape_string($period);

/* ================================
   JUDUL PERIODE
================================ */
$yr = substr($period, 0, 4);
$mn = substr($period, 5, 2);
$mnName = $namaBulan[$mn] ?? $mn;
$title = $mnName . " " . $yr;

/* ================================
   AMBIL KRITERIA
================================ */
$krit = [];
$bobot = [];

$q = $db->query("SELECT id_criteria, attribute, weight FROM saw_criterias ORDER BY id_criteria");
while ($r = $q->fetch_object()) {
  $krit[$r->id_criteria] = $r->attribute;
  $bobot[$r->id_criteria] = $r->weight;
}
$jmlKrit = count($krit);

/* ================================
   AMBIL MATRIX X
================================ */
$sql = "
SELECT 
    b.id_alternative, b.name,
    GROUP_CONCAT(a.id_criteria ORDER BY a.id_criteria) AS ids,
    GROUP_CONCAT(a.value ORDER BY a.id_criteria) AS vals
FROM saw_alternatives b
LEFT JOIN saw_evaluations a 
  ON a.id_alternative = b.id_alternative
 AND a.period = '{$periodEsc}'
GROUP BY b.id_alternative
ORDER BY b.id_alternative
";

$result = $db->query($sql);

$alternatifNama = [];
$idMapping = [];
$X = [];
$no = 1;

while ($row = $result->fetch_object()) {

  $alternatifNama[$row->id_alternative] = $row->name;
  $idMapping[$row->id_alternative] = $no;

  $vals = array_fill(1, $jmlKrit, NULL);

  if ($row->ids) {
    $idArr  = explode(",", $row->ids);
    $valArr = explode(",", $row->vals);

    foreach ($idArr as $idx => $idC) {
      $vals[$idC] = $valArr[$idx];
    }
  }

  $X[$row->id_alternative] = $vals;
  $no++;
}

/* ================================
   HITUNG NORMALISASI R
================================ */
$R = [];

foreach ($X as $idAlt => $vals) {

  $rRow = [];

  foreach ($vals as $idC => $xij) {

    if ($xij === NULL) {
      $rRow[$idC] = "-";
    } else {

      $wj = $bobot[$idC] / 100;
      $maxScale = 5;

      if ($krit[$idC] === "cost") {
        $r = (($maxScale - $xij + 1) / $maxScale) * $wj;
      } else {
        $r = ($xij / $maxScale) * $wj;
      }

      $rRow[$idC] = number_format($r, 3);
    }
  }

  $R[$idAlt] = $rRow;
}

/* ================================
   HITUNG NILAI AKHIR P
================================ */
$nilaiP = [];

foreach ($R as $idAlt => $vals) {
  $t = 0;
  foreach ($vals as $v) {
    if ($v !== "-") $t += (float)$v;
  }
  $nilaiP[$idAlt] = $t;
}

/* ================================
   GENERATE HTML
================================ */
ob_start();
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <style>
    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 11px;
    }

    h2,
    h3 {
      text-align: center;
      margin: 0;
    }

    .info {
      text-align: center;
      margin-bottom: 15px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    th,
    td {
      border: 1px solid #000;
      padding: 5px;
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

  <h2>LAPORAN MATRIKS SAW</h2>
  <div class="info">
    Periode: <strong><?= htmlspecialchars($title) ?></strong>
  </div>

  <!-- ==================== -->
  <!-- MATRIKS KEPUTUSAN X -->
  <!-- ==================== -->
  <h3>Matriks Keputusan (X)</h3>
  <table>
    <tr>
      <th rowspan="2">Alternatif</th>
      <th colspan="<?= $jmlKrit ?>">Kriteria</th>
    </tr>
    <tr>
      <?php foreach ($krit as $idC => $v): ?>
        <th>C<?= $idC ?></th>
      <?php endforeach; ?>
    </tr>

    <?php foreach ($X as $idAlt => $vals): ?>
      <tr>
        <th class="left">
          A<?= $idMapping[$idAlt] ?> <?= htmlspecialchars($alternatifNama[$idAlt]) ?>
        </th>
        <?php foreach ($vals as $v): ?>
          <td><?= $v === NULL ? "-" : round($v, 2) ?></td>
        <?php endforeach; ?>
      </tr>
    <?php endforeach; ?>
  </table>

  <!-- ==================== -->
  <!-- MATRIKS NORMALISASI -->
  <!-- ==================== -->
  <h3>Matriks Ternormalisasi (R)</h3>
  <table>
    <tr>
      <th rowspan="2">Alternatif</th>
      <th colspan="<?= $jmlKrit ?>">Kriteria</th>
    </tr>
    <tr>
      <?php foreach ($krit as $idC => $v): ?>
        <th>C<?= $idC ?></th>
      <?php endforeach; ?>
    </tr>

    <?php foreach ($R as $idAlt => $vals): ?>
      <tr>
        <th class="left">
          A<?= $idMapping[$idAlt] ?> <?= htmlspecialchars($alternatifNama[$idAlt]) ?>
        </th>
        <?php foreach ($vals as $v): ?>
          <td><?= $v ?></td>
        <?php endforeach; ?>
      </tr>
    <?php endforeach; ?>
  </table>

  <!-- ==================== -->
  <!-- NILAI AKHIR -->
  <!-- ==================== -->
  <h3>Nilai Akhir (P)</h3>
  <table>
    <tr>
      <th>Alternatif</th>
      <th>Nilai Akhir (P)</th>
    </tr>

    <?php foreach ($nilaiP as $idAlt => $val): ?>
      <tr>
        <th class="left">
          A<?= $idMapping[$idAlt] ?> <?= htmlspecialchars($alternatifNama[$idAlt]) ?>
        </th>
        <td><?= number_format($val, 3) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

</body>

</html>

<?php
$html = ob_get_clean();

/* ================================
   GENERATE PDF
================================ */
$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = "Laporan-Matriks-" . str_replace(" ", "-", $title) . ".pdf";
$dompdf->stream($filename, ["Attachment" => true]);
exit;
