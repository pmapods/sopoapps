<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailAdditional;

use DB;

class MailingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        EmailAdditional::truncate();
        $categories = array(
            array('category'=>'armada', 'type'=>'pengadaan'),
            array('category'=>'armada', 'type'=>'perpanjangan'),
            array('category'=>'armada', 'type'=>'replace'),
            array('category'=>'armada', 'type'=>'renewal'),
            array('category'=>'armada', 'type'=>'end_kontrak'),
            array('category'=>'armada', 'type'=>'mutasi'),
            array('category'=>'barang_jasa', 'type'=>'pengadaan'),
            array('category'=>'barang_jasa', 'type'=>'replace_existing'),
            array('category'=>'barang_jasa', 'type'=>'repeat_order'),
            array('category'=>'security', 'type'=>'pengadaan'),
            array('category'=>'security', 'type'=>'perpanjangan'),
            array('category'=>'security', 'type'=>'replace'),
            array('category'=>'security', 'type'=>'end_kontrak'),
            array('category'=>'security', 'type'=>'pengadaan_lembur'),
            // array('category'=>'cit', 'type'=>'perpanjangan'),
            // array('category'=>'pest_control', 'type'=>'perpanjangan'),
            // array('category'=>'merchandiser', 'type'=>'perpanjangan'),
            array('category'=>'purchasing', 'type'=>'east'),
            array('category'=>'purchasing', 'type'=>'west'),
            array('category'=>'purchasing', 'type'=>'national'),
            array('category'=>'ga', 'type'=>'armada'),
            array('category'=>'ga', 'type'=>'barang_jasa'),
            array('category'=>'ga', 'type'=>'security'),
            array('category'=>'ga', 'type'=>'cit'),
            array('category'=>'ga', 'type'=>'pest_control'),
            array('category'=>'ga', 'type'=>'merchandiser'),
        );
        $seeder = array(
            0 => array('category' => 'armada', 'type' => 'pengadaan', 'emails' => 'pmaho_ga4@pinusmerahabadi.co.id, dwita_riyanti@pinusmerahabadi.co.id, suhardi_laiya@pinusmerahabadi.co.id, james_arthur@pinusmerahabadi.co.id, wiwik_wijaya@pinusmerahabadi.co.id, susilawati@pinusmerahabadi.co.id, pmaho_ga5@pinusmerahabadi.co.id, pmaho_ap10@pinusmerahabadi.co.id, pmaho_ap13@pinusmerahabadi.co.id, pmaho_ap14@pinusmerahabadi.co.id, pmaho_ap7@pinusmerahabadi.co.id, okiyandi_wiyantara@pinusmerahabadi.co.id, muhammad_bayu@pinusmerahabadi.co.id, dina_chotimah@pinusmerahabadi.co.id, ervyanty_oktaviani@pinusmerahabadi.co.id, iqbal_alkahfi@pinusmerahabadi.co.id, Grace_avianny@pinusmerahabadi.co.id, ruth_yulita@pinusmerahabadi.co.id, hafid_fauzi@pinusmerahabadi.co.id'),
            1 => array('category' => 'armada', 'type' => 'perpanjangan', 'emails' => 'pmaho_ga4@pinusmerahabadi.co.id, dwita_riyanti@pinusmerahabadi.co.id, suhardi_laiya@pinusmerahabadi.co.id, james_arthur@pinusmerahabadi.co.id, wiwik_wijaya@pinusmerahabadi.co.id, susilawati@pinusmerahabadi.co.id, pmaho_ga5@pinusmerahabadi.co.id, pmaho_ap10@pinusmerahabadi.co.id, pmaho_ap13@pinusmerahabadi.co.id, pmaho_ap14@pinusmerahabadi.co.id, pmaho_ap7@pinusmerahabadi.co.id, okiyandi_wiyantara@pinusmerahabadi.co.id, muhammad_bayu@pinusmerahabadi.co.id, dina_chotimah@pinusmerahabadi.co.id, ervyanty_oktaviani@pinusmerahabadi.co.id, iqbal_alkahfi@pinusmerahabadi.co.id, Grace_avianny@pinusmerahabadi.co.id, ruth_yulita@pinusmerahabadi.co.id, hafid_fauzi@pinusmerahabadi.co.id'),
            2 => array('category' => 'armada', 'type' => 'replace', 'emails' => 'pmaho_ga4@pinusmerahabadi.co.id, dwita_riyanti@pinusmerahabadi.co.id, suhardi_laiya@pinusmerahabadi.co.id, james_arthur@pinusmerahabadi.co.id, wiwik_wijaya@pinusmerahabadi.co.id, susilawati@pinusmerahabadi.co.id, pmaho_ga5@pinusmerahabadi.co.id, pmaho_ap10@pinusmerahabadi.co.id, pmaho_ap13@pinusmerahabadi.co.id, pmaho_ap14@pinusmerahabadi.co.id, pmaho_ap7@pinusmerahabadi.co.id, okiyandi_wiyantara@pinusmerahabadi.co.id, muhammad_bayu@pinusmerahabadi.co.id, dina_chotimah@pinusmerahabadi.co.id, ervyanty_oktaviani@pinusmerahabadi.co.id, iqbal_alkahfi@pinusmerahabadi.co.id, Grace_avianny@pinusmerahabadi.co.id, ruth_yulita@pinusmerahabadi.co.id, hafid_fauzi@pinusmerahabadi.co.id'),
            3 => array('category' => 'armada', 'type' => 'renewal', 'emails' => 'pmaho_ga4@pinusmerahabadi.co.id, dwita_riyanti@pinusmerahabadi.co.id, suhardi_laiya@pinusmerahabadi.co.id, james_arthur@pinusmerahabadi.co.id, wiwik_wijaya@pinusmerahabadi.co.id, susilawati@pinusmerahabadi.co.id, pmaho_ga5@pinusmerahabadi.co.id, pmaho_ap10@pinusmerahabadi.co.id, pmaho_ap13@pinusmerahabadi.co.id, pmaho_ap14@pinusmerahabadi.co.id, pmaho_ap7@pinusmerahabadi.co.id, okiyandi_wiyantara@pinusmerahabadi.co.id, muhammad_bayu@pinusmerahabadi.co.id, dina_chotimah@pinusmerahabadi.co.id, ervyanty_oktaviani@pinusmerahabadi.co.id, iqbal_alkahfi@pinusmerahabadi.co.id, Grace_avianny@pinusmerahabadi.co.id, ruth_yulita@pinusmerahabadi.co.id, hafid_fauzi@pinusmerahabadi.co.id'),
            4 => array('category' => 'armada', 'type' => 'end_kontrak', 'emails' => 'pmaho_ga4@pinusmerahabadi.co.id, dwita_riyanti@pinusmerahabadi.co.id, suhardi_laiya@pinusmerahabadi.co.id, james_arthur@pinusmerahabadi.co.id, wiwik_wijaya@pinusmerahabadi.co.id, susilawati@pinusmerahabadi.co.id, pmaho_ga5@pinusmerahabadi.co.id, pmaho_ap10@pinusmerahabadi.co.id, pmaho_ap13@pinusmerahabadi.co.id, pmaho_ap14@pinusmerahabadi.co.id, pmaho_ap7@pinusmerahabadi.co.id, okiyandi_wiyantara@pinusmerahabadi.co.id, muhammad_bayu@pinusmerahabadi.co.id, dina_chotimah@pinusmerahabadi.co.id, ervyanty_oktaviani@pinusmerahabadi.co.id, iqbal_alkahfi@pinusmerahabadi.co.id, Grace_avianny@pinusmerahabadi.co.id, ruth_yulita@pinusmerahabadi.co.id, hafid_fauzi@pinusmerahabadi.co.id'),
            5 => array('category' => 'armada', 'type' => 'mutasi', 'emails' => 'pmaho_ga4@pinusmerahabadi.co.id, dwita_riyanti@pinusmerahabadi.co.id, suhardi_laiya@pinusmerahabadi.co.id, james_arthur@pinusmerahabadi.co.id, wiwik_wijaya@pinusmerahabadi.co.id, susilawati@pinusmerahabadi.co.id, pmaho_ga5@pinusmerahabadi.co.id, pmaho_ap10@pinusmerahabadi.co.id, pmaho_ap13@pinusmerahabadi.co.id, pmaho_ap14@pinusmerahabadi.co.id, pmaho_ap7@pinusmerahabadi.co.id, okiyandi_wiyantara@pinusmerahabadi.co.id, muhammad_bayu@pinusmerahabadi.co.id, dina_chotimah@pinusmerahabadi.co.id, ervyanty_oktaviani@pinusmerahabadi.co.id, iqbal_alkahfi@pinusmerahabadi.co.id, Grace_avianny@pinusmerahabadi.co.id, ruth_yulita@pinusmerahabadi.co.id, hafid_fauzi@pinusmerahabadi.co.id'),
            6 => array('category' => 'barang_jasa', 'type' => 'pengadaan', 'emails' => 'pmaho_ga2@pinusmerahabadi.co.id, dwita_riyanti@pinusmerahabadi.co.id, suhardi_laiya@pinusmerahabadi.co.id, james_arthur@pinusmerahabadi.co.id, wiwik_wijaya@pinusmerahabadi.co.id, susilawati@pinusmerahabadi.co.id, hary_rahmadi@pinusmerahabadi.co.id, pmaho_asset1@pinusmerahabadi.co.id, ervyanty_oktaviani@pinusmerahabadi.co.id, fauzan@pinusmerahabadi.co.id, muhammad_bayu@pinusmerahabadi.co.id, dina_chotimah@pinusmerahabadi.co.id, Grace_avianny@pinusmerahabadi.co.id, Pmaho_console1@pinusmerahabadi.co.id, pmaho_console2@pinusmerahabadi.co.id, pmaho_console3@pinusmerahabadi.co.id, pmaho_console4@pinusmerahabadi.co.id, pmaho_console5@pinusmerahabadi.co.id, ruth_yulita@pinusmerahabadi.co.id, hafid_fauzi@pinusmerahabadi.co.id'),
            7 => array('category' => 'barang_jasa', 'type' => 'replace_existing', 'emails' => 'pmaho_ga2@pinusmerahabadi.co.id, dwita_riyanti@pinusmerahabadi.co.id, suhardi_laiya@pinusmerahabadi.co.id, james_arthur@pinusmerahabadi.co.id, wiwik_wijaya@pinusmerahabadi.co.id, susilawati@pinusmerahabadi.co.id, hary_rahmadi@pinusmerahabadi.co.id, pmaho_asset1@pinusmerahabadi.co.id, ervyanty_oktaviani@pinusmerahabadi.co.id, fauzan@pinusmerahabadi.co.id, muhammad_bayu@pinusmerahabadi.co.id, dina_chotimah@pinusmerahabadi.co.id, Grace_avianny@pinusmerahabadi.co.id, Pmaho_console1@pinusmerahabadi.co.id, pmaho_console2@pinusmerahabadi.co.id, pmaho_console3@pinusmerahabadi.co.id, pmaho_console4@pinusmerahabadi.co.id, pmaho_console5@pinusmerahabadi.co.id, ruth_yulita@pinusmerahabadi.co.id, hafid_fauzi@pinusmerahabadi.co.id'),
            8 => array('category' => 'barang_jasa', 'type' => 'repeat_order', 'emails' => 'pmaho_ga2@pinusmerahabadi.co.id, dwita_riyanti@pinusmerahabadi.co.id, suhardi_laiya@pinusmerahabadi.co.id, james_arthur@pinusmerahabadi.co.id, wiwik_wijaya@pinusmerahabadi.co.id, susilawati@pinusmerahabadi.co.id, hary_rahmadi@pinusmerahabadi.co.id, pmaho_asset1@pinusmerahabadi.co.id, ervyanty_oktaviani@pinusmerahabadi.co.id, fauzan@pinusmerahabadi.co.id, muhammad_bayu@pinusmerahabadi.co.id, dina_chotimah@pinusmerahabadi.co.id, Grace_avianny@pinusmerahabadi.co.id, Pmaho_console1@pinusmerahabadi.co.id, pmaho_console2@pinusmerahabadi.co.id, pmaho_console3@pinusmerahabadi.co.id, pmaho_console4@pinusmerahabadi.co.id, pmaho_console5@pinusmerahabadi.co.id, ruth_yulita@pinusmerahabadi.co.id, hafid_fauzi@pinusmerahabadi.co.id'),
            9 => array('category' => 'security', 'type' => 'pengadaan', 'emails' => 'pmaho_ga4@pinusmerahabadi.co.id, dwita_riyanti@pinusmerahabadi.co.id, suhardi_laiya@pinusmerahabadi.co.id, james_arthur@pinusmerahabadi.co.id, wiwik_wijaya@pinusmerahabadi.co.id, susilawati@pinusmerahabadi.co.id, pmaho_ga5@pinusmerahabadi.co.id, pmaho_ap10@pinusmerahabadi.co.id, pmaho_ap13@pinusmerahabadi.co.id, pmaho_ap14@pinusmerahabadi.co.id, pmaho_ap7@pinusmerahabadi.co.id, okiyandi_wiyantara@pinusmerahabadi.co.id, muhammad_bayu@pinusmerahabadi.co.id, dina_chotimah@pinusmerahabadi.co.id, ervyanty_oktaviani@pinusmerahabadi.co.id, iqbal_alkahfi@pinusmerahabadi.co.id, Grace_avianny@pinusmerahabadi.co.id, ruth_yulita@pinusmerahabadi.co.id, hafid_fauzi@pinusmerahabadi.co.id'),
            10 => array('category' => 'security', 'type' => 'perpanjangan', 'emails' => 'pmaho_ga4@pinusmerahabadi.co.id, dwita_riyanti@pinusmerahabadi.co.id, suhardi_laiya@pinusmerahabadi.co.id, james_arthur@pinusmerahabadi.co.id, wiwik_wijaya@pinusmerahabadi.co.id, susilawati@pinusmerahabadi.co.id, pmaho_ga5@pinusmerahabadi.co.id, pmaho_ap10@pinusmerahabadi.co.id, pmaho_ap13@pinusmerahabadi.co.id, pmaho_ap14@pinusmerahabadi.co.id, pmaho_ap7@pinusmerahabadi.co.id, okiyandi_wiyantara@pinusmerahabadi.co.id, muhammad_bayu@pinusmerahabadi.co.id, dina_chotimah@pinusmerahabadi.co.id, ervyanty_oktaviani@pinusmerahabadi.co.id, iqbal_alkahfi@pinusmerahabadi.co.id, Grace_avianny@pinusmerahabadi.co.id, ruth_yulita@pinusmerahabadi.co.id, hafid_fauzi@pinusmerahabadi.co.id'),
            11 => array('category' => 'security', 'type' => 'replace', 'emails' => 'pmaho_ga4@pinusmerahabadi.co.id, dwita_riyanti@pinusmerahabadi.co.id, suhardi_laiya@pinusmerahabadi.co.id, james_arthur@pinusmerahabadi.co.id, wiwik_wijaya@pinusmerahabadi.co.id, susilawati@pinusmerahabadi.co.id, pmaho_ga5@pinusmerahabadi.co.id, pmaho_ap10@pinusmerahabadi.co.id, pmaho_ap13@pinusmerahabadi.co.id, pmaho_ap14@pinusmerahabadi.co.id, pmaho_ap7@pinusmerahabadi.co.id, okiyandi_wiyantara@pinusmerahabadi.co.id, muhammad_bayu@pinusmerahabadi.co.id, dina_chotimah@pinusmerahabadi.co.id, ervyanty_oktaviani@pinusmerahabadi.co.id, iqbal_alkahfi@pinusmerahabadi.co.id, Grace_avianny@pinusmerahabadi.co.id, ruth_yulita@pinusmerahabadi.co.id, hafid_fauzi@pinusmerahabadi.co.id'),
            12 => array('category' => 'security', 'type' => 'end_kontrak', 'emails' => 'pmaho_ga4@pinusmerahabadi.co.id, dwita_riyanti@pinusmerahabadi.co.id, suhardi_laiya@pinusmerahabadi.co.id, james_arthur@pinusmerahabadi.co.id, wiwik_wijaya@pinusmerahabadi.co.id, susilawati@pinusmerahabadi.co.id, pmaho_ga5@pinusmerahabadi.co.id, pmaho_ap10@pinusmerahabadi.co.id, pmaho_ap13@pinusmerahabadi.co.id, pmaho_ap14@pinusmerahabadi.co.id, pmaho_ap7@pinusmerahabadi.co.id, okiyandi_wiyantara@pinusmerahabadi.co.id, muhammad_bayu@pinusmerahabadi.co.id, dina_chotimah@pinusmerahabadi.co.id, ervyanty_oktaviani@pinusmerahabadi.co.id, iqbal_alkahfi@pinusmerahabadi.co.id, Grace_avianny@pinusmerahabadi.co.id, ruth_yulita@pinusmerahabadi.co.id, hafid_fauzi@pinusmerahabadi.co.id'),
            13 => array('category' => 'security', 'type' => 'pengadaan_lembur', 'emails' => 'pmaho_ga4@pinusmerahabadi.co.id, dwita_riyanti@pinusmerahabadi.co.id, suhardi_laiya@pinusmerahabadi.co.id, james_arthur@pinusmerahabadi.co.id, wiwik_wijaya@pinusmerahabadi.co.id, susilawati@pinusmerahabadi.co.id, pmaho_ga5@pinusmerahabadi.co.id, pmaho_ap10@pinusmerahabadi.co.id, pmaho_ap13@pinusmerahabadi.co.id, pmaho_ap14@pinusmerahabadi.co.id, pmaho_ap7@pinusmerahabadi.co.id, okiyandi_wiyantara@pinusmerahabadi.co.id, muhammad_bayu@pinusmerahabadi.co.id, dina_chotimah@pinusmerahabadi.co.id, ervyanty_oktaviani@pinusmerahabadi.co.id, iqbal_alkahfi@pinusmerahabadi.co.id, Grace_avianny@pinusmerahabadi.co.id, ruth_yulita@pinusmerahabadi.co.id, hafid_fauzi@pinusmerahabadi.co.id'),
            14 => array('category' => 'cit', 'type' => 'perpanjangan', 'emails' => 'pmaho_ga4@pinusmerahabadi.co.id, dwita_riyanti@pinusmerahabadi.co.id, suhardi_laiya@pinusmerahabadi.co.id, james_arthur@pinusmerahabadi.co.id, wiwik_wijaya@pinusmerahabadi.co.id, susilawati@pinusmerahabadi.co.id, pmaho_ga5@pinusmerahabadi.co.id, pmaho_ap10@pinusmerahabadi.co.id, pmaho_ap13@pinusmerahabadi.co.id, pmaho_ap14@pinusmerahabadi.co.id, pmaho_ap7@pinusmerahabadi.co.id, okiyandi_wiyantara@pinusmerahabadi.co.id, muhammad_bayu@pinusmerahabadi.co.id, dina_chotimah@pinusmerahabadi.co.id, ervyanty_oktaviani@pinusmerahabadi.co.id, iqbal_alkahfi@pinusmerahabadi.co.id, Grace_avianny@pinusmerahabadi.co.id, ruth_yulita@pinusmerahabadi.co.id, hafid_fauzi@pinusmerahabadi.co.id'),
            15 => array('category' => 'pest_control', 'type' => 'perpanjangan', 'emails' => 'pmaho_ga4@pinusmerahabadi.co.id, dwita_riyanti@pinusmerahabadi.co.id, suhardi_laiya@pinusmerahabadi.co.id, james_arthur@pinusmerahabadi.co.id, wiwik_wijaya@pinusmerahabadi.co.id, susilawati@pinusmerahabadi.co.id, pmaho_ga5@pinusmerahabadi.co.id, pmaho_ap10@pinusmerahabadi.co.id, pmaho_ap13@pinusmerahabadi.co.id, pmaho_ap14@pinusmerahabadi.co.id, pmaho_ap7@pinusmerahabadi.co.id, okiyandi_wiyantara@pinusmerahabadi.co.id, muhammad_bayu@pinusmerahabadi.co.id, dina_chotimah@pinusmerahabadi.co.id, ervyanty_oktaviani@pinusmerahabadi.co.id, iqbal_alkahfi@pinusmerahabadi.co.id, Grace_avianny@pinusmerahabadi.co.id, ruth_yulita@pinusmerahabadi.co.id, hafid_fauzi@pinusmerahabadi.co.id'),
            16 => array('category' => 'merchandiser', 'type' => 'perpanjangan', 'emails' => 'pmaho_ga2@pinusmerahabadi.co.id, dwita_riyanti@pinusmerahabadi.co.id, suhardi_laiya@pinusmerahabadi.co.id, james_arthur@pinusmerahabadi.co.id, wiwik_wijaya@pinusmerahabadi.co.id, susilawati@pinusmerahabadi.co.id, hary_rahmadi@pinusmerahabadi.co.id, pmaho_asset1@pinusmerahabadi.co.id, ervyanty_oktaviani@pinusmerahabadi.co.id, fauzan@pinusmerahabadi.co.id, muhammad_bayu@pinusmerahabadi.co.id, dina_chotimah@pinusmerahabadi.co.id, Grace_avianny@pinusmerahabadi.co.id, Pmaho_console1@pinusmerahabadi.co.id, pmaho_console2@pinusmerahabadi.co.id, pmaho_console3@pinusmerahabadi.co.id, pmaho_console4@pinusmerahabadi.co.id, pmaho_console5@pinusmerahabadi.co.id, ruth_yulita@pinusmerahabadi.co.id, hafid_fauzi@pinusmerahabadi.co.id'),
            17 => array('category' => 'purchasing', 'type' => 'east', 'emails' => 'angga_ginanjar@pinusmerahabadi.co.id,pmaho_purchasing2@pinusmerahabadi.co.id'),
            18 => array('category' => 'purchasing', 'type' => 'west', 'emails' => 'anugrah_purnama@pinusmerahabadi.co.id,pmaho_purchasing1@pinusmerahabadi.co.id'),
            19 => array('category' => 'purchasing', 'type' => 'national', 'emails' => 'pmaho_purchasing3@pinusmerahabadi.co.id,tirani_susanti@pinusmerahabadi.co.id'),
            20 => array('category' => 'ga', 'type' => 'armada', 'emails' => 'pmaho_ga4@pinusmerahabadi.co.id, dwita_riyanti@pinusmerahabadi.co.id, suhardi_laiya@pinusmerahabadi.co.id'),
            21 => array('category' => 'ga', 'type' => 'barang_jasa', 'emails' => 'pmaho_ga2@pinusmerahabadi.co.id, pmaho_ga5@pinusmerahabadi.co.id, pmaho_receptionist@pinusmerahabadi.co.id, dwita_riyanti@pinusmerahabadi.co.id, suhardi_laiya@pinusmerahabadi.co.id'),
            22 => array('category' => 'ga', 'type' => 'security', 'emails' => 'pmaho_ga4@pinusmerahabadi.co.id, dwita_riyanti@pinusmerahabadi.co.id, suhardi_laiya@pinusmerahabadi.co.id'),
            23 => array('category' => 'ga', 'type' => 'cit', 'emails' => 'pmaho_ga4@pinusmerahabadi.co.id, dwita_riyanti@pinusmerahabadi.co.id, suhardi_laiya@pinusmerahabadi.co.id'),
            24 => array('category' => 'ga', 'type' => 'pest_control', 'emails' => 'pmaho_ga4@pinusmerahabadi.co.id, dwita_riyanti@pinusmerahabadi.co.id, suhardi_laiya@pinusmerahabadi.co.id'),
            25 => array('category' => 'ga', 'type' => 'merchandiser', 'emails' => 'pmaho_ga2@pinusmerahabadi.co.id, pmaho_ga5@pinusmerahabadi.co.id, dwita_riyanti@pinusmerahabadi.co.id, suhardi_laiya@pinusmerahabadi.co.id'),
        );
        foreach($categories as $category){
            $additional = new EmailAdditional;
            $additional->category = $category['category'];
            $additional->type = $category['type'];
            $additional->save();
        }
        foreach($seeder as $data){
            $email_additional = EmailAdditional::where('category', $data['category'])
                ->where('type', $data['type'])->first();
            if($email_additional){
                $emails = explode(",",$data['emails']);
                $emails = array_map('trim', $emails);
                $email_additional->emails = json_encode($emails);
                $email_additional->save();
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
