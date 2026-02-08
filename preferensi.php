<?php
require "layout/head.php";
require "preferensi-fungsi.php";
require "include/conn.php";
require "include/nama-bulan.php";

$role = $_SESSION['role'] ?? 'guest';
$periodList = getPeriodList($db);

$showFilter = true;
if ($role === 'mitra') {
  $showFilter = false;
  $nowYear = date("Y");
  $nowMonth = date("m");
  $period = "{$nowYear}-{$nowMonth}";
} else {
  $period = isset($_GET['period']) ? trim($_GET['period']) : 'all';
  if ($period !== 'all' && !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) {
    $period = $periodList[0] ?? 'all';
  }
}

if ($period !== 'all') {
  $yr = substr($period, 0, 4);
  $mn = substr($period, 5, 2);
  $mnName = $namaBulan[$mn] ?? $mn;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <style>
    .export-btn {
      border-radius: 30px;
      padding: 8px 16px;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .export-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
    }
  </style>
</head>

<body>
  <div id="app">
    <?php require "layout/sidebar.php"; ?>
    <div id="main">
      <div class="page-heading">
        <h3>Hasil Perangkingan</h3>
      </div>
      <div class="page-content">
        <?php if ($role === 'mitra'): ?>
          <div class="alert alert-info mb-4">
            Anda masuk sebagai <strong>Mitra</strong>. Menampilkan data untuk <strong><?= htmlspecialchars($namaBulan[$nowMonth] . " " . $nowYear) ?></strong>.
          <?php endif; ?>
          </div>
          <section class="row">
            <div class="col-12">
              <!-- FILTER -->
              <?php if ($showFilter): ?>
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
                        <?= htmlspecialchars($bulanNama . ' ' . $y) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </form>
              <?php endif; ?>

              <!-- SINGLE PERIODE -->
              <?php if ($period !== 'all'):
                list($values, $alternatif) = getEvaluasi($db, $period);
                list($krit, $bobot) = getKriteria($db);

                if (!empty($values)):
                  $R = hitungNormalisasi($db, $values, $krit, $bobot);
                  $P = hitungNilaiAkhir($R);
                  $ranking = perangkingan($P, $alternatif);
                  $narasi = buatNarasiRankingBreak($ranking, $mnName, $yr);
              ?>

                  <div class="card p-4 shadow-sm">

                    <!-- NARASI RANKING 1 -->
                    <div class="alert alert-primary">
                      <?= nl2br(htmlspecialchars($narasi['narasi_1'])) ?>
                    </div>

                    <!-- TABEL RANKING 1 -->
                    <div class="">
                      <div class="table-responsive">
                        <table class="table table-striped">
                          <thead>
                            <tr>
                              <th>Peringkat</th>
                              <th>Alternatif</th>
                              <th>Nilai Akhir</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td><?= $narasi['rank1']['ranking'] ?></td>
                              <td><?= htmlspecialchars($narasi['rank1']['name']) ?></td>
                              <td><?= $narasi['rank1']['nilai'] ?></td>
                            </tr>
                          </tbody>
                        </table>

                        <p>
                          Dengan Nilai Per Kriteria sebagai berikut:
                        </p>

                        <table class="table table-bordered">
                          <thead class="table-light">
                            <tr>
                              <th>Kriteria</th>
                              <th>Nilai</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                            $rank1Id = $narasi['rank1']['id'];
                            $rank1Data = getNilaiPerKriteria($values, $rank1Id, $krit);
                            foreach ($rank1Data as $k => $v): ?>
                              <tr>
                                <td><?= htmlspecialchars($k) ?></td>
                                <td><?= $v ?></td>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>

                    <hr class="my-4">

                    <!-- NARASI RANKING SELANJUTNYA -->
                    <?php if (!empty($narasi['rank_lanjutan'])): ?>
                      <div class="alert alert-info">
                        <p>Peringkat selanjutnya</p>
                      </div>

                      <div class="table-responsive">
                        <table class="table table-striped">
                          <thead>
                            <tr>
                              <th>Peringkat</th>
                              <th>Alternatif</th>
                              <th>Nilai Akhir</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($narasi['rank_lanjutan'] as $r): ?>
                              <tr>
                                <td><?= $r['ranking'] ?></td>
                                <td><?= htmlspecialchars($r['name']) ?></td>
                                <td><?= $r['nilai'] ?></td>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    <?php endif; ?>

                  <?php else: ?>
                    <div class="alert alert-danger text-center">Belum ada data pada periode ini.</div>
                  <?php endif; ?>
                  </div>

                <?php else:
                // MODE ALL PERIODE
                $grouped = [];
                foreach ($periodList as $p) {
                  $y = substr($p, 0, 4);
                  $grouped[$y][] = $p;
                }
                krsort($grouped);
                ?>

                  <?php foreach ($grouped as $year => $periods): ?>
                    <div class="card shadow-sm mb-4">
                      <div class="card-header">
                        <h5 class="mb-0">Tahun <?= htmlspecialchars($year) ?></h5>
                      </div>
                      <div class="card-body">
                        <?php foreach ($periods as $p):
                          $m = substr($p, 5, 2);
                          $bulanNama = $namaBulan[$m] ?? $m;

                          list($valuesP, $alternatifP) = getEvaluasi($db, $p);
                          list($krit, $bobot) = getKriteria($db);

                          if (!empty($valuesP)):
                            $RP = hitungNormalisasi($db, $valuesP, $krit, $bobot);
                            $PP = hitungNilaiAkhir($RP);
                            $rankingP = perangkingan($PP, $alternatifP);
                            $narasiP = buatNarasiRankingBreak($rankingP, $bulanNama, $year);
                          else:
                            $rankingP = [];
                          endif;
                        ?>
                          <div class="mb-4">
                            <h6><?= htmlspecialchars($bulanNama) ?></h6>
                            <?php if (!empty($rankingP)): ?>
                              <div class="alert alert-primary small">
                                <?= nl2br(htmlspecialchars($narasiP['narasi_1'])) ?>
                              </div>

                              <div class="table-responsive mb-2">
                                <table class="table table-striped">
                                  <thead>
                                    <tr>
                                      <th>Peringkat</th>
                                      <th>Alternatif</th>
                                      <th>Nilai Akhir</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <tr>
                                      <td><?= $narasiP['rank1']['ranking'] ?></td>
                                      <td><?= htmlspecialchars($narasiP['rank1']['name']) ?></td>
                                      <td><?= $narasiP['rank1']['nilai'] ?></td>
                                    </tr>
                                  </tbody>
                                </table>

                                <p>
                                  Dengan Nilai Per Kriteria sebagai berikut:
                                </p>

                                <table class="table table-sm table-bordered">
                                  <thead class="table-light">
                                    <tr>
                                      <th>Kriteria</th>
                                      <th>Nilai</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <?php
                                    $rank1Id = $narasiP['rank1']['id'];
                                    $rank1Data = getNilaiPerKriteria($valuesP, $rank1Id, $krit);
                                    foreach ($rank1Data as $k => $v): ?>
                                      <tr>
                                        <td><?= htmlspecialchars($k) ?></td>
                                        <td><?= $v ?></td>
                                      </tr>
                                    <?php endforeach; ?>
                                  </tbody>
                                </table>
                              </div>

                              <?php if (!empty($narasiP['rank_lanjutan'])): ?>
                                <div class="alert alert-info small">
                                  <p>Peringkat Selanjutnya</p>
                                </div>

                                <div class="table-responsive mb-2">
                                  <table class="table table-sm table-striped">
                                    <thead>
                                      <tr>
                                        <th>Peringkat</th>
                                        <th>Alternatif</th>
                                        <th>Nilai Akhir</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <?php foreach ($narasiP['rank_lanjutan'] as $r): ?>
                                        <tr>
                                          <td><?= $r['ranking'] ?></td>
                                          <td><?= htmlspecialchars($r['name']) ?></td>
                                          <td><?= $r['nilai'] ?></td>
                                        </tr>
                                      <?php endforeach; ?>
                                    </tbody>
                                  </table>
                                </div>
                              <?php endif; ?>

                            <?php else: ?>
                              <div class="alert alert-warning small">Belum ada data evaluasi.</div>
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