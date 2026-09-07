<?php

header("Content-Type: application/json");

require "../config/koneksi.php";

$user_id = isset($_GET['user_id'])
    ? (int) $_GET['user_id']
    : 0;

if ($user_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "User tidak valid."
    ]);
    exit;
}

$stmt = $conn->prepare("
    SELECT
        id,
        pertemuan,
        tanggal,
        sekolah,
        kelas,
        materi,
        aktivitas,
        kendala,
        catatan,
        foto
    FROM logbook
    WHERE user_id = ?
    ORDER BY id DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $data
]);

$stmt->close();
$conn->close();