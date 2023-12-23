<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Hash;
use Illuminate\Database\Seeder;
use App\Models\Employee;
use Schema;
use DB;

class EmployeeSeederCOP extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $data = array(
            639 => array('name' => 'Andi Wibowo', 'username' => '18000673', 'nik' => '18000673', 'email' => 'andi_wibowo@pinusmerahabadi.co.id'),
            640 => array('name' => 'Haryo Angka Wijaya', 'username' => '22000298', 'nik' => '22000298', 'email' => 'haryo_wijaya@pinusmerahabadi.co.id'),
            641 => array('name' => 'Pebri Christian', 'username' => '22000128', 'nik' => '22000128', 'email' => 'pebri_christian@pinusmerahabadi.co.id'),
            642 => array('name' => 'Sabrina Eka Sari', 'username' => '18000143', 'nik' => '18000143', 'email' => 'sabrina_ekasari@pinusmerahabadi.co.id'),
            643 => array('name' => 'Adella Fernanda Tanoko', 'username' => '19000367', 'nik' => '19000367', 'email' => 'adella_fernanda@pinusmerahabadi.co.id'),
            644 => array('name' => 'Gabriel Chyntia Michella', 'username' => '21000103', 'nik' => '21000103', 'email' => 'gabriel_chyntia@pinusmerahabadi.co.id'),
            645 => array('name' => 'Christine Agnes', 'username' => '22000407', 'nik' => '22000407', 'email' => 'christine_agnes@pinusmerahabadi.co.id'),
            646 => array('name' => 'Regina Mandastari Rimba', 'username' => '20000073', 'nik' => '20000073', 'email' => 'regina_mandastari@pinusmerahabadi.co.id'),
            647 => array('name' => 'Upiyanah', 'username' => '17000050', 'nik' => '17000050', 'email' => 'upiyanah@pinusmerahabadi.co.id'),
            648 => array('name' => 'Reza Pahlevi Adisaputra', 'username' => '21000325', 'nik' => '21000325', 'email' => 'reza_pahlevi@pinusmerahabadi.co.id'),
            649 => array('name' => 'Agus Pamuji', 'username' => '22000085', 'nik' => '22000085', 'email' => 'agus_pamuji@pinusmerahabadi.co.id'),
            650 => array('name' => 'Harman', 'username' => '19000057', 'nik' => '19000057', 'email' => 'harman@pinusmerahabadi.co.id'),
            651 => array('name' => 'Joko Chunshi', 'username' => '18000752', 'nik' => '18000752', 'email' => 'joko_chunshi@pinusmerahabadi.co.id'),
            652 => array('name' => 'Gabriel Titus Cristiawan', 'username' => '22000068', 'nik' => '22000068', 'email' => 'gabriel_cristiawan@pinusmerahabadi.co.id'),
            653 => array('name' => 'Duana Setiawan', 'username' => '18000885', 'nik' => '18000885', 'email' => 'duana_setiawan@pinusmerahabadi.co.id'),
            654 => array('name' => 'Leonardo Suhartono', 'username' => '21000105', 'nik' => '21000105', 'email' => 'leonardo_suhartono@pinusmerahabadi.co.id'),
            655 => array('name' => 'Ricaldo Christopher Santoso', 'username' => '22000588', 'nik' => '22000588', 'email' => 'ricaldo_christopher@pinusmerahabadi.co.id'),
            656 => array('name' => 'Vieca Octavia Asmarawati', 'username' => '18000295', 'nik' => '18000295', 'email' => 'vieca_octavia@pinusmerahabadi.co.id'),
            657 => array('name' => 'Hery Adi Wijaya', 'username' => '18000354', 'nik' => '18000354', 'email' => 'hery_wijaya@pinusmerahabadi.co.id'),
            658 => array('name' => 'Lidya Desy Natalia', 'username' => '19000192', 'nik' => '19000192', 'email' => 'lidya_natalia@pinusmerahabadi.co.id'),
            659 => array('name' => 'Indah Anglalami', 'username' => '19000264', 'nik' => '19000264', 'email' => 'indah_anglalami@pinusmerahabadi.co.id'),
            660 => array('name' => 'Nunung Rahmawati', 'username' => '18000628', 'nik' => '18000628', 'email' => 'nunung_rahmawati@pinusmerahabadi.co.id'),
            661 => array('name' => 'Antony Dwi Raharjo', 'username' => '19000307', 'nik' => '19000307', 'email' => 'antony_dwi@pinusmerahabadi.co.id'),
            662 => array('name' => 'Maureen S. Porayow', 'username' => '19000275', 'nik' => '19000275', 'email' => 'maureen_porayow@pinusmerahabadi.co.id'),
            663 => array('name' => 'Stephen', 'username' => '18000043', 'nik' => '18000043', 'email' => 'stephen@pinusmerahabadi.co.id'),
            664 => array('name' => 'Priscilia Alfrina Langi Pesik', 'username' => '21000192', 'nik' => '21000192', 'email' => 'priscilia_alfrina@pinusmerahabadi.co.id'),
            665 => array('name' => 'Halimah', 'username' => '18000142', 'nik' => '18000142', 'email' => 'halimah@pinusmerahabadi.co.id'),
            666 => array('name' => 'Angelina Siska', 'username' => '21000107', 'nik' => '21000107', 'email' => 'angelina_siska@pinusmerahabadi.co.id'),
            667 => array('name' => 'Nova Arisanti', 'username' => '18000046', 'nik' => '18000046', 'email' => 'nova_arisanti@pinusmerahabadi.co.id'),
            668 => array('name' => 'Antonius Agung Cahya Nugraha', 'username' => '20000170', 'nik' => '20000170', 'email' => 'antonius_agung@pinusmerahabadi.co.id'),
            669 => array('name' => 'Prita Eka Prastika', 'username' => '17000603', 'nik' => '17000603', 'email' => 'prita_prastika@pinusmerahabadi.co.id'),
            670 => array('name' => 'Rachmat Hidayat', 'username' => '20000215', 'nik' => '20000215', 'email' => 'rachmat_hidayat@pinusmerahabadi.co.id'),
            671 => array('name' => 'Armansyah', 'username' => '21000091', 'nik' => '21000091', 'email' => 'armansyah@pinusmerahabadi.co.id'),
            672 => array('name' => 'Eunike Intar Dharmamihardjo', 'username' => '19000309', 'nik' => '19000309', 'email' => 'eunike_dharmamihardjo@pinusmerahabadi.co.id'),
            673 => array('name' => 'Liza Christiani', 'username' => '18000527', 'nik' => '18000527', 'email' => 'liza_christiani@pinusmerahabadi.co.id'),
            674 => array('name' => 'Oni Tabroni', 'username' => '18000045', 'nik' => '18000045', 'email' => 'oni_tabroni@pinusmerahabadi.co.id'),
            675 => array('name' => 'Wijayanti', 'username' => '17000051', 'nik' => '17000051', 'email' => 'wijayanti@pinusmerahabadi.co.id'),
            676 => array('name' => 'Deshiko Arlyanto', 'username' => '18000630', 'nik' => '18000630', 'email' => 'desiko_arlyanto@pinusmerahabadi.co.id')
        );

        foreach ($data as $employee_data) {
            try {
                DB::beginTransaction();
                $count_employee = Employee::withTrashed()->count() + 1;
                $code = "EMP-" . str_repeat("0", 4 - strlen($count_employee)) . $count_employee;

                $checkEmployee = Employee::where('username', $employee_data["username"])->first();
                if ($checkEmployee) {
                    throw new \Exception("Username exists " . $employee_data['name'] . " | " . $employee_data['username'] . " | " . $employee_data['email']);
                }

                $checkEmployee = Employee::where('nik', $employee_data["nik"])->first();
                if ($checkEmployee) {
                    throw new \Exception("NIK exists " . $employee_data['name'] . " | " . $employee_data['username'] . " | " . $employee_data['email']);
                }

                if (!filter_var($employee_data['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new \Exception("Email format false " . $employee_data['name'] . " | " . $employee_data['username'] . " | " . $employee_data['email']);
                }

                $newEmployee                         = new Employee;
                $newEmployee->code                   = $code;
                $newEmployee->name                   = $employee_data["name"];
                $newEmployee->username               = $employee_data["username"];
                $newEmployee->nik                    = $employee_data["nik"] ?? $employee_data["username"];
                $newEmployee->email                  = $employee_data["email"];
                $newEmployee->password               = Hash::make("pma123");
                $newEmployee->save();
                DB::commit();
            } catch (\Exception $ex) {
                DB::rollback();
                print($ex->getMessage() . "|" . $ex->getLine() . "\n");
            }
        }

        print("import employee data COP finished");
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
