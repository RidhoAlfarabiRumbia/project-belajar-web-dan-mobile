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
        judul,
        pertemuan,
        deskripsi,
        link,
        file
    FROM materi
    WHERE user_id = ?
    ORDER BY id DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$materi = [];

while ($row = $result->fetch_assoc()) {
    $materi[] = [
        "id" => (int) $row["id"],
        "judul" => $row["judul"],
        "pertemuan" => $row["pertemuan"],
        "deskripsi" => $row["deskripsi"],
        "link" => $row["link"],
        "file" => $row["file"]
    ];
}

echo json_encode([
    "success" => true,
    "data" => $materi
]);

$stmt->close();
$conn->close();