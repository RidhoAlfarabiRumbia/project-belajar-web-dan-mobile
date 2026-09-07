<?php
require "../config/auth.php";
require "../config/koneksi.php";
require "../config/csrf.php";
require "../templates.php";
$user_id = (int) $_SESSION['user_id'];

$folderFoto = "../uploads/logbook/";


// =========================
// TAMBAH LOGBOOK
// =========================
if (isset($_POST['simpan'])) {

    if (!csrf_check($_POST['csrf_token'] ?? '')) { // fitur CSRF untuk keamanan
            die("Permintaan tidak valid.");
        }

    $pertemuan = $conn->real_escape_string($_POST['pertemuan']);
    $tanggal   = $conn->real_escape_string($_POST['tanggal']);
    $sekolah   = $conn->real_escape_string($_POST['sekolah']);
    $kelas     = $conn->real_escape_string($_POST['kelas']);
    $materi    = $conn->real_escape_string($_POST['materi']);
    $aktivitas = $conn->real_escape_string($_POST['aktivitas']);
    $kendala   = $conn->real_escape_string($_POST['kendala']);
    $catatan   = $conn->real_escape_string($_POST['catatan']);

    $namaFoto = null;

    if (
        isset($_FILES['foto']) &&
        $_FILES['foto']['error'] === UPLOAD_ERR_OK
    ) {

        if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
            die("Ukuran foto terlalu besar. Maksimal 5 MB.");
        }

        $mime = mime_content_type($_FILES['foto']['tmp_name']);

        $allowed = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (!in_array($mime, $allowed)) {
            die("Format foto harus JPG, PNG, atau WEBP.");
        }

        $ext = strtolower(
            pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION)
        );

        $namaFoto = uniqid("logbook_") . "." . $ext;

        move_uploaded_file(
            $_FILES['foto']['tmp_name'],
            $folderFoto . $namaFoto
        );
    }

    $fotoSQL = $namaFoto
        ? "'" . $conn->real_escape_string($namaFoto) . "'"
        : "NULL";

    $conn->query("
        INSERT INTO logbook
        (
            user_id,
            pertemuan,
            tanggal,
            sekolah,
            kelas,
            materi,
            aktivitas,
            kendala,
            catatan,
            foto
        )
        VALUES
        (
            $user_id,
            '$pertemuan',
            '$tanggal',
            '$sekolah',
            '$kelas',
            '$materi',
            '$aktivitas',
            '$kendala',
            '$catatan',
            $fotoSQL
        )
    ");

    header("Location:index.php");
    exit;
}


