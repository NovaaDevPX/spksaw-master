<!DOCTYPE html>
<html lang="en">

<?php require "layout/head.php"; ?>

<body>
  <div id="app">

    <?php require "layout/sidebar.php"; ?>

    <div id="main">

      <!-- HEADER -->
      <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
          <i class="bi bi-justify fs-3"></i>
        </a>
      </header>

      <!-- PAGE TITLE -->
      <div class="page-heading mb-4">
        <h3 class="fw-bold">Dashboard</h3>
        <p class="text-muted mb-0">
          Sistem Pendukung Keputusan Mitra Kerja Terbaik
        </p>
      </div>

      <!-- CONTENT -->
      <div class="page-content">
        <section class="row g-4">

          <!-- HERO / INTRO -->
          <div class="col-12">
            <div class="card border-0 shadow-sm">
              <div class="card-body p-4">
                <h4 class="fw-bold mb-3">
                  <i class="bi bi-diagram-3-fill text-primary me-2"></i>
                  Metode Simple Additive Weighting (SAW)
                </h4>
                <p class="text-muted mb-0">
                  Simple Additive Weighting (SAW) adalah metode pengambilan
                  keputusan multikriteria dengan cara menjumlahkan nilai
                  terbobot dari setiap alternatif berdasarkan kriteria yang
                  telah ditentukan.
                </p>
              </div>
            </div>
          </div>

          <!-- TABEL PENILAIAN -->
          <div class="col-12">
            <div class="card border-0 shadow-sm">
              <div class="card-body p-4">

                <h5 class="fw-bold mb-3">
                  <i class="bi bi-clipboard-data-fill text-warning me-2"></i>
                  Acuan Penilaian Mitra
                </h5>

                <p class="text-muted mb-3">
                  Skala penilaian digunakan dari
                  <strong>1 (Sangat Buruk)</strong> sampai
                  <strong>5 (Sangat Baik)</strong>.
                </p>

                <div class="table-responsive">
                  <table class="table table-bordered table-hover align-middle text-center">
                    <thead class="table-light">
                      <tr>
                        <th width="80">Nilai</th>
                        <th>Waktu Pengiriman</th>
                        <th>Kualitas Barang</th>
                        <th>Solusi Masalah</th>
                        <th>Dokumen & BA</th>
                        <th>Waktu Penyelesaian</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td><span class="badge bg-danger">1</span></td>
                        <td>&gt; 7 hari</td>
                        <td>&gt; 0.0063%</td>
                        <td>Tidak responsif</td>
                        <td>&gt; 7 hari</td>
                        <td>Tidak responsif</td>
                      </tr>
                      <tr>
                        <td><span class="badge bg-warning text-dark">2</span></td>
                        <td>6 hari</td>
                        <td>0.0062% – 0.0044%</td>
                        <td>± 2 hari</td>
                        <td>6 hari</td>
                        <td>± 2 hari</td>
                      </tr>
                      <tr>
                        <td><span class="badge bg-secondary">3</span></td>
                        <td>4 hari</td>
                        <td>0.0043% – 0.0025%</td>
                        <td>± 1 hari</td>
                        <td>5 hari</td>
                        <td>± 1 hari</td>
                      </tr>
                      <tr>
                        <td><span class="badge bg-primary">4</span></td>
                        <td>2 hari</td>
                        <td>0.0024% – 0.0013%</td>
                        <td>± 5 jam</td>
                        <td>4 hari</td>
                        <td>± 5 jam</td>
                      </tr>
                      <tr class="table-success fw-bold">
                        <td><span class="badge bg-success">5</span></td>
                        <td>Tepat Waktu</td>
                        <td>&lt; 0.0013%</td>
                        <td>&lt; 4 jam</td>
                        <td>&lt; 3 hari</td>
                        <td>&lt; 4 jam</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- KETERANGAN -->
                <div class="alert alert-info mt-3 d-flex align-items-center">
                  <i class="bi bi-info-circle fs-4 me-3"></i>
                  <div>
                    <strong>Keterangan Nilai:</strong><br>
                    1 = Sangat Buruk |
                    2 = Buruk |
                    3 = Cukup |
                    4 = Baik |
                    5 = Sangat Baik
                  </div>
                </div>

              </div>
            </div>
          </div>

        </section>
      </div>

      <?php require "layout/footer.php"; ?>

    </div>
  </div>

  <?php require "layout/js.php"; ?>

</body>

</html>