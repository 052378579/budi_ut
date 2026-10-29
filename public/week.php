<?php
date_default_timezone_set('Asia/Jakarta');

$hariInggris = date('l');
$terjemahanHari = [
    'Monday'    => 'SENIN',
    'Tuesday'   => 'SELASA',
    'Wednesday' => 'RABU',
    'Thursday'  => 'KAMIS',
    'Friday'    => 'JUMAT',
    'Saturday'  => 'SABTU',
    'Sunday'    => 'MINGGU'
];

$hariIndonesia = $terjemahanHari[$hariInggris];
$tanggal = date('d/m/Y');
$weekNumber = date('W');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender Minggu</title>
    <!-- Memanggil file CSS eksternal -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="calendar-card">
        <!-- Efek cincin besi di bagian atas kalender -->
        <div class="rings">
            <div class="ring"></div>
            <div class="ring"></div>
        </div>
        
        <!-- Bagian atas berwarna pink -->
        <div class="calendar-top"></div>
        
        <!-- Bagian bawah berwarna putih dengan teks -->
        <div class="calendar-bottom">
            <h2>CURRENT WEEK<br>NUMBER</h2>
            <div class="week-number"><?= $weekNumber ?></div>
            
            <div class="calendar-footer">
                <!-- Simbol hati (♡) diikuti Hari dan Tanggal -->
                &#9825; <?= $hariIndonesia ?>, <?= $tanggal ?>
            </div>
        </div>
    </div>
</body>
</html>