<?php
require "include/conn.php";
require "include/notification-helper.php";

/* =========================
   PROSES SIMPAN
========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

  /* ===== HAPUS DATA ===== */
  if (!empty($_POST['delete_id'])) {
    foreach ($_POST['delete_id'] as $delId) {
      $delId = (int)$delId;
      $db->query("DELETE FROM saw_criterias WHERE id_criteria = $delId");
    }
  }

  /* ===== SIMPAN & UPDATE ===== */
  foreach ($_POST["criteria"] as $i => $criteria) {

    if (trim($criteria) === "") continue;

    $criteria  = $db->real_escape_string($criteria);
    $weight    = floatval($_POST["weight"][$i]);
    $attribute = $db->real_escape_string($_POST["attribute"][$i]);
    $id        = $_POST["id"][$i];

    if (!empty($id)) {
      // UPDATE
      $id = (int)$id;
      $db->query("
        UPDATE saw_criterias SET
          criteria  = '$criteria',
          weight    = '$weight',
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

  /* ===== NOTIFIKASI ===== */
  createNotification($db, "Bobot Diperbarui", "Bobot kriteria berhasil diperbarui", "admin", null);
  createNotification($db, "Bobot Diperbarui", "Bobot kriteria berhasil diperbarui", "quality_control", null);

  header("Location: bobot.php?success=updated");
  exit;
}

/* =========================
   DATA
========================= */
$result = $db->query("SELECT * FROM saw_criterias ORDER BY id_criteria ASC");
?>

<!DOCTYPE html>
<html lang="en">
<?php require "layout/head.php"; ?>

<body>
  <div id="app">
    <?php include "include/notification.php"; ?>
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

                <div class="card-header d-flex justify-content-between">
                  <h4 class="card-title">Edit & Tambah Kriteria</h4>
                  <button type="button" id="addRow" class="btn btn-success btn-sm">
                    <i class="bi bi-plus-lg"></i> Tambah
                  </button>
                </div>

                <div class="card-body">

                  <table class="table table-bordered" id="criteriaTable">
                    <thead>
                      <tr>
                        <th width="50">No</th>
                        <th>Kriteria</th>
                        <th width="120">Bobot</th>
                        <th width="120">Atribut</th>
                        <th width="80">Aksi</th>
                      </tr>
                    </thead>

                    <tbody>
                      <?php $no = 1;
                      while ($row = $result->fetch_object()): ?>
                        <tr>
                          <td class="row-no"><?= $no++ ?></td>

                          <td>
                            <input type="hidden" name="id[]" value="<?= $row->id_criteria ?>">
                            <input type="text" name="criteria[]" class="form-control"
                              value="<?= htmlspecialchars($row->criteria) ?>" required>
                          </td>

                          <td>
                            <input type="number" step="0.01" name="weight[]"
                              class="form-control weight-input"
                              value="<?= $row->weight ?>" required>
                          </td>

                          <td>
                            <select name="attribute[]" class="form-control">
                              <option value="benefit" <?= $row->attribute == "benefit" ? "selected" : "" ?>>Benefit</option>
                              <option value="cost" <?= $row->attribute == "cost" ? "selected" : "" ?>>Cost</option>
                            </select>
                          </td>

                          <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm removeRow">
                              Hapus
                            </button>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>

                  <input type="hidden" name="delete_id[]" id="deleteContainer">

                  <div id="weightInfo" class="fw-bold mt-2"></div>

                  <div class="text-end mt-3">
                    <a href="bobot.php" class="btn btn-secondary">Kembali</a>
                    <button class="btn btn-primary" id="submitBtn">Simpan</button>
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

  <script>
    const tableBody = document.querySelector("#criteriaTable tbody");
    let deletedIds = [];

    /* ===== TAMBAH BARIS ===== */
    document.getElementById("addRow").addEventListener("click", () => {
      tableBody.insertAdjacentHTML("beforeend", `
  <tr>
    <td class="row-no"></td>
    <td>
      <input type="hidden" name="id[]" value="">
      <input type="text" name="criteria[]" class="form-control" required>
    </td>
    <td>
      <input type="number" step="0.01" name="weight[]" class="form-control weight-input" required>
    </td>
    <td>
      <select name="attribute[]" class="form-control">
        <option value="benefit">Benefit</option>
        <option value="cost">Cost</option>
      </select>
    </td>
    <td class="text-center">
      <button type="button" class="btn btn-danger btn-sm removeRow">
        <i class="bi bi-dash-lg"></i>
      </button>
    </td>
  </tr>
  `);

      refreshRowNumber();
      attachEvents();
    });

    /* ===== HAPUS BARIS ===== */
    function attachEvents() {
      document.querySelectorAll(".removeRow").forEach(btn => {
        btn.onclick = function() {
          const row = this.closest("tr");
          const idInput = row.querySelector('input[name="id[]"]');

          if (idInput && idInput.value !== "") {
            deletedIds.push(idInput.value);
          }

          row.remove();
          refreshRowNumber();
          calculateTotal();
          document.getElementById("deleteContainer").value = deletedIds;
        };
      });

      document.querySelectorAll(".weight-input").forEach(w => {
        w.oninput = calculateTotal;
      });
    }

    /* ===== NOMOR ULANG ===== */
    function refreshRowNumber() {
      document.querySelectorAll(".row-no").forEach((td, i) => {
        td.innerText = i + 1;
      });
    }

    /* ===== TOTAL BOBOT ===== */
    function calculateTotal() {
      let total = 0;
      document.querySelectorAll(".weight-input").forEach(w => {
        let v = parseFloat(w.value);
        if (!isNaN(v)) total += v;
      });

      const info = document.getElementById("weightInfo");
      const btn = document.getElementById("submitBtn");

      total = total.toFixed(2);
      if (total == 100) {
        info.innerHTML = `<span class="text-success">Total Bobot: ${total} ✔</span>`;
        btn.disabled = false;
      } else {
        info.innerHTML = `<span class="text-danger">Total Bobot: ${total} ❌ (harus 100)</span>`;
        btn.disabled = true;
      }
    }

    attachEvents();
    calculateTotal();
  </script>

</body>

</html>