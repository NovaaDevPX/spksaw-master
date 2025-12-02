<div id="sidebar" class="active">
  <div class="sidebar-wrapper active">
    <div class="sidebar-header">
      <div class="d-flex justify-content-between">
        <div class="logo">
          <a href="/spksaw-master/index.php">
            <img src="/spksaw-master/assets/images/logo-telkomsel.webp" alt="Telkomsel Logo" style="height: 80px;">
          </a>
        </div>

        <div class="toggler">
          <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
        </div>
      </div>
    </div>

    <div class="sidebar-menu">
      <ul class="menu">
        <li class="sidebar-title">Menu</li>

        <!-- Dashboard -->
        <li class="sidebar-item">
          <a href="/spksaw-master/index.php" class='sidebar-link'>
            <i class="bi bi-grid-fill"></i>
            <span>Dashboard</span>
          </a>
        </li>

        <?php
        $role = $_SESSION["role"];

        if ($role == "master" || $role == "admin") {
          echo "
          <li class='sidebar-item has-sub'>
            <a href='#' class='sidebar-link'>
              <i class='bi bi-file-earmark-spreadsheet-fill'></i>
              <span>Data</span>
            </a>
            <ul class='submenu'>
              <li class='submenu-item'><a href='/spksaw-master/alternatif.php'>Alternatif</a></li>
              <li class='submenu-item'><a href='/spksaw-master/bobot.php'>Bobot & Kriteria</a></li>
            </ul>
          </li>

          <li class='sidebar-item'>
            <a href='/spksaw-master/matrik.php' class='sidebar-link'>
              <i class='bi bi-pentagon-fill'></i>
              <span>Matrik</span>
            </a>
          </li>
          ";
        }

        if ($role == "master") {
          echo "
          <li class='sidebar-item has-sub'>
            <a href='#' class='sidebar-link'>
              <i class='bi bi-people-fill'></i>
              <span>User Management</span>
            </a>
            <ul class='submenu'>
              <li class='submenu-item'><a href='/spksaw-master/user-management/list-user.php'>Daftar User</a></li>
            </ul>
          </li>
          ";
        }

        echo "
        <li class='sidebar-item'>
          <a href='/spksaw-master/preferensi.php' class='sidebar-link'>
            <i class='bi bi-bar-chart-fill'></i>
            <span>Nilai Preferensi</span>
          </a>
        </li>
        ";
        ?>

        <!-- Logout -->
        <li class="sidebar-item">
          <a href="/spksaw-master/logout.php" class="sidebar-link">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
          </a>
        </li>

      </ul>
    </div>

    <button class="sidebar-toggler btn x"><i data-feather="x"></i></button>
  </div>
</div>