<?php 
require "config/auth.php"; 
require "config/koneksi.php"; 
require "templates.php"; 
head("Dashboard"); 
?>

<h2 class="mb-0">Dashboard <small class="text-muted fw-normal"></small></h2>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><i class="bi bi-house-door"></i></li>
  </ol>
</nav>



<div class="card mt-3 border-0 shadow-sm overflow-hidden">
  <div class="row g-0">
   
    <div class="col-md-8">
      <div class="card-body p-4">
        <h4 class="mb-1">Halo, <strong><?=htmlspecialchars($_SESSION['nama'])?></strong></h4>
        <p class="text-muted mb-4">Selamat datang kembali di portal ini.</p>

        <h4 class="fw-bold mb-1">PORTAL MAGANG</h4>
        <p class="text-secondary mb-0">
          Aplikasi pendukung untuk memantau dan mengelola kegiatan Program Magang KKA.
        </p>
      </div>
    </div>
  </div>
</div>

<?php foot(); ?>