<!DOCTYPE html>
<html lang="en">
<?php
require "layout/head.php";
require "preferensi-fungsi.php";
require "include/conn.php";
require "include/nama-bulan.php"; // konversi bulan

// Ambil daftar periode (format YYYY-MM), terurut DESC (mis. 2025-12, 2025-11, ...)
$periodList = getPeriodList($db);

// Tentukan periode aktif (default = "all")
$period = isset($_GET['period']) ? $_GET['period'] : 'all';

// Jika user pilih spesifik period, validasi singkat
if ($period !== 'all') {
  if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) {
    // fallback bila format tidak valid
    $period = $periodList[0] ?? 'all';
  }
}
?>

<body>
  <div id="app">
    <?php require "layout/sidebar.php"; ?>
    <div id="main">

      <div class="page-heading">
        <h3>Hasil Perangkingan</h3>
      </div>

      <div class="page-content">
        <section class="row">
          <div class="col-12">

            <!-- FILTER PERIODE -->
            <form method="GET" class="mb-4">
              <label class="fw-semibold mb-1">Pilih Periode:</label>
              <select name="period" class="form-select" onchange="this.form.submit()">
                <option value="all" <?= $period === 'all' ? 'selected' : '' ?>>Semua Periode</option>
                <?php foreach ($periodList as $p):
                  $y = substr($p, 0, 4);
                  $m = substr($p, 5, 2);
                  $bulanNama = $namaBulan[$m] ?? $m;
                ?>
                  <option value="<?= htmlspecialchars($p) ?>" <?= $p === $period ? 'selected' : '' ?>>
                    <?= htmlspecialchars($bulanNama . " " . $y) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </form>

            <?php if ($period !== 'all'):
              // tampil single period (seperti sebelumnya)
              $yr = substr($period, 0, 4);
              $mn = substr($period, 5, 2);
              $mnName = $namaBulan[$mn] ?? $mn;
            ?>
              <div class="card shadow-sm mb-3">
                <div class="card-header bg-light">
                  <h4 class="card-title mb-0">Ranking Periode <?= htmlspecialchars($mnName . ' ' . $yr) ?></h4>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-striped mb-0">
                      <thead>
                        <tr>
                          <th>Peringkat</th>
                          <th>Alternatif</th>
                          <th>Nilai Akhir (P)</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        list($values, $alternatif) = getEvaluasi($db, $period);
                        list($krit, $bobot) = getKriteria($db);

                        if (empty($values)) {
                          echo "<tr><td colspan='3' class='text-center text-danger'>Belum ada data pada periode ini.</td></tr>";
                        } else {
                          $R = hitungNormalisasi($values, $krit, $bobot);
                          $P = hitungNilaiAkhir($R);
                          $ranking = perangkingan($P, $alternatif);

                          foreach ($ranking as $row) {
                            echo "<tr>
                                    <td>{$row['ranking']}</td>
                                    <td>{$row['name']}</td>
                                    <td>{$row['nilai']}</td>
                                  </tr>";
                          }
                        }
                        ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

            <?php else:
              // period == all -> kelompokkan periodList berdasarkan tahun
              $grouped = [];
              foreach ($periodList as $p) {
                $y = substr($p, 0, 4);
                if (!isset($grouped[$y])) $grouped[$y] = [];
                $grouped[$y][] = $p;
              }

              // Tampilkan per tahun (desc). periodList already desc, but ensure years in desc order:
              krsort($grouped); // years desc
            ?>

              <?php foreach ($grouped as $year => $periodsOfYear): ?>
                <div class="card shadow-sm mb-4">
                  <div class="card-header">
                    <h5 class="mb-0">Tahun <?= htmlspecialchars($year) ?></h5>
                  </div>
                  <div class="card-body">

                    <?php
                    // Untuk tiap bulan di tahun ini (periodsOfYear is in desc order because original list desc)
                    foreach ($periodsOfYear as $p):
                      $m = substr($p, 5, 2);
                      $bulanNama = $namaBulan[$m] ?? $m;

                      // Ambil data evaluasi untuk period $p
                      list($valuesP, $alternatifP) = getEvaluasi($db, $p);
                      list($krit, $bobot) = getKriteria($db);

                      // Jika ada data, hitung ranking untuk period ini
                      if (!empty($valuesP)) {
                        $RP = hitungNormalisasi($valuesP, $krit, $bobot);
                        $PP = hitungNilaiAkhir($RP);
                        $rankingP = perangkingan($PP, $alternatifP);
                      } else {
                        $rankingP = [];
                      }
                    ?>
                      <div class="mb-3">
                        <h6 class="mb-2"><?= htmlspecialchars($bulanNama) ?></h6>

                        <?php if (empty($rankingP)): ?>
                          <div class="alert alert-warning small mb-3">Belum ada data evaluasi untuk periode <?= htmlspecialchars($p) ?>.</div>
                        <?php else: ?>
                          <div class="table-responsive mb-3">
                            <table class="table table-sm table-bordered">
                              <thead class="table-light">
                                <tr>
                                  <th style="width:10%;">Peringkat</th>
                                  <th>Alternatif</th>
                                  <th>Nilai Akhir (P)</th>
                                </tr>
                              </thead>
                              <tbody>
                                <?php foreach ($rankingP as $r): ?>
                                  <tr>
                                    <td><?= htmlspecialchars($r['ranking']) ?></td>
                                    <td><?= htmlspecialchars($r['name']) ?></td>
                                    <td><?= htmlspecialchars($r['nilai']) ?></td>
                                  </tr>
                                <?php endforeach; ?>
                              </tbody>
                            </table>
                          </div>
                        <?php endif; ?>
                      </div>
                    <?php endforeach; ?>

                  </div>
                </div>
              <?php endforeach; ?>

            <?php endif; ?>

          </div>
        </section>
      </div>

    </div>
  </div>

  <?php require "layout/js.php"; ?>
</body>

</html>