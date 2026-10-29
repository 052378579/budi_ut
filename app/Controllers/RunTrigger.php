<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class RunTrigger extends Controller
{
    public function index()
    {
        $db = \Config\Database::connect();
        
        // Drop existing
        $db->query("DROP TRIGGER IF EXISTS `before_insert_users_mhs`");

        // Create trigger
        $sql = "
        CREATE TRIGGER `before_insert_users_mhs` BEFORE INSERT ON `users_mhs` 
        FOR EACH ROW 
        BEGIN
            IF NEW.no_hp NOT REGEXP '^[0-9]+$' OR LENGTH(NEW.no_hp) < 10 OR LENGTH(NEW.no_hp) > 13 THEN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Gagal INSERT: Nomor HP harus berisi 10-13 digit angka saja tanpa karakter lain.';
            END IF;

            IF NEW.no_hp NOT REGEXP '^(08|628)(1[1-9]|2[1-3]|3[1-38]|5[1-9]|7[7-8]|8[1-9]|9[5-9])' THEN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Gagal INSERT: Prefix nomor HP tidak dikenali sebagai provider Indonesia.';
            END IF;

            IF NEW.no_hp REGEXP '([0-9])\\\\1{4,}' THEN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Gagal INSERT: Terdeteksi pola angka kembar berulang (Indikasi Spam).';
            END IF;

            IF NEW.no_hp REGEXP '([0-9]{2,3})\\\\1{2,}' THEN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Gagal INSERT: Terdeteksi pola blok angka berulang (Indikasi Spam).';
            END IF;

            IF NEW.no_hp REGEXP '(012345|123456|234567|345678|456789|567890|098765|987654|876543|765432|654321|543210)' THEN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Gagal INSERT: Terdeteksi pola angka berurutan (Indikasi Dummy/Spam).';
            END IF;
        END;
        ";

        if ($db->query($sql)) {
            echo "Trigger created successfully!";
        } else {
            echo "Error creating trigger.";
        }
    }
}
