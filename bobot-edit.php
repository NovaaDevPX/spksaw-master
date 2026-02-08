<?php
require "include/conn.php";
require "include/notification-helper.php";

// Jika form disubmit
if ($_SERVER["REQUEST_METHOD"] === "POST") {

  foreach ($_POST["criteria"] as $index => $criteria) {

    $criteria = $db->real_escape_string($criteria);
    $weight = floatval($_POST["weight"][$index]);
    $attribute = $db->real_escape_string($_POST["attribute"][$index]);

    // UPDATE
    if (!empty($_POST["id"][$index])) {
      $id = intval($_POST["id"][$index]);

      $db->query("
        UPDATE saw_criterias SET
          criteria = '$criteria',
          weight = '$weight',
          attribute = '$attribute'
        WHERE id_criteria = $id
      ");
    } else {
      // INSERT
      $db->query("
        INSERT INTO saw_criterias (criteria, weight, attribute)
        VALUES ('$criteria', '$weight', '$attribute')
      ");
    }
  }

  /* ===========================
     NOTIFIKASI
  =========================== */
  $title = "Bobot Kriteria Diperbarui";
  $message = "Bobot kriteria telah diperbarui atau ditambahkan.";

  // kirim ke admin
  createNotification($db, $title, $message, "admin", null);

  // kirim ke quality_control
  createNotification($db, $title, $message, "quality_control", null);

  header("Location: bobot.php?msg=Bobot berhasil diperbarui&type=success");
  exit;
}

// Ambil data
$result = $db->query("SELECT * FROM saw_criterias ORDER BY id_criteria ASC");
?>


<!DOCTYPE html>
<html lang="en">
<?php require "layout/head.php"; ?>

<body>

  <div id="app">
    <?php require "layout/sidebar.php"; ?>

    <div id="main">

      <div class="page-heading">
        <h3>Edit Bobot Kriteria</h3>
      </div>

      <div class="page-content">
        <section class="row">
          <div class="col-12">

            <form method="POST">
              <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">
                  <h4 class="card-title">Edit & Tambah Kriteria</h4>

                  <button type="button" id="addRow" class="btn btn-success btn-sm">
                    <i class="bi bi-plus-lg"></i> Tambah Baris
                  </button>
                </div>

                <div class="card-body">

                  <table class="table table-bordered" id="criteriaTable">
                    <thead>
                      <tr>
                        <th>No</th>
                        <th>Kriteria</th>
                        <th>Bobot</th>
                        <th>Atribut</th>
                      </tr>
                    </thead>

                    <tbody>
                      <?php
                      $no = 1;
                      while ($row = $result->fetch_object()):
                      ?>
                        <tr>
                          <td><?= $no ?></td>

                          <td>
                            <input type="hidden" name="id[]" value="<?= $row->id_criteria ?>">
                            <input type="text" name="criteria[]" class="form-control"
                              value="<?= htmlspecialchars($row->criteria) ?>" required>
                          </td>

                          <td>
                            <input type="number" step="0.01" name="weight[]" class="form-control weight-input"
                              value="<?= $row->weight ?>" required>
                          </td>

                          <td>
                            <select name="attribute[]" class="form-control">
                              <option value="benefit" <?= $row->attribute == "benefit" ? "selected" : "" ?>>Benefit</option>
                              <option value="cost" <?= $row->attribute == "cost" ? "selected" : "" ?>>Cost</option>
                            </select>
                          </td>
                        </tr>
                      <?php
                        $no++;
                      endwhile;
                      ?>
                    </tbody>
                  </table>

                  <!-- Live total bobot info -->
                  <div id="weightInfo" class="mt-2 fw-bold"></div>

                  <div class="text-end mt-3">
                    <a href="bobot.php" class="btn btn-secondary">Kembali</a>
                    <button type="submit" id="submitBtn" class="btn btn-primary">Simpan Perubahan</button>
                  </div>

                </div>
              </div>
            </form>

          </div>
        </section>
      </div>

      <?php require "layout/footer.php"; ?>

    </div>
  </div>

  <?php require "layout/js.php"; ?>

  <!-- Script Validasi Live -->
  <script>
    // Tambah baris baru
    document.getElementById("addRow").addEventListener("click", function() {
      let table = document.querySelector("#criteriaTable tbody");
      let rowCount = table.rows.length + 1;

      let newRow = `
        <tr>
          <td>${rowCount}</td>
          <td>
            <input type="hidden" name="id[]" value="">
            <input type="text" name="criteria[]" class="form-control" placeholder="Nama Kriteria" required>
          </td>
          <td>
            <input type="number" step="0.01" name="weight[]" class="form-control weight-input" placeholder="0.00" required>
          </td>
          <td>
            <select name="attribute[]" class="form-control">
              <option value="benefit">Benefit</option>
              <option value="cost">Cost</option>
            </select>
          </td>
        </tr>
      `;

      table.insertAdjacentHTML("beforeend", newRow);

      attachWeightListener();
    });

    // Hitung total bobot
    function calculateTotal() {
      let weights = document.querySelectorAll(".weight-input");
      let total = 0;

      weights.forEach(w => {
        let val = parseFloat(w.value);
        if (!isNaN(val)) total += val;
      });

      total = total.toFixed(2);

      const info = document.getElementById("weightInfo");
      const submitBtn = document.getElementById("submitBtn");

      if (total == 100) {
        info.innerHTML = `<span class="text-success">Total Bobot: ${total} ✔️ (Valid)</span>`;
        submitBtn.disabled = false;
      } else {
        info.innerHTML = `<span class="text-danger">Total Bobot: ${total} ❌ (Harus 100)</span>`;
        submitBtn.disabled = true;
      }
    }

    // Pasang event listener ke semua input bobot
    function attachWeightListener() {
      document.querySelectorAll(".weight-input").forEach(input => {
        input.removeEventListener("input", calculateTotal);
        input.addEventListener("input", calculateTotal);
      });
    }

    // Pertama kali load
    attachWeightListener();
    calculateTotal();
  </script>

</body>

</html>