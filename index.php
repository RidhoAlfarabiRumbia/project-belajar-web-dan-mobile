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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = "Email dan password wajib diisi.";
    } else {

        $stmt = $conn->prepare(
            "SELECT id, nama, email, password FROM users WHERE email = ? LIMIT 1"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            /*
             * Mendukung password lama yang masih MD5.
             * Jika login berhasil menggunakan password lama,
             * password langsung diubah menjadi password_hash().
             */

            $valid = false;

            if (
                password_get_info($user['password'])['algo'] !== 0
                && password_verify($password, $user['password'])
            ) {

                $valid = true;

            } elseif (
                strlen($user['password']) === 32
                && hash_equals($user['password'], md5($password))
            ) {

                $valid = true;

                $hashBaru = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $update = $conn->prepare(
                    "UPDATE users SET password = ? WHERE id = ?"
                );

                $update->bind_param(
                    "si",
                    $hashBaru,
                    $user['id']
                );

                $update->execute();
            }

            if ($valid) {

                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];

                header("Location: dashboard.php");
                exit;

            } else {
                $error = "Email atau password salah.";
            }

        } else {
            $error = "Email atau password salah.";
        }

        $stmt->close();
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

    <title>Portal Magang</title>

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
                    Portal Magang
                </h3>

                <p class="text-muted">
                    Koding & Kecerdasan Artifisial
                </p>


                <?php if ($error): ?>

                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error) ?>
                    </div>

                <?php endif; ?>


                <form method="post">

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
                        placeholder="Password"
                        required
                    >

                    <button
                        type="submit"
                        class="btn btn-dark w-100"
                    >
                        Login
                    </button>

                </form>


                <div class="text-center mt-3">

                    <small class="text-muted">
                        Belum punya akun?
                    </small>

                    <br>

                    <a href="register.php">
                        Daftar akun
                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>