// =========================
// HAPUS LOGBOOK
// =========================
if (isset($_POST['hapus'])) {

    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        die("Permintaan tidak valid.");
    }

    $id = (int) $_POST['hapus'];

    $data = $conn->query("
    SELECT foto
    FROM logbook
    WHERE id=$id
    AND user_id=$user_id
    ")->fetch_assoc();

    if ($data && !empty($data['foto'])) {

        $file = $folderFoto . $data['foto'];

        if (file_exists($file)) {
            unlink($file);
        }
    }

    $conn->query("
    DELETE FROM logbook
    WHERE id=$id
    AND user_id=$user_id
   ");

    header("Location:index.php");
    exit;
}


// =========================
// UPDATE LOGBOOK
// =========================
if (isset($_POST['update'])) {

    if (!csrf_check($_POST['csrf_token'] ?? '')) { // CSRF
        die("Permintaan tidak valid.");
    }

    $id        = (int) $_POST['id'];
    $pertemuan = $conn->real_escape_string($_POST['pertemuan']);
    $tanggal   = $conn->real_escape_string($_POST['tanggal']);
    $sekolah   = $conn->real_escape_string($_POST['sekolah']);
    $kelas     = $conn->real_escape_string($_POST['kelas']);
    $materi    = $conn->real_escape_string($_POST['materi']);
    $aktivitas = $conn->real_escape_string($_POST['aktivitas']);
    $kendala   = $conn->real_escape_string($_POST['kendala']);
    $catatan   = $conn->real_escape_string($_POST['catatan']);

    $dataLama = $conn->query("
    SELECT foto
    FROM logbook
    WHERE id=$id
    AND user_id=$user_id
    ")->fetch_assoc();

    $namaFoto = $dataLama['foto'] ?? null;


    // Kalau upload foto baru
    if (
        isset($_FILES['foto']) &&
        $_FILES['foto']['error'] === UPLOAD_ERR_OK
    ) {

        if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
            die("Ukuran foto terlalu besar. Maksimal 5 MB.");
        }

        $mime = mime_content_type($_FILES['foto']['tmp_name']);

        $allowed = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (!in_array($mime, $allowed)) {
            die("Format foto harus JPG, PNG, atau WEBP.");
        }

        // Hapus foto lama
        if (!empty($namaFoto)) {

            $fileLama = $folderFoto . $namaFoto;

            if (file_exists($fileLama)) {
                unlink($fileLama);
            }
        }

        $ext = strtolower(
            pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION)
        );

        $namaFoto = uniqid("logbook_") . "." . $ext;

        move_uploaded_file(
            $_FILES['foto']['tmp_name'],
            $folderFoto . $namaFoto
        );
    }


    $fotoSQL = $namaFoto
        ? "'" . $conn->real_escape_string($namaFoto) . "'"
        : "NULL";


    $conn->query("
        UPDATE logbook
        SET
            pertemuan='$pertemuan',
            tanggal='$tanggal',
            sekolah='$sekolah',
            kelas='$kelas',
            materi='$materi',
            aktivitas='$aktivitas',
            kendala='$kendala',
            catatan='$catatan',
            foto=$fotoSQL
            WHERE id=$id
            AND user_id=$user_id
    ");

    header("Location:index.php");
    exit;
}


// =========================
// DATA UNTUK EDIT
// =========================
$edit = null;

if (isset($_GET['edit'])) {

    $id = (int) $_GET['edit'];

    $edit = $conn->query("
    SELECT *
    FROM logbook
    WHERE id=$id
    AND user_id=$user_id
    ")->fetch_assoc();
}


head("Logbook");
?>

<h2>Logbook Mengajar</h2>


<!-- FORM -->
<div class="card p-3 my-3">

    <h5>
        <?= $edit ? "Edit Logbook" : "Tambah Logbook" ?>
    </h5>

    <form
        method="post"
        enctype="multipart/form-data"
        class="row g-2"
    >

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(csrf_token()) ?>"
        >

        <?php if ($edit): ?>

            <input
                type="hidden"
                name="id"
                value="<?= $edit['id'] ?>"
            >

        <?php endif; ?>


        <div class="col-md-3">

            <input
                name="pertemuan"
                class="form-control"
                placeholder="Pertemuan ke-1"
                value="<?= $edit ? htmlspecialchars($edit['pertemuan']) : '' ?>"
                required
            >

        </div>


        <div class="col-md-3">

            <input
                type="date"
                name="tanggal"
                class="form-control"
                value="<?= $edit ? $edit['tanggal'] : '' ?>"
                required
            >

        </div>


        <div class="col-md-3">

            <input
                name="sekolah"
                class="form-control"
                placeholder="Sekolah"
                value="<?= $edit ? htmlspecialchars($edit['sekolah']) : '' ?>"
                required
            >

        </div>


        <div class="col-md-3">

            <input
                name="kelas"
                class="form-control"
                placeholder="Kelas"
                value="<?= $edit ? htmlspecialchars($edit['kelas']) : '' ?>"
                required
            >

        </div>


        <div class="col-md-6">

            <input
                name="materi"
                class="form-control"
                placeholder="Materi"
                value="<?= $edit ? htmlspecialchars($edit['materi']) : '' ?>"
                required
            >

        </div>


        <div class="col-md-6">

            <input
                name="aktivitas"
                class="form-control"
                placeholder="Aktivitas mengajar"
                value="<?= $edit ? htmlspecialchars($edit['aktivitas']) : '' ?>"
                required
            >

        </div>


        <div class="col-md-6">

            <textarea
                name="kendala"
                class="form-control"
                placeholder="Kendala"
            ><?= $edit ? htmlspecialchars($edit['kendala']) : '' ?></textarea>

        </div>


        <div class="col-md-6">

            <textarea
                name="catatan"
                class="form-control"
                placeholder="Catatan"
            ><?= $edit ? htmlspecialchars($edit['catatan']) : '' ?></textarea>

        </div>


        <div class="col-md-6">

            <label class="form-label">
                Foto Dokumentasi
            </label>

            <input
                type="file"
                name="foto"
                class="form-control"
                accept="image/jpeg,image/png,image/webp"
            >

            <small class="text-muted">
                JPG, PNG, WEBP. Maksimal 5 MB.
            </small>

            <?php if ($edit && !empty($edit['foto'])): ?>

                <div class="mt-2">

                    Foto saat ini:

                    <br>

                    <img
                        src="<?= $folderFoto . htmlspecialchars($edit['foto']) ?>"
                        width="120"
                        style="border-radius:6px;"
                    >

                </div>

            <?php endif; ?>

        </div>


        <div>

            <?php if ($edit): ?>

                <button
                    name="update"
                    class="btn btn-dark"
                >
                    Update Logbook
                </button>

                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    Batal
                </a>

            <?php else: ?>

                <button
                    name="simpan"
                    class="btn btn-dark"
                >
                    Simpan Logbook
                </button>

            <?php endif; ?>

        </div>

    </form>

</div>


<!-- DAFTAR LOGBOOK -->
<div class="card p-3">

    <table class="table table-hover">

        <thead>

            <tr>
                <th>Pertemuan</th>
                <th>Tanggal</th>
                <th>Kelas</th>
                <th>Materi</th>
                <th>Aktivitas</th>
                <th>Foto</th>
                <th>Aksi</th>
            </tr>

        </thead>


        <tbody>

        <?php

        $q = $conn->query("
           SELECT * FROM logbook
           WHERE user_id=$user_id
           ORDER BY id DESC
        ");

        while ($r = $q->fetch_assoc()):

        ?>

            <tr>

                <td>
                    <?= htmlspecialchars($r['pertemuan']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($r['tanggal']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($r['kelas']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($r['materi']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($r['aktivitas']) ?>
                </td>

                <td>

                    <?php if (!empty($r['foto'])): ?>

                        <a
                            href="../uploads/logbook/<?= htmlspecialchars($r['foto']) ?>"
                            target="_blank"
                        >

                            <img
                                src="../uploads/logbook/<?= htmlspecialchars($r['foto']) ?>"
                                width="80"
                                height="60"
                                style="object-fit:cover;border-radius:6px;"
                            >

                        </a>

                        <br>

                        <a
                            href="../uploads/logbook/<?= htmlspecialchars($r['foto']) ?>"
                            download
                            class="btn btn-sm btn-success mt-1"
                        >
                            Unduh
                        </a>

                    <?php else: ?>

                        <span class="text-muted">
                            Tidak ada
                        </span>

                    <?php endif; ?>

                </td>


                <td>

                    <a
                        href="?edit=<?= $r['id'] ?>"
                        class="btn btn-sm btn-warning"
                    >
                        Edit
                    </a>

                    <form method="post"
                        style="display:inline;"
                        onsubmit="return confirm('Hapus logbook ini?')">

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= htmlspecialchars(csrf_token()) ?>"
                        >

                        <input
                            type="hidden"
                            name="hapus"
                            value="<?= $r['id'] ?>"
                        >

                        <button
                            type="submit"
                            class="btn btn-sm btn-danger">
                            Hapus
                        </button>

                    </form>

                </td>

            </tr>

        <?php endwhile; ?>

        </tbody>

    </table>

</div>


<?php foot(); ?>