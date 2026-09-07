<?php

header("Content-Type: application/json");

require "../config/koneksi.php";

$user_id = isset($_GET['user_id'])
    ? (int) $_GET['user_id']
    : 0;

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($user_id <= 0 || $id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Permintaan tidak valid."
    ]);
    exit;
}

$stmt = $conn->prepare("
    SELECT file
    FROM materi
    WHERE id = ?
    AND user_id = ?
    LIMIT 1
");

$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();

$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data || empty($data['file'])) {
    echo json_encode([
        "success" => false,
        "message" => "File tidak ditemukan."
    ]);
    exit;
}

$file_path = "../materi/uploads/" . basename($data['file']);

if (!is_file($file_path)) {
    echo json_encode([
        "success" => false,
        "message" => "File tidak ditemukan di server."
    ]);
    exit;
}

$mime = mime_content_type($file_path);

header("Content-Type: " . $mime);
header("Content-Length: " . filesize($file_path));
header(
    'Content-Disposition: inline; filename="' .
    basename($data['file']) . '"'
);

readfile($file_path);

$stmt->close();
$conn->close();
exit;