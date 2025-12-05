<?php
require "layout/head.php";
require "preferensi-fungsi.php";
require "include/conn.php";
require "include/nama-bulan.php";

// Pastikan session sudah berjalan (head.php biasanya sudah start session)
// Tentukan role pengguna (fallback 'guest' jika tidak ada)
$role = $_SESSION['role'] ?? 'guest';

/* ================================
   Ambil daftar periode (YYYY-MM)
   ================================ */
$periodList = getPeriodList($db);

// Jika user adalah mitra -> paksa melihat bulan berjalan saja 
$showFilter = true;
if ($role === 'mitra') {
  $showFilter = false;
  $nowYear = date("Y");
  $nowMonth = date("m");
  $period = "{$nowYear}-{$nowMonth}";
} else {
  // Non-mitra -> izinkan pilih period (GET) atau 'all'
  $period = isset($_GET['period']) ? trim($_GET['period']) : 'all';

  // Validasi format jika bukan 'all'
  if ($period !== 'all' && !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) {
    // fallback: gunakan first period dari db jika ada, else 'all'
    $period = $periodList[0] ?? 'all';
  }
}

/* ================================
   Jika period bukan 'all' -> ambil nama bulan & tahun untuk header
   ================================ */
if ($period !== 'all') {
  $yr = substr($period, 0, 4);
  $mn = substr($period, 5, 2);
  $mnName = $namaBulan[$mn] ?? $mn;
}
?>
<!DOCTYPE html>
<html lang="en">

<body>
  <div id="app">
    <?php require "layout/sidebar.php"; ?>
    <div id="main">

      <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
          <i class="bi bi-justify fs-3"></i>
        </a>
      </header>

      <div class="page-heading">
        <h3>Hasil Perangkingan</h3>
      </div>

      <div class="page-content">
        <section class="row">
          <div class="col-12">

            <!-- FILTER PERIODE (sembunyikan untuk mitra) -->
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
                      <?= htmlspecialchars($bulanNama . " " . $y) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </form>
            <?php else: ?>
              <div class="alert alert-info mb-4">
                Anda masuk sebagai <strong>Mitra</strong>. Menampilkan data untuk <strong><?= htmlspecialchars($namaBulan[$nowMonth] . " " . $nowYear) ?></strong>.
              </div>
            <?php endif; ?>

            <!-- TAMPILAN SINGLE PERIODE -->
            <?php if ($period !== 'all'): ?>
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
                        // ambil evaluasi untuk period ini
                        list($values, $alternatif) = getEvaluasi($db, $period);
                        list($krit, $bobot) = getKriteria($db);

                        if (empty($values)) {
                          echo "<tr><td colspan='3' class='text-center text-danger'>Belum ada data pada periode ini.</td></tr>";
                        } else {
                          // hitung normalisasi (fungsi di preferensi-fungsi.php)
                          // beberapa implementasi hitungNormalisasi menerima $db sebagai argumen.
                          // kami coba panggil dengan $db terlebih dahulu (sesuaikan bila fungsi anda tanpa $db).
                          if (function_exists('hitungNormalisasi')) {
                            // cek jumlah parameter fungsi hitungNormalisasi
                            $ref = new ReflectionFunction('hitungNormalisasi');
                            $params = $ref->getNumberOfParameters();
                            if ($params === 4) {
                              $R = hitungNormalisasi($db, $values, $krit, $bobot);
                            } else {
                              // anggap definisi: hitungNormalisasi($db, $values, $krit, $bobot)
                              $R = hitungNormalisasi($db, $values, $krit, $bobot);
                            }
                          } else {
                            // fallback: manual (very basic), jangan biarkan fatal
                            $R = [];
                            foreach ($values as $id_alt => $criteriaVals) {
                              foreach ($criteriaVals as $id_crit => $xij) {
                                $wj = ($bobot[$id_crit] ?? 0) / 100;
                                $R[$id_alt][$id_crit] = ($krit[$id_crit] === 'cost') ? (1 - ($xij / 5)) * $wj : ($xij / 5) * $wj;
                              }
                            }
                          }

                          $P = hitungNilaiAkhir($R);
                          $ranking = perangkingan($P, $alternatif);

                          foreach ($ranking as $row) {
                            echo "<tr>
                                    <td>" . htmlspecialchars($row['ranking']) . "</td>
                                    <td>" . htmlspecialchars($row['name']) . "</td>
                                    <td>" . htmlspecialchars($row['nilai']) . "</td>
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
              // period == all -> kelompokkan periodList berdasarkan tahun (descending)
              $grouped = [];
              foreach ($periodList as $p) {
                $y = substr($p, 0, 4);
                if (!isset($grouped[$y])) $grouped[$y] = [];
                $grouped[$y][] = $p;
              }
              krsort($grouped); // years desc
            ?>

              <?php foreach ($grouped as $year => $periodsOfYear): ?>
                <div class="card shadow-sm mb-4">
                  <div class="card-header">
                    <h5 class="mb-0">Tahun <?= htmlspecialchars($year) ?></h5>
                  </div>
                  <div class="card-body">

                    <?php
                    // Untuk tiap bulan di tahun ini (periodsOfYear urut seperti periodList)
                    foreach ($periodsOfYear as $p):
                      $m = substr($p, 5, 2);
                      $bulanNama = $namaBulan[$m] ?? $m;

                      // Ambil data evaluasi untuk period $p dan hitung ranking
                      list($valuesP, $alternatifP) = getEvaluasi($db, $p);
                      list($krit, $bobot) = getKriteria($db);

                      if (!empty($valuesP)) {
                        // panggil hitungNormalisasi sesuai definisi yang ada
                        $ref = new ReflectionFunction('hitungNormalisasi');
                        $params = $ref->getNumberOfParameters();
                        if ($params === 4) {
                          $RP = hitungNormalisasi($db, $valuesP, $krit, $bobot);
                        } else {
                          $RP = hitungNormalisasi($db, $valuesP, $krit, $bobot);
                        }

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