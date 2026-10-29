<?php
require __DIR__ . '/../app/Config/Paths.php';
$paths = new \Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . '/bootstrap.php';

$db = \Config\Database::connect();

$sql = "
SELECT id_user, nama_user, no_hp 
FROM users_mhs 
WHERE 
    no_hp NOT REGEXP '^08(1[1-9]|2[1-3]|3[1-3]|38|5[1-35-9]|7[7-8]|8[1-9]|9[5-9])'
    OR no_hp REGEXP '([0-9])\\\\1{3,}'
    OR no_hp REGEXP '([0-9]{2,3})\\\\1{2,}'
    OR no_hp REGEXP '(012345|123456|234567|345678|456789|567890|098765|987654|876543|765432|654321|543210)'
";

$query = $db->query($sql);
$results = $query->getResultArray();

echo "Total data yang akan terhapus: " . count($results) . " pengguna.\n\n";
echo "Daftar pengguna (Spam/Dummy):\n";
foreach($results as $row) {
    echo "- ID: {$row['id_user']} | Nama: {$row['nama_user']} | No HP: {$row['no_hp']}\n";
}
