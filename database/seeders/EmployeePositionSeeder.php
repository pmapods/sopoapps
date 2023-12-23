<?php

namespace Database\Seeders;
use Faker\Factory as Faker;
use Hash;

use Illuminate\Database\Seeder;
use App\Models\EmployeePosition;

use Schema;
use DB;

class EmployeePositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // tambah satu user super admin
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        EmployeePosition::truncate();

        $data = array(
            0 => array('name' => 'Superadmin'),
            1 => array('name' => 'Purchasing Manager'),
            2 => array('name' => 'Purchasing Supervisor'),
            3 => array('name' => 'Purchasing Staff'),
            4 => array('name' => 'Finance Supervisor'),
            5 => array('name' => 'Claim Supervisor'),
            6 => array('name' => 'Tax Supervisor'),
            7 => array('name' => 'Accounting Supervisor'),
            8 => array('name' => 'Accounting Manager'),
            9 => array('name' => 'Tax Plan Manager'),
            10 => array('name' => 'National Finance Accounting Manager'),
            11 => array('name' => 'Claim Manager'),
            12 => array('name' => 'Accounting Assistant Manager'),
            13 => array('name' => 'Finance Manager'),
            14 => array('name' => 'Finance Assistant Manager'),
            15 => array('name' => 'Tax Plan Assistant Manager'),
            16 => array('name' => 'Compensation & Benefit Supervisor'),
            17 => array('name' => 'General Affair Staff'),
            18 => array('name' => 'Industrial Relation Manager'),
            19 => array('name' => 'People & Organization Development Manager'),
            20 => array('name' => 'Head Of Human Capital'),
            21 => array('name' => 'Legal & Compliance Manager'),
            22 => array('name' => 'General Affair Supervisor'),
            23 => array('name' => 'Talent Acquisition Manager'),
            24 => array('name' => 'Talent Management Specialist'),
            25 => array('name' => 'System Application Support Staff'),
            26 => array('name' => 'Network Staff'),
            27 => array('name' => 'System Networking Supervisor'),
            28 => array('name' => 'Master Data Maintenance Supervisor'),
            29 => array('name' => 'Master Data Maintenance Staff'),
            30 => array('name' => 'Operation Support Manager'),
            31 => array('name' => 'System Application Support Supervisor'),
            32 => array('name' => 'It Project Development Specialist'),
            33 => array('name' => 'Operational Supervisor'),
            34 => array('name' => 'Area Operational Supervisor'),
            35 => array('name' => 'Receptionist'),
            36 => array('name' => 'National Business Support'),
            37 => array('name' => 'Country Manager'),
            38 => array('name' => 'Internal Audit Manager'),
            39 => array('name' => 'Personal Assisstant'),
            40 => array('name' => 'National Operation Manager'),
            41 => array('name' => 'Operational Manager'),
            42 => array('name' => 'Internal Audit Supervisor'),
            43 => array('name' => 'Regional Business Support'),
            44 => array('name' => 'Sales Support Supervisor'),
            45 => array('name' => 'Trade Support Supervisor'),
            46 => array('name' => 'Business Manager'),
            47 => array('name' => 'Key Account Manager'),
            48 => array('name' => 'Regional Business Manager'),
            49 => array('name' => 'National Trade Support Manager'),
            50 => array('name' => 'Business Development Manager'),
            51 => array('name' => 'Merchandising Manager'),
            52 => array('name' => 'Sales Manager'),
            53 => array('name' => 'Sales Support Manager'),
            54 => array('name' => 'Head Of Sales & Distribution'),
            55 => array('name' => 'Area Business Manager'),
            56 => array('name' => 'Customer Relation Manager'),
            57 => array('name' => 'Key Account Executive'),
            58 => array('name' => 'Demand Planning Supervisor'),
            59 => array('name' => 'Demand Planning Manager'),
            60 => array('name' => 'Logistic Manager'),
            61 => array('name' => 'Deputy Head Operation')
        );
        foreach($data as $key => $employee_position_data){
            try{
                $check_is_employee_position_exist = EmployeePosition::where('name', $employee_position_data['name'])->first();
                
                if($check_is_employee_position_exist){
                    throw new \Exception("Jabatan ".$employee_position_data['name']." sudah ada (".$key.")");
                }
                $newEmployeePosition                         = new EmployeePosition;
                $newEmployeePosition->name                   = $employee_position_data["name"];
                $newEmployeePosition->save();
            }catch(\Exception $ex){
                print($ex->getMessage()."\n");
            }
        }
        
        print("import employee position data finished");
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
