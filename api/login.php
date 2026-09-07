<?php

header("Content-Type: application/json");

require "../config/koneksi.php";

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    echo json_encode([
        "success" => false,
        "message" => "Email dan password wajib diisi."
    ]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, nama, email, password
     FROM users
     WHERE email = ?
     LIMIT 1"
);

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo json_encode([
        "success" => false,
        "message" => "Email atau password salah."
    ]);
    exit;
}

$user = $result->fetch_assoc();

$valid = false;

if (
    password_get_info($user['password'])['algo'] !== 0 &&
    password_verify($password, $user['password'])
) {
    $valid = true;

} elseif (
    strlen($user['password']) === 32 &&
    hash_equals($user['password'], md5($password))
) {
    $valid = true;
}

if (!$valid) {
    echo json_encode([
        "success" => false,
        "message" => "Email atau password salah."
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "message" => "Login berhasil.",
    "user" => [
        "id" => $user['id'],
        "nama" => $user['nama'],
        "email" => $user['email']
    ]
]);

$stmt->close();
$conn->close();