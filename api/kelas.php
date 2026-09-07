<?php

header("Content-Type: application/json");

require "../config/koneksi.php";

$user_id = isset($_GET['user_id'])
    ? (int) $_GET['user_id']
    : 0;

if ($user_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "User ID tidak valid."
    ]);
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        k.id,
        k.nama_kelas,
        COUNT(s.id) AS jumlah_siswa
    FROM kelas k
    LEFT JOIN siswa s ON s.kelas_id = k.id
    WHERE k.user_id = ?
    GROUP BY k.id, k.nama_kelas
    ORDER BY k.id DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$kelas = [];

while ($row = $result->fetch_assoc()) {
    $kelas[] = [
        "id" => (int) $row["id"],
        "nama_kelas" => $row["nama_kelas"],
        "jumlah_siswa" => (int) $row["jumlah_siswa"]
    ];
}

echo json_encode([
    "success" => true,
    "data" => $kelas
]);

$stmt->close();
$conn->close();