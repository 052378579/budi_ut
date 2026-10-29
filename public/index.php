<?php

use CodeIgniter\Boot;
use Config\Paths;

/*
 *---------------------------------------------------------------
 * CHECK PHP VERSION
 *---------------------------------------------------------------
 */

$minPhpVersion = '8.2'; // If you update this, don't forget to update `spark`.
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    $message = sprintf(
        'Your PHP version must be %s or higher to run CodeIgniter. Current version: %s',
        $minPhpVersion,
        PHP_VERSION,
    );

    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo $message;

    exit(1);
}

/*
 *---------------------------------------------------------------
 * SET THE ENVIRONMENT DYNAMICALLY
 *---------------------------------------------------------------
 * Deteksi host, IP, dan Port untuk menentukan CI_ENVIRONMENT secara otomatis
 */
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$port = $_SERVER['SERVER_PORT'] ?? '';

// Gunakan variabel superglobal $_SERVER, BUKAN define()
// JIKA diakses lewat port 2023 ATAU mengandung IP development
if (strpos($host, '10.147.17.40') !== false || strpos($host, '192.168.1.4') !== false || strpos($host, '2023') !== false) {
    $_SERVER['CI_ENVIRONMENT'] = 'development';
} 
// JIKA diakses lewat domain utama
elseif (strpos($host, 'ut.budi.biz.id') !== false) {
    $_SERVER['CI_ENVIRONMENT'] = 'production';
} 
// Fallback aman jika dijalankan via CLI / Spark
else {
    $_SERVER['CI_ENVIRONMENT'] = 'development';
}

/*
 *---------------------------------------------------------------
 * SET THE CURRENT DIRECTORY
 *---------------------------------------------------------------
 */

// Path to the front controller (this file)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Ensure the current directory is pointing to the front controller's directory
if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

/*
 *---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION
 *---------------------------------------------------------------
 * This process sets up the path constants, loads and registers
 * our autoloader, along with Composer's, loads our constants
 * and fires up an environment-specific bootstrapping.
 */

// LOAD OUR PATHS CONFIG FILE
// This is the line that might need to be changed, depending on your folder structure.
require FCPATH . '../app/Config/Paths.php';
// ^^^ Change this line if you move your application folder

$paths = new Paths();

// LOAD THE FRAMEWORK BOOTSTRAP FILE
require $paths->systemDirectory . '/Boot.php';

exit(Boot::bootWeb($paths));