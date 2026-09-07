<?php
require "../config/auth.php";
require "../config/koneksi.php";
require "../config/csrf.php";
require "../templates.php";

$user_id = (int) $_SESSION['user_id'];

if (isset($_POST['hapus'])) {

    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        die("Permintaan tidak valid.");
    }

    $id = (int) $_POST['hapus'];

    $conn->query("
        DELETE FROM kelas
        WHERE id=$id
        AND user_id=$user_id
    ");

    header("Location: index.php");
    exit;
}

if (isset($_POST['simpan'])) {

    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        die("Permintaan tidak valid.");
    }

    $nama = $conn->real_escape_string($_POST['nama_kelas']);

    $conn->query("
        INSERT INTO kelas (user_id, nama_kelas)
        VALUES ($user_id, '$nama')
    ");

    header("Location: index.php");
    exit;
}

if (isset($_POST['update'])) {

    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        die("Permintaan tidak valid.");
    }

    $id = (int) $_POST['id'];
    $nama = $conn->real_escape_string($_POST['nama_kelas']);

    $conn->query("
        UPDATE kelas
        SET nama_kelas='$nama'
        WHERE id=$id
        AND user_id=$user_id
    ");

    header("Location: index.php");
    exit;
}

$edit = null;

if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];

    $edit = $conn->query("
        SELECT * FROM kelas
        WHERE id=$id
        AND user_id=$user_id
    ")->fetch_assoc();
}

head("Kelas");
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Kelas</h2>

    <?php if ($edit): ?>
        <a href="index.php" class="btn btn-secondary">
            Batal
        </a>
    <?php endif; ?>
</div>

<div class="card p-4 mb-4">

    <h5>
        <?= $edit ? "Edit Kelas" : "Tambah Kelas" ?>
    </h5>

    <form method="post">

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

        <div class="mb-3">

            <label class="form-label">
                Nama Kelas
            </label>

            <input
                type="text"
                name="nama_kelas"
                class="form-control"
                placeholder="Contoh: X IPA 2"
                value="<?= $edit ? htmlspecialchars($edit['nama_kelas']) : '' ?>"
                required
            >

        </div>

        <?php if ($edit): ?>

            <button
                type="submit"
                name="update"
                class="btn btn-dark"
            >
                Update
            </button>

        <?php else: ?>

            <button
                type="submit"
                name="simpan"
                class="btn btn-dark"
            >
                Tambah Kelas
            </button>

        <?php endif; ?>

    </form>

</div>

<div class="card p-4">

    <table class="table table-hover">

        <thead>

            <tr>
                <th>Nama Kelas</th>
                <th>Jumlah Siswa</th>
                <th>Aksi</th>
            </tr>

        </thead>

        <tbody>

        <?php

        $q = $conn->query("
            SELECT
                k.*,
                COUNT(s.id) AS jumlah_siswa
            FROM kelas k
            LEFT JOIN siswa s
                ON s.kelas_id = k.id
            WHERE k.user_id = $user_id
            GROUP BY k.id
            ORDER BY k.id DESC
        ");

        while ($r = $q->fetch_assoc()):

        ?>

            <tr>

                <td>
                    <?= htmlspecialchars($r['nama_kelas']) ?>
                </td>

                <td>
                    <?= $r['jumlah_siswa'] ?>
                </td>

                <td>

                    <a
                        href="../siswa/index.php?kelas=<?= $r['id'] ?>"
                        class="btn btn-sm btn-primary"
                    >
                        Siswa
                    </a>

                    <a
                        href="?edit=<?= $r['id'] ?>"
                        class="btn btn-sm btn-warning"
                    >
                        Edit
                    </a>

                    <form
                        method="post"
                        style="display:inline;"
                        onsubmit="return confirm('Hapus kelas ini? Semua siswa di dalamnya juga akan terhapus.')"
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

<?php foot(); ?>