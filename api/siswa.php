<?php

header("Content-Type: application/json");

require "../config/koneksi.php";

$kelas_id = isset($_GET['kelas_id'])
    ? (int) $_GET['kelas_id']
    : 0;

$user_id = isset($_GET['user_id'])
    ? (int) $_GET['user_id']
    : 0;

if ($kelas_id <= 0 || $user_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Kelas atau user tidak valid."
    ]);
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        s.id,
        s.nama_siswa
    FROM siswa s
    INNER JOIN kelas k ON s.kelas_id = k.id
    WHERE s.kelas_id = ?
    AND k.user_id = ?
    ORDER BY s.nama_siswa ASC
");

$stmt->bind_param("ii", $kelas_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();

$siswa = [];

while ($row = $result->fetch_assoc()) {
    $siswa[] = [
        "id" => (int) $row["id"],
        "nama_siswa" => $row["nama_siswa"]
    ];
}

echo json_encode([
    "success" => true,
    "data" => $siswa
]);

$stmt->close();
$conn->close();