<!DOCTYPE html>
<html lang="en">
<?php
require "layout/head.php";
require "preferensi-fungsi.php";
require "include/conn.php";

// ambil daftar period
$periodList = getPeriodList($db);

// tentukan period aktif
$period = isset($_GET['period']) ? $_GET['period'] : (count($periodList) ? $periodList[0] : null);

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

            <form method="GET" class="mb-3">
              <label>Pilih Periode:</label>
              <select name="period" class="form-select" onchange="this.form.submit()">
                <?php foreach ($periodList as $p): ?>
                  <option value="<?= $p ?>" <?= $p == $period ? 'selected' : '' ?>>
                    <?= $p ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </form>

            <div class="card">
              <div class="card-header">
                <h4 class="card-title">Ranking Periode <?= $period ?></h4>
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
                        echo "<tr><td colspan='3' class='text-danger text-center'>Belum ada data periode ini.</td></tr>";
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

          </div>

        </section>
      </div>

    </div>
  </div>

  <?php require "layout/js.php"; ?>
</body>

</html>