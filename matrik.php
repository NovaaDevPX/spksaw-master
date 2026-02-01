<!DOCTYPE html>
<html lang="en">
<?php
require "layout/head.php";
require "include/conn.php";
require "include/nama-bulan.php";
?>

<body>
  <div id="app">
    <?php require "layout/sidebar.php"; ?>
    <div id="main">

      <?php
      // tampilkan pesan jika ada ?msg=... pada URL
      if (isset($_GET['msg'])) {
        $msg = htmlspecialchars($_GET['msg']);
        $type = isset($_GET['type']) ? htmlspecialchars($_GET['type']) : 'info';

        $class = 'alert-info';
        if ($type === 'success') $class = 'alert-success';
        if ($type === 'error') $class = 'alert-danger';
        if ($type === 'warning') $class = 'alert-warning';

        echo "
        <div class='alert {$class} alert-dismissible fade show' role='alert' style='margin:10px 0;'>
          {$msg}
          <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
      }
      ?>

      <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
          <i class="bi bi-justify fs-3"></i>
        </a>
      </header>

      <div class="page-heading">
        <h3>Matriks</h3>
      </div>

      <div class="page-content">
        <section class="row">
          <div class="col-12">
            <div class="card">

              <div class="card-header">
                <h4 class="card-title">Matriks Keputusan (X) &amp; Ternormalisasi (R)</h4>
              </div>

              <div class="card-content">
                <div class="card shadow-sm border-0">
                  <div class="card-body">
                    <h5 class="card-title mb-3">
                      <i class="bi bi-calculator"></i> Normalisasi Metode SAW
                    </h5>

                    <p class="card-text">
                      Melakukan perhitungan normalisasi untuk mendapatkan
                      <b>matriks nilai ternormalisasi (R)</b>, dengan ketentuan:
                    </p>

                    <p class="card-text"> Melakukan perhitungan normalisasi untuk mendapatkan matriks nilai ternormalisasi (R), dengan ketentuan:<br> Jika atribut <b>benefit</b> maka digunakan rumus: Rij = ( Xij / max{Xij} )<br> Jika atribut <b>cost</b> maka digunakan rumus: Rij = ( min{Xij} / Xij ) </p>
                  </div>
                </div>


                <button type="button" class="btn btn-outline-success btn-sm m-2" data-bs-toggle="modal" data-bs-target="#inlineForm">
                  Isi Nilai Alternatif
                </button>
                <?php

                /* ================================
   AMBIL DAFTAR TAHUN (UNIK)
   ================================ */
                $yearList = [];
                $q = $db->query("SELECT DISTINCT LEFT(period,4) AS y FROM saw_evaluations ORDER BY y DESC");
                if ($q) {
                  while ($r = $q->fetch_object()) {
                    $yearList[] = $r->y;
                  }
                  $q->free();
                }

                /* ================================
   TENTUKAN YEAR (PRIORITAS)
   ================================ */
                $year = null;
                $explicitYear = false;

                // 1 — user memilih year
                if (isset($_GET["year"]) && trim($_GET["year"]) !== "") {
                  $year = trim($_GET["year"]);
                  $explicitYear = true;

                  // 2 — user hanya memilih period, ambil year dari period
                } elseif (isset($_GET["period"]) && trim($_GET["period"]) !== "") {
                  $pTmp = trim($_GET["period"]);
                  if (preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', $pTmp, $m)) {
                    $year = $m[1];
                  }
                }

                // 3 — default: ambil tahun terbaru dari DB
                if ($year === null) {
                  $year = $yearList[0] ?? null;
                }

                /* ================================
   AMBIL LIST PERIOD UNTUK TAHUN TERSEBUT
   ================================ */
                $periodList = [];
                if ($year !== null) {
                  $yearEsc = $db->real_escape_string($year);
                  $q = $db->query("
        SELECT DISTINCT period
        FROM saw_evaluations
        WHERE LEFT(period,4) = '{$yearEsc}'
        ORDER BY period DESC
    ");
                } else {
                  $q = $db->query("SELECT DISTINCT period FROM saw_evaluations ORDER BY period DESC");
                }

                if ($q) {
                  while ($r = $q->fetch_object()) {
                    $periodList[] = $r->period;
                  }
                  $q->free();
                }

                /* ================================
   TENTUKAN PERIOD
   ================================ */
                $period = null;

                if (isset($_GET["period"]) && trim($_GET["period"]) !== "") {
                  $periodCand = trim($_GET["period"]);

                  // valid format
                  if (preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', $periodCand, $m)) {

                    // user memilih year → hanya terima period yang cocok
                    if ($explicitYear) {
                      if ($m[1] === $year) {
                        $period = $periodCand;
                      } else {
                        $period = null; // period tidak cocok → abaikan
                      }
                    } else {
                      // user hanya pilih period → gunakan langsung
                      $period = $periodCand;

                      // pastikan period muncul di dropdown
                      if (!in_array($period, $periodList)) {
                        array_unshift($periodList, $period);
                      }

                      // year ikut berubah
                      $year = substr($period, 0, 4);

                      if (!in_array($year, $yearList)) {
                        array_unshift($yearList, $year);
                      }
                    }
                  }
                }

                // jika belum ditentukan, gunakan period terbaru pada tahun tersebut
                if ($period === null) {
                  $period = $periodList[0] ?? null;
                }

                /* ESCAPING FINAL */
                $periodEsc = $period !== null ? $db->real_escape_string($period) : null;
                $yearEsc = $year !== null ? $db->real_escape_string($year) : null;

                ?>

                <div class="table-responsive">

                  <!-- ======================== -->
                  <!-- FILTER TAHUN & PERIODE -->
                  <!-- ======================== -->

                  <div class="card shadow-sm mb-4">
                    <div class="card-body">

                      <form method="GET" class="row g-3">

                        <!-- Pilih Tahun -->
                        <div class="col-md-4">
                          <label class="form-label fw-semibold">Pilih Tahun</label>
                          <select name="year" class="form-select" onchange="this.form.submit()">
                            <?php foreach ($yearList as $y): ?>
                              <option value="<?= htmlspecialchars($y) ?>" <?= $y == $year ? "selected" : "" ?>>
                                <?= htmlspecialchars($y) ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </div>

                        <!-- Pilih Periode -->
                        <div class="col-md-4">
                          <label class="form-label fw-semibold">Pilih Periode (Bulan)</label>
                          <select name="period" class="form-select" onchange="this.form.submit()">
                            <?php foreach ($periodList as $p): ?>
                              <?php
                              $bulanOnly = substr($p, 5, 2);       // Ambil angka bulan dari YYYY-MM
                              $bulanNama = $namaBulan[$bulanOnly]; // Konversi ke nama bulan
                              ?>
                              <option value="<?= htmlspecialchars($p) ?>"
                                <?= $p == $period ? "selected" : "" ?>>
                                <?= htmlspecialchars($bulanNama) ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </form>
                    </div>

                    <div class="card-footer bg-light">
                      <strong>Filter Aktif:</strong>

                      <span class="text-primary">
                        <?= htmlspecialchars($year) ?>
                      </span>
                      —
                      <?php
                      // Jika period hanya "01", gunakan langsung
                      // Jika period format YYYY-MM, ambil bagian bulannya
                      if (strlen($period) === 2) {
                        $bulanAktif = $period; // "01"
                      } else {
                        $bulanAktif = substr($period, 5, 2); // dari "2025-01"
                      }

                      $bulanNamaAktif = $namaBulan[$bulanAktif] ?? $period;
                      ?>

                      <span class="text-primary">
                        <?= htmlspecialchars($bulanNamaAktif) ?>
                      </span>
                    </div>

                    <hr>

                    <?php
                    // Ambil kriteria
                    $krit = [];
                    $bobot = [];

                    $q = $db->query("SELECT id_criteria, attribute, weight FROM saw_criterias ORDER BY id_criteria");
                    while ($r = $q->fetch_object()) {
                      $krit[$r->id_criteria] = $r->attribute;
                      $bobot[$r->id_criteria] = $r->weight;
                    }

                    $jmlKrit = count($krit); // jumlah kriteria
                    ?>

                    <!-- ============================== -->
                    <!--       MATRIX KEPUTUSAN (X)     -->
                    <!-- ============================== -->

                    <table class="table table-striped mb-0">
                      <caption>Matrik Keputusan (X)</caption>

                      <tr>
                        <th rowspan="2">Alternatif</th>
                        <th colspan="<?= $jmlKrit ?>">Kriteria</th>
                        <th>Aksi</th>
                      </tr>
                      <tr>
                        <?php foreach ($krit as $idC => $v): ?>
                          <th>C<?= $idC ?></th>
                        <?php endforeach; ?>
                        <th></th>
                      </tr>

                      <?php
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

                        // Isi nilai Xij berdasarkan jumlah kriteria
                        $vals = array_fill(1, $jmlKrit, NULL);

                        if ($row->ids) {
                          $idArr  = explode(",", $row->ids);
                          $valArr = explode(",", $row->vals);

                          foreach ($idArr as $idx => $idC) {
                            $vals[$idC] = $valArr[$idx];
                          }
                        }

                        // Simpan ke matriks X
                        $X[$row->id_alternative] = $vals;

                        echo "<tr class='center'>";
                        echo "<th>A{$no} {$row->name}</th>";

                        foreach ($vals as $v) {
                          echo "<td>" . ($v === NULL ? "-" : round($v, 2)) . "</td>";
                        }

                        $delUrl = "keputusan-hapus.php?id={$row->id_alternative}&period=" . urlencode($period);

                        echo "
        <td>
            <a href='$delUrl' class='btn btn-danger btn-sm' 
                onclick=\"return confirm('Hapus semua evaluasi untuk {$row->name}?');\">
                Hapus
            </a>
        </td>
    ";

                        echo "</tr>";
                        $no++;
                      }
                      ?>
                    </table>


                    <!-- ============================== -->
                    <!--        MATRIX NORMALISASI      -->
                    <!-- ============================== -->

                    <table class="table table-striped mb-0 mt-4">
                      <caption>Matrik Ternormalisasi (R)</caption>

                      <tr>
                        <th rowspan="2">Alternatif</th>
                        <th colspan="<?= $jmlKrit ?>">Kriteria</th>
                      </tr>
                      <tr>
                        <?php foreach ($krit as $idC => $v): ?>
                          <th>C<?= $idC ?></th>
                        <?php endforeach; ?>
                      </tr>

                      <?php
                      $R = [];

                      foreach ($X as $idAlt => $vals) {
                        echo "<tr class='center'>";
                        echo "<th>A{$idMapping[$idAlt]} {$alternatifNama[$idAlt]}</th>";

                        $rRow = [];

                        foreach ($vals as $idC => $xij) {

                          if ($xij === NULL) {
                            $rRow[$idC] = "-";
                          } else {

                            // Bobot (persen → desimal)
                            $wj = $bobot[$idC] / 100;

                            // Fixed scale 1 - 5
                            $minScale = 1;
                            $maxScale = 5;

                            if ($krit[$idC] === "cost") {
                              $r = (($maxScale - $xij + 1) / $maxScale) * $wj;
                            } else {
                              $r = ($xij / $maxScale) * $wj;
                            }

                            $rRow[$idC] = number_format($r, 3);
                          }

                          echo "<td>{$rRow[$idC]}</td>";
                        }

                        $R[$idAlt] = $rRow;

                        echo "</tr>";
                      }
                      ?>
                    </table>



                    <!-- ============================== -->
                    <!--           NILAI AKHIR          -->
                    <!-- ============================== -->

                    <table class="table table-striped mb-0 mt-4">
                      <caption>Nilai Akhir (P)</caption>

                      <tr>
                        <th>Alternatif</th>
                        <th>Nilai Akhir (P)</th>
                      </tr>

                      <?php
                      $nilaiP = [];

                      foreach ($R as $idAlt => $vals) {
                        $t = 0;
                        foreach ($vals as $v) {
                          if ($v !== "-") $t += (float)$v;
                        }
                        $nilaiP[$idAlt] = $t;
                      }

                      foreach ($nilaiP as $idAlt => $val) {
                        echo "<tr>
        <th>A{$idMapping[$idAlt]} {$alternatifNama[$idAlt]}</th>
        <td>" . number_format($val, 3) . "</td>
    </tr>";
                      }
                      ?>
                    </table>


                  </div>
                </div>
              </div>
            </div>
        </section>
      </div>

      <?php require "layout/footer.php"; ?>
    </div>
  </div>

  <!-- ========================== -->
  <!-- MODAL INPUT NILAI -->
  <!-- ========================== -->
  <div class="modal fade text-left" id="inlineForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="myModalLabel33">Isi Nilai Kandidat</h4>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <i data-feather="x"></i>
          </button>
        </div>

        <form action="matrik-simpan.php" method="POST">

          <!-- PERIOD RESULT -->
          <input type="hidden" name="period" id="periodInput">

          <div class="modal-body">

            <!-- KETERANGAN -->
            <div class="alert alert-info mt-3 d-flex align-items-center">
              <div>
                <strong>Keterangan Nilai:</strong><br>
                1 = Sangat Buruk |
                2 = Buruk |
                3 = Cukup |
                4 = Baik |
                5 = Sangat Baik
              </div>
            </div>

            <!-- Pilih Tahun -->
            <label>Pilih Tahun:</label>
            <div class="form-group">
              <input
                type="number"
                class="form-control"
                id="tahunInput"
                placeholder="Contoh: 2024"
                value="<?php echo date('Y'); ?>"
                min="2000"
                required>
            </div>

            <!-- Pilih Bulan -->
            <label>Pilih Bulan:</label>
            <div class="form-group">
              <select class="form-control form-select" id="bulanSelect" required></select>
            </div>

            <hr>

            <label>Nama Alternatif:</label>
            <div class="form-group">
              <select class="form-control form-select" name="id_alternative" required>
                <?php
                $sql = 'SELECT id_alternative,name FROM saw_alternatives';
                $result = $db->query($sql);
                while ($row = $result->fetch_object()) {
                  echo '<option value="' . $row->id_alternative . '">' . $row->name . '</option>';
                }
                $result->free();
                ?>
              </select>
            </div>

            <label>Kriteria:</label>
            <div class="form-group">
              <select class="form-control form-select" name="id_criteria" required>
                <?php
                $sql = 'SELECT * FROM saw_criterias';
                $result = $db->query($sql);
                while ($row = $result->fetch_object()) {
                  echo '<option value="' . $row->id_criteria . '">' . $row->criteria . '</option>';
                }
                $result->free();
                ?>
              </select>
            </div>

            <label>Nilai:</label>
            <div class="form-group">
              <input type="number" name="value" class="form-control" required min="0" max="5" step="0.1"
                placeholder="Masukkan nilai..."
                oninput="if(this.value > 5) this.value = 5;">
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary ml-1">Simpan</button>
          </div>

        </form>
      </div>
    </div>
  </div>

  // kode javascript untuk mengelola pilihan tahun tanpa validasi

  <script>
    // Nama bulan
    const namaBulan = {
      "01": "Januari",
      "02": "Februari",
      "03": "Maret",
      "04": "April",
      "05": "Mei",
      "06": "Juni",
      "07": "Juli",
      "08": "Agustus",
      "09": "September",
      "10": "Oktober",
      "11": "November",
      "12": "Desember"
    };

    const tahunInput = document.getElementById("tahunInput");
    const bulanSelect = document.getElementById("bulanSelect");
    const periodInput = document.getElementById("periodInput");

    // Generate bulan selalu lengkap (1–12)
    function updateBulan() {
      const selectedYear = tahunInput.value;
      bulanSelect.innerHTML = "";

      if (!selectedYear) return;

      for (let i = 1; i <= 12; i++) {
        const mm = i.toString().padStart(2, "0");
        const option = document.createElement("option");

        option.value = mm;
        option.textContent = namaBulan[mm];

        // Default ke Januari
        if (i === 1) {
          option.selected = true;
        }

        bulanSelect.appendChild(option);
      }

      updatePeriod();
    }

    // Generate period YYYY-MM
    function updatePeriod() {
      if (!tahunInput.value || !bulanSelect.value) return;
      periodInput.value = `${tahunInput.value}-${bulanSelect.value}`;
    }

    tahunInput.addEventListener("input", updateBulan);
    bulanSelect.addEventListener("change", updatePeriod);

    // Init saat load
    updateBulan();
  </script>

  // kode javascript untuk mengelola pilihan tahun dengan validasi

  // <script>
    //   // Nama bulan
    //   const namaBulan = {
    //     "01": "Januari",
    //     "02": "Februari",
    //     "03": "Maret",
    //     "04": "April",
    //     "05": "Mei",
    //     "06": "Juni",
    //     "07": "Juli",
    //     "08": "Agustus",
    //     "09": "September",
    //     "10": "Oktober",
    //     "11": "November",
    //     "12": "Desember"
    //   };

    //   const tahunInput = document.getElementById("tahunInput");
    //   const bulanSelect = document.getElementById("bulanSelect");
    //   const periodInput = document.getElementById("periodInput");

    //   const currentYear = new Date().getFullYear();
    //   const currentMonth = (new Date().getMonth() + 1).toString().padStart(2, "0");

    //   // Update list bulan sesuai aturan
    //   function updateBulan() {
    //     const selectedYear = parseInt(tahunInput.value);
    //     bulanSelect.innerHTML = "";

    //     if (!selectedYear) return;

    //     let maxMonth = 12;

    //     // Jika tahun sekarang → bulan hanya sampai bulan saat ini
    //     if (selectedYear === currentYear) {
    //       maxMonth = parseInt(currentMonth);
    //     }

    //     for (let i = 1; i <= maxMonth; i++) {
    //       const mm = i.toString().padStart(2, "0");
    //       const option = document.createElement("option");

    //       option.value = mm;
    //       option.textContent = namaBulan[mm];

    //       // Default bulan sekarang
    //       if (selectedYear === currentYear && mm === currentMonth) {
    //         option.selected = true;
    //       }

    //       bulanSelect.appendChild(option);
    //     }

    //     updatePeriod();
    //   }

    //   // Generate period YYYY-MM
    //   function updatePeriod() {
    //     if (!bulanSelect.value || !tahunInput.value) return;
    //     periodInput.value = `${tahunInput.value}-${bulanSelect.value}`;
    //   }

    //   tahunInput.addEventListener("input", updateBulan);
    //   bulanSelect.addEventListener("change", updatePeriod);

    //   // Init saat load
    //   updateBulan();
    // 
  </script>

  <?php require "layout/js.php"; ?>
</body>

</html>