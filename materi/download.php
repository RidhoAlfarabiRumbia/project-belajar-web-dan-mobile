<?php

require "../config/auth.php";
require "../config/koneksi.php";

$user_id = (int) $_SESSION['user_id'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    exit("Permintaan tidak valid.");
}

$q = $conn->query("
    SELECT file
    FROM materi
    WHERE id=$id
    AND user_id=$user_id
    LIMIT 1
");

$data = $q ? $q->fetch_assoc() : null;

if (!$data || empty($data['file'])) {
    http_response_code(404);
    exit("File tidak ditemukan.");
}

$file_path = __DIR__ . "/uploads/" . basename($data['file']);

if (!is_file($file_path)) {
    http_response_code(404);
    exit("File tidak ditemukan.");
}

$mime = mime_content_type($file_path);

header("Content-Type: " . $mime);
header("Content-Length: " . filesize($file_path));
header(
    'Content-Disposition: inline; filename="' .
    basename($data['file']) .
    '"'
);

readfile($file_path);
exit;