<!DOCTYPE html>
<html lang="en">

<?php
require "layout/head.php";
require "include/conn.php";
?>

<body>
  <div id="app">
    <?php include "include/notification.php"; ?>
    <?php require "layout/sidebar.php"; ?>

    <div id="main">
      <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
          <i class="bi bi-justify fs-3"></i>
        </a>
      </header>

      <div class="page-heading">
        <h3>Bobot Kriteria</h3>
      </div>

      <div class="page-content">
        <section class="row">
          <div class="col-12">
            <div class="card">

              <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">Tabel Bobot Kriteria</h4>
                <a href="bobot-edit.php" class="btn btn-primary btn-sm">
                  <i class="bi bi-pencil-square"></i> Edit Bobot
                </a>
              </div>

              <div class="card-content">
                <div class="card-body">
                  <p class="card-text">
                    Pengambil keputusan memberi bobot preferensi dari setiap kriteria
                    dengan masing-masing jenisnya (keuntungan/benefit atau biaya/cost):
                  </p>
                </div>

                <div class="table-responsive">
                  <table class="table table-striped mb-0">
                    <caption>Tabel Kriteria C<sub>i</sub></caption>

                    <tr>
                      <th style='padding-left: 20px;'>No</th>
                      <th>Simbol</th>
                      <th>Kriteria</th>
                      <th>Bobot</th>
                      <th>Atribut</th>
                    </tr>

                    <?php
                    $sql = "SELECT * FROM saw_criterias ORDER BY id_criteria ASC";
                    $result = $db->query($sql);
                    $i = 0;

                    while ($row = $result->fetch_object()) {
                      $i++;
                      echo "
                        <tr>
                          <td class='right' style='padding-left: 20px;'>{$i}</td>
                          <td class='center'>C{$i}</td>
                          <td>{$row->criteria}</td>
                          <td>{$row->weight}</td>
                          <td>{$row->attribute}</td>
                        </tr>
                      ";
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

  <?php require "layout/js.php"; ?>
</body>

</html>