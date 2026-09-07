<?php
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
require "config/koneksi.php";

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';


    if ($nama === '' || $email === '' || $password === '' || $konfirmasi === '') {

        $error = "Semua data wajib diisi.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Format email tidak valid.";

    } elseif (strlen($password) < 6) {

        $error = "Password minimal 6 karakter.";

    } elseif ($password !== $konfirmasi) {

        $error = "Konfirmasi password tidak cocok.";

    } else {

        // Cek email sudah digunakan atau belum
        $cek = $conn->prepare(
            "SELECT id FROM users WHERE email = ? LIMIT 1"
        );

        $cek->bind_param("s", $email);
        $cek->execute();

        $hasil = $cek->get_result();

        if ($hasil->num_rows > 0) {

            $error = "Email tersebut sudah terdaftar.";

        } else {

            $hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare(
                "INSERT INTO users (nama, email, password)
                 VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                "sss",
                $nama,
                $email,
                $hash
            );

            if ($stmt->execute()) {

                $success = "Akun berhasil dibuat. Silakan login.";

            } else {

                $error = "Gagal membuat akun.";
            }

            $stmt->close();
        }

        $cek->close();
    }
}
?>

<!doctype html>
<html lang="id">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width,initial-scale=1"
    >

    <title>Daftar Akun - Portal Magang</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="assets/style.css"
        rel="stylesheet"
    >

</head>

<body>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-md-5">

            <div class="card p-4">

                <h3 class="mb-1">
                    Daftar Akun
                </h3>

                <p class="text-muted">
                    Portal Magang
                </p>


                <?php if ($error): ?>

                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error) ?>
                    </div>

                <?php endif; ?>


                <?php if ($success): ?>

                    <div class="alert alert-success">
                        <?= htmlspecialchars($success) ?>
                    </div>

                <?php endif; ?>


                <form method="post">

                    <input
                        class="form-control mb-3"
                        name="nama"
                        placeholder="Nama lengkap"
                        required
                    >


                    <input
                        class="form-control mb-3"
                        name="email"
                        type="email"
                        placeholder="Email"
                        required
                    >


                    <input
                        class="form-control mb-3"
                        name="password"
                        type="password"
                        placeholder="Password minimal 6 karakter"
                        required
                    >


                    <input
                        class="form-control mb-3"
                        name="konfirmasi"
                        type="password"
                        placeholder="Konfirmasi password"
                        required
                    >


                    <button
                        type="submit"
                        class="btn btn-dark w-100"
                    >
                        Daftar
                    </button>

                </form>


                <div class="text-center mt-3">

                    <a href="index.php">
                        Kembali ke Login
                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>