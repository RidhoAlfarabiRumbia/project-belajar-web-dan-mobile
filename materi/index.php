<?php
require "../config/auth.php";
require "../config/koneksi.php";
require "../config/csrf.php";
require "../templates.php";

$user_id = (int) $_SESSION['user_id'];

$upload_dir = "uploads/";

function upload_materi($file, $upload_dir)
{
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'message' => 'File gagal diupload.'
        ];
    }

    $max_size = 40 * 1024 * 1024;

    if ($file['size'] > $max_size) {
        return [
            'success' => false,
            'message' => 'Ukuran file maksimal 40 MB.'
        ];
    }

    $allowed = [
        'pdf'  => 'application/pdf',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
    ];

    $nama_asli = $file['name'];
    $ext = strtolower(pathinfo($nama_asli, PATHINFO_EXTENSION));

    if (!array_key_exists($ext, $allowed)) {
        return [
            'success' => false,
            'message' => 'Format file tidak diperbolehkan.'
        ];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if ($ext === 'pdf' && $mime !== 'application/pdf') {
        return [
            'success' => false,
            'message' => 'File PDF tidak valid.'
        ];
    }

    if ($ext === 'ppt' && $mime !== 'application/vnd.ms-powerpoint') {
        return [
            'success' => false,
            'message' => 'File PPT tidak valid.'
        ];
    }

    $nama_file = bin2hex(random_bytes(16)) . "." . $ext;

    if (!move_uploaded_file(
        $file['tmp_name'],
        $upload_dir . $nama_file
    )) {
        return [
            'success' => false,
            'message' => 'File gagal disimpan.'
        ];
    }

    return [
        'success' => true,
        'filename' => $nama_file
    ];
}

if (isset($_POST['hapus'])) {

    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        die("Permintaan tidak valid.");
    }

    $id = (int) $_POST['hapus'];

    $data = $conn->query("SELECT file FROM materi WHERE id=$id")->fetch_assoc();

    if ($data && !empty($data['file'])) {
        $file_path = $upload_dir . $data['file'];

        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    $conn->query("DELETE FROM materi WHERE id=$id AND user_id=$user_id");

    header("Location: index.php");
    exit;
}


if (isset($_POST['simpan'])) {

    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        die("Permintaan tidak valid.");
    }

    $judul = $conn->real_escape_string($_POST['judul']);
    $pertemuan = $conn->real_escape_string($_POST['pertemuan']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
    $link = $conn->real_escape_string($_POST['link']);

    $nama_file = "";

    if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {

        $upload = upload_materi($_FILES['file'], $upload_dir);

        if (!$upload['success']) {
            die($upload['message']);
        }

        $nama_file = $upload['filename'];
    }

    $conn->query("
        INSERT INTO materi
        (user_id, judul, pertemuan, deskripsi, link, file)
        VALUES
        ($user_id, '$judul', '$pertemuan', '$deskripsi', '$link', '$nama_file')
    ");

    header("Location: index.php");
    exit;
}


if (isset($_POST['update'])) {

    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        die("Permintaan tidak valid.");
    }

    $id = (int) $_POST['id'];

    $judul = $conn->real_escape_string($_POST['judul']);
    $pertemuan = $conn->real_escape_string($_POST['pertemuan']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
    $link = $conn->real_escape_string($_POST['link']);

    $data_lama = $conn->query(
        "SELECT file FROM materi WHERE id=$id"
    )->fetch_assoc();

    $nama_file = $data_lama['file'];

    if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {

        $upload = upload_materi($_FILES['file'], $upload_dir);

        if (!$upload['success']) {
            die($upload['message']);
        }

        if (!empty($nama_file)) {
            $file_lama = $upload_dir . $nama_file;

            if (file_exists($file_lama)) {
                unlink($file_lama);
            }
        }

        $nama_file = $upload['filename'];
    }

    $conn->query("
        UPDATE materi SET
        judul='$judul',
        pertemuan='$pertemuan',
        deskripsi='$deskripsi',
        link='$link',
        file='$nama_file'
        WHERE id=$id AND user_id=$user_id
    ");

    header("Location: index.php");
    exit;
}


$edit_data = null;

if (isset($_GET['edit'])) {

    $id = (int) $_GET['edit'];

    $edit_data = $conn->query(
        "SELECT * FROM materi WHERE id=$id AND user_id=$user_id"
    )->fetch_assoc();
}

head("Materi");
?>

<div class="d-flex justify-content-between align-items-center mb-3">

    <h2>Materi</h2>

    <?php if ($edit_data): ?>

        <a href="index.php" class="btn btn-secondary">
            Batal Edit
        </a>

    <?php endif; ?>

</div>


<div class="card p-4 mb-4">

    <h5>
        <?= $edit_data ? "Edit Materi" : "Tambah Materi" ?>
    </h5>

    <form method="post" enctype="multipart/form-data">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(csrf_token()) ?>"
        >

        <?php if ($edit_data): ?>

            <input
                type="hidden"
                name="id"
                value="<?= $edit_data['id'] ?>"
            >

        <?php endif; ?>


        <div class="mb-3">

            <label class="form-label">
                Judul Materi
            </label>

            <input
                type="text"
                name="judul"
                class="form-control"
                value="<?= $edit_data ? htmlspecialchars($edit_data['judul']) : '' ?>"
                required
            >

        </div>


        <div class="mb-3">

            <label class="form-label">
                Pertemuan
            </label>

            <input
                type="text"
                name="pertemuan"
                class="form-control"
                placeholder="Contoh: Pertemuan 1"
                value="<?= $edit_data ? htmlspecialchars($edit_data['pertemuan']) : '' ?>"
            >

        </div>


        <div class="mb-3">

            <label class="form-label">
                Deskripsi
            </label>

            <textarea
                name="deskripsi"
                class="form-control"
                rows="3"
            ><?= $edit_data ? htmlspecialchars($edit_data['deskripsi']) : '' ?></textarea>

        </div>


        <div class="mb-3">

            <label class="form-label">
                Link Materi
            </label>

            <input
                type="url"
                name="link"
                class="form-control"
                placeholder="Google Drive / Colab / YouTube"
                value="<?= $edit_data ? htmlspecialchars($edit_data['link']) : '' ?>"
            >

        </div>


        <div class="mb-3">

            <label class="form-label">
                File Materi
            </label>

            <input
                type="file"
                name="file"
                class="form-control"
                accept=".pdf,.ppt,.pptx"
            >

            <small class="text-muted">
                Format yang diperbolehkan: PDF, PPT, PPTX
            </small>

            <?php if ($edit_data && !empty($edit_data['file'])): ?>

                <div class="mt-2">

                    File saat ini:
                    <a
                        href="download.php?id=<?= $edit_data['id'] ?>"
                        target="_blank"
                    >
                        <?= htmlspecialchars($edit_data['file']) ?>
                    </a>

                </div>

            <?php endif; ?>

        </div>


        <?php if ($edit_data): ?>

            <button
                type="submit"
                name="update"
                class="btn btn-dark"
            >
                Update Materi
            </button>

        <?php else: ?>

            <button
                type="submit"
                name="simpan"
                class="btn btn-dark"
            >
                Simpan Materi
            </button>

        <?php endif; ?>

    </form>

</div>


<div class="card p-4">

    <h5 class="mb-3">
        Daftar Materi
    </h5>

    <div class="table-responsive">

        <table class="table table-hover">

            <thead>

                <tr>

                    <th>Judul</th>
                    <th>Pertemuan</th>
                    <th>File</th>
                    <th>Link</th>
                    <th>Aksi</th>

                </tr>

            </thead>

            <tbody>

                <?php

                $q = $conn->query(
                    "SELECT * FROM materi
                    WHERE user_id=$user_id
                    ORDER BY id DESC"
                );

                while ($r = $q->fetch_assoc()):

                ?>

                <tr>

                    <td>
                        <?= htmlspecialchars($r['judul']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($r['pertemuan']) ?>
                    </td>

                    <td>

                        <?php if (!empty($r['file'])): ?>

                            <a
                                href="download.php?id=<?= $r['id'] ?>"
                                target="_blank"
                                class="btn btn-sm btn-outline-primary"
                            >
                                Buka File
                            </a>

                        <?php else: ?>

                            <span class="text-muted">
                                Tidak ada
                            </span>

                        <?php endif; ?>

                    </td>

                    <td>

                        <?php if (!empty($r['link'])): ?>

                            <a
                                href="<?= htmlspecialchars($r['link']) ?>"
                                target="_blank"
                                class="btn btn-sm btn-outline-secondary"
                            >
                                Buka Link
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

                        <form
                            method="post"
                            style="display:inline;"
                            onsubmit="return confirm('Yakin ingin menghapus materi ini?')"
                        >

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
                                class="btn btn-sm btn-danger"
                            >
                                Hapus
                            </button>

                        </form>

                    </td>

                </tr>

                <?php endwhile; ?>

            </tbody>

        </table>

    </div>

</div>


<?php foot(); ?>