<?php
require "../config/auth.php";
require "../config/koneksi.php";
require "../config/csrf.php";
require "../templates.php";

$user_id = (int) $_SESSION['user_id'];

if (!isset($_GET['kelas']) && !isset($_POST['kelas_id'])) {

    head("Daftar Siswa");
    ?>

    <h2>Daftar Siswa</h2>

    <p class="text-muted">
        Pilih kelas terlebih dahulu.
    </p>

    <div class="card p-4">

        <div class="list-group">

        <?php

        $q = $conn->query("
            SELECT
                k.id,
                k.nama_kelas,
                COUNT(s.id) AS jumlah_siswa
            FROM kelas k
            LEFT JOIN siswa s
                ON s.kelas_id = k.id
            WHERE k.user_id = $user_id
            GROUP BY k.id
            ORDER BY k.nama_kelas
        ");

        while ($r = $q->fetch_assoc()):

        ?>

            <a
                href="?kelas=<?= $r['id'] ?>"
                class="list-group-item list-group-item-action d-flex justify-content-between"
            >

                <span>
                    <?= htmlspecialchars($r['nama_kelas']) ?>
                </span>

                <span class="badge text-bg-secondary">
                    <?= $r['jumlah_siswa'] ?> siswa
                </span>

            </a>

        <?php endwhile; ?>

        </div>

    </div>

    <?php

    foot();
    exit;
}


$kelas_id = isset($_GET['kelas'])
    ? (int) $_GET['kelas']
    : (int) $_POST['kelas_id'];


$kelas = $conn->query("
    SELECT *
    FROM kelas
    WHERE id=$kelas_id
    AND user_id=$user_id
")->fetch_assoc();

if (!$kelas) {
    header("Location: index.php");
    exit;
}


if (isset($_POST['hapus'])) {

    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        die("Permintaan tidak valid.");
    }

    $id = (int) $_POST['hapus'];

    $conn->query("
        DELETE FROM siswa
        WHERE id=$id
        AND kelas_id=$kelas_id
    ");

    header("Location: index.php?kelas=$kelas_id");
    exit;
}


if (isset($_POST['simpan'])) {

    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        die("Permintaan tidak valid.");
    }

    $nama = $conn->real_escape_string(
        $_POST['nama_siswa']
    );

    $conn->query("
        INSERT INTO siswa
        (kelas_id, nama_siswa)
        VALUES
        ($kelas_id, '$nama')
    ");

    header("Location: index.php?kelas=$kelas_id");
    exit;
}


if (isset($_POST['update'])) {

    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        die("Permintaan tidak valid.");
    }

    $id = (int) $_POST['id'];

    $nama = $conn->real_escape_string(
        $_POST['nama_siswa']
    );

    $conn->query("
        UPDATE siswa
        SET nama_siswa='$nama'
        WHERE id=$id
        AND kelas_id=$kelas_id
    ");

    header("Location: index.php?kelas=$kelas_id");
    exit;
}


$edit = null;

if (isset($_GET['edit'])) {

    $id = (int) $_GET['edit'];

    $edit = $conn->query("
        SELECT *
        FROM siswa
        WHERE id=$id
        AND kelas_id=$kelas_id
    ")->fetch_assoc();
}


head("Daftar Siswa");
?>

<div class="d-flex justify-content-between align-items-center mb-3">

    <div>

        <h2>Daftar Siswa</h2>

        <p class="text-muted mb-0">
            Kelas:
            <strong>
                <?= htmlspecialchars($kelas['nama_kelas']) ?>
            </strong>
        </p>

    </div>

    <a
        href="../kelas/index.php"
        class="btn btn-secondary"
    >
        Ganti Kelas
    </a>

</div>


<div class="card p-4 mb-4">

    <h5>
        <?= $edit ? "Edit Siswa" : "Tambah Siswa" ?>
    </h5>

    <form method="post">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(csrf_token()) ?>"
        >

        <input
            type="hidden"
            name="kelas_id"
            value="<?= $kelas_id ?>"
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
                Nama Siswa
            </label>

            <input
                type="text"
                name="nama_siswa"
                class="form-control"
                placeholder="Nama lengkap siswa"
                value="<?= $edit ? htmlspecialchars($edit['nama_siswa']) : '' ?>"
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

            <a
                href="?kelas=<?= $kelas_id ?>"
                class="btn btn-secondary"
            >
                Batal
            </a>

        <?php else: ?>

            <button
                type="submit"
                name="simpan"
                class="btn btn-dark"
            >
                Tambah Siswa
            </button>

        <?php endif; ?>

    </form>

</div>


<div class="card p-4">

    <table class="table table-hover">

        <thead>

            <tr>
                <th>No.</th>
                <th>Nama Siswa</th>
                <th>Aksi</th>
            </tr>

        </thead>

        <tbody>

        <?php

        $q = $conn->query("
            SELECT *
            FROM siswa
            WHERE kelas_id=$kelas_id
            ORDER BY nama_siswa
        ");

        $no = 1;

        while ($r = $q->fetch_assoc()):

        ?>

            <tr>

                <td>
                    <?= $no++ ?>
                </td>

                <td>
                    <?= htmlspecialchars($r['nama_siswa']) ?>
                </td>

                <td>

                    <a
                        href="?kelas=<?= $kelas_id ?>&edit=<?= $r['id'] ?>"
                        class="btn btn-sm btn-warning"
                    >
                        Edit
                    </a>

                    <form
                        method="post"
                        style="display:inline;"
                        onsubmit="return confirm('Hapus siswa ini?')"
                    >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= htmlspecialchars(csrf_token()) ?>"
                        >

                        <input
                            type="hidden"
                            name="kelas_id"
                            value="<?= $kelas_id ?>"
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