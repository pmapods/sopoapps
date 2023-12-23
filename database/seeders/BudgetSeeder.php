<?php

namespace Database\Seeders;

use Faker\Factory as Faker;

use Illuminate\Database\Seeder;
use App\Models\BudgetPricingCategory;
use App\Models\BudgetPricing;
use App\Models\BudgetBrand;
use App\Models\BudgetType;
use DB;

class BudgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        BudgetPricingCategory::truncate();
        BudgetPricing::truncate();
        BudgetType::truncate();
        BudgetBrand::truncate();
        DB::beginTransaction();
        
        $category_list = ["Office Equipment","Fixture and Furniture","Warehouse Equipment","Table Computer", "Others", "Jasa"];
        $category_code = ["OE","FF","WE","TC","OT","JS"];
        foreach($category_list as $key=>$list){
            $newCategory = new BudgetPricingCategory;
            $newCategory->name = $list;
            $newCategory->code = $category_code[$key];
            $newCategory->save();
        }

        $data = array(
            0 => array('budget_code' => 'OE-01', 'name' => 'Handheld', 'brands' => 'Samsung Galaxy', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '1875500', 'outjs_min_price' => '', 'outjs_max_price' => '1875500'),
            1 => array('budget_code' => 'OE-02', 'name' => 'AC (1/2 PK)', 'brands' => 'Panasonic / Daikin/Sharp/LG/Samsung', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '3800000', 'outjs_min_price' => '', 'outjs_max_price' => '4000000'),
            2 => array('budget_code' => 'OE-03', 'name' => 'AC (3/4 PK)', 'brands' => 'Panasonic / Daikin/Sharp/LG/Samsung', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '3900000', 'outjs_min_price' => '', 'outjs_max_price' => '4500000'),
            3 => array('budget_code' => 'OE-04', 'name' => 'AC (1 PK)', 'brands' => 'Panasonic / Daikin/Sharp/LG/Samsung', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '4600000', 'outjs_min_price' => '', 'outjs_max_price' => '5250000'),
            4 => array('budget_code' => 'OE-05', 'name' => 'AC (1,5 PK)', 'brands' => 'Panasonic / Daikin/Sharp/LG/Samsung', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '5500000', 'outjs_min_price' => '', 'outjs_max_price' => '6300000'),
            5 => array('budget_code' => 'OE-06', 'name' => 'AC (2 PK)', 'brands' => 'Panasonic / Daikin/Sharp/LG/Samsung', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '7100000', 'outjs_min_price' => '', 'outjs_max_price' => '7350000'),
            6 => array('budget_code' => 'OE-07', 'name' => 'Penambahan pipa & selang (per meter & per unit)', 'brands' => '', 'types' => '', 'uom' => 'Meter', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '100000', 'outjs_min_price' => '', 'outjs_max_price' => '100000'),
            7 => array('budget_code' => 'OE-08', 'name' => 'Brankas Kecil  uk. 730x460x510 - Depo / CP', 'brands' => 'Ichiban/I-Safe/Okida/Dragon/Ichiko/Progresif/Chubbsafes/Krisbow', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '9450000', 'outjs_min_price' => '', 'outjs_max_price' => '9450000'),
            8 => array('budget_code' => 'OE-09', 'name' => 'Brankas Besar uk. 1020x650x600 - Cabang', 'brands' => 'Ichiban/ I-Safe/Okida/Dragon/Ichiko/Progresif/Chubbsafes/Krisbow', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '13000000', 'outjs_min_price' => '', 'outjs_max_price' => '15000000'),
            9 => array('budget_code' => 'OE-10', 'name' => 'Brankas faktur 3 laci - MT  Only', 'brands' => 'Indachi', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '20000000', 'outjs_min_price' => '', 'outjs_max_price' => '22000000'),
            10 => array('budget_code' => 'OE-11', 'name' => 'Brankas faktur 4 laci - MT Only', 'brands' => 'Indachi', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '25000000', 'outjs_min_price' => '', 'outjs_max_price' => '27000000'),
            11 => array('budget_code' => 'OE-12', 'name' => 'Mesin Absensi Manual + Card Rack', 'brands' => 'Amano/Time Recorder/Secure', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '3500000', 'outjs_min_price' => '', 'outjs_max_price' => '3675000'),
            12 => array('budget_code' => 'OE-13', 'name' => 'Money Counter', 'brands' => 'Krisbow / Morgan/Dynamic/Secure', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '3675000', 'outjs_min_price' => '', 'outjs_max_price' => '4200000'),
            13 => array('budget_code' => 'OE-14', 'name' => 'Lampu UV Money detector', 'brands' => '', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'non asset', 'injs_min_price' => '', 'injs_max_price' => '450000', 'outjs_min_price' => '', 'outjs_max_price' => '450000'),
            14 => array('budget_code' => 'OE-15', 'name' => 'Emergency Light', 'brands' => '', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'non asset', 'injs_min_price' => '', 'injs_max_price' => '500000', 'outjs_min_price' => '', 'outjs_max_price' => '500000'),
            15 => array('budget_code' => 'OE-16', 'name' => 'Cash Box u/ Kasir', 'brands' => '', 'types' => '', 'uom' => 'Box', 'isAsset' => 'non asset', 'injs_min_price' => '', 'injs_max_price' => '500000', 'outjs_min_price' => '', 'outjs_max_price' => '500000'),
            16 => array('budget_code' => 'OE-17', 'name' => 'Genset 5500 - 6600 Watt', 'brands' => 'Daito/ Firman/ Tiger / Ryu / General / Power One', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '10395000', 'outjs_min_price' => '', 'outjs_max_price' => '11550000'),
            17 => array('budget_code' => 'OE-18', 'name' => 'LCD Proyektor', 'brands' => 'Infocus / Micro Vision', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '5500000', 'outjs_min_price' => '', 'outjs_max_price' => '5500000'),
            18 => array('budget_code' => 'OE-19', 'name' => 'Dispenser', 'brands' => '', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'non asset', 'injs_min_price' => '', 'injs_max_price' => '600000', 'outjs_min_price' => '', 'outjs_max_price' => '600000'),
            19 => array('budget_code' => 'OE-20', 'name' => 'Kotak Peluru', 'brands' => '', 'types' => '', 'uom' => 'Box', 'isAsset' => 'non asset', 'injs_min_price' => '', 'injs_max_price' => '300000', 'outjs_min_price' => '', 'outjs_max_price' => '400000'),
            20 => array('budget_code' => 'OE-21', 'name' => 'Apar', 'brands' => '', 'types' => '', 'uom' => 'Tabung', 'isAsset' => 'non asset', 'injs_min_price' => '', 'injs_max_price' => '1250000', 'outjs_min_price' => '', 'outjs_max_price' => '1250000'),
            21 => array('budget_code' => 'OE-22', 'name' => 'Timbangan Uang Coin', 'brands' => 'Krischef', 'types' => 'EK9350H', 'uom' => 'Unit', 'isAsset' => 'non asset', 'injs_min_price' => '', 'injs_max_price' => '350000', 'outjs_min_price' => '', 'outjs_max_price' => '400000'),
            22 => array('budget_code' => 'OE-23', 'name' => 'Handphone', 'brands' => 'Lokal', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'non asset', 'injs_min_price' => '', 'injs_max_price' => '300000', 'outjs_min_price' => '', 'outjs_max_price' => '300000'),
            23 => array('budget_code' => 'OE-24', 'name' => 'Kipas Angin', 'brands' => 'Cosmos/Maspion /GMC', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'non asset', 'injs_min_price' => '', 'injs_max_price' => '400000', 'outjs_min_price' => '', 'outjs_max_price' => '400000'),
            24 => array('budget_code' => 'OE-25', 'name' => 'Smart TV', 'brands' => 'Samsung, Panasonic', 'types' => '50" / 55"', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '7000000', 'outjs_min_price' => '', 'outjs_max_price' => '7000000'),
            25 => array('budget_code' => 'FF-01', 'name' => 'Meja 1/2 Biro + laci', 'brands' => 'Expo / VIP / Idola / Active / Uno', 'types' => 'MT-3001 / MV 501', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '787500', 'outjs_min_price' => '', 'outjs_max_price' => '997500'),
            26 => array('budget_code' => 'FF-02', 'name' => 'Meja 1/2 biro utk ruang meeting', 'brands' => 'Expo / VIP / Idola / Active / Uno', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '787500', 'outjs_min_price' => '', 'outjs_max_price' => '997500'),
            27 => array('budget_code' => 'FF-03', 'name' => 'Kursi susun', 'brands' => 'Chitose / Futura / Wellness / Star', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'non asset', 'injs_min_price' => '', 'injs_max_price' => '420000', 'outjs_min_price' => '', 'outjs_max_price' => '525000'),
            28 => array('budget_code' => 'FF-04', 'name' => 'Kursi plastik', 'brands' => 'Lion Star/Napoly', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'non asset', 'injs_min_price' => '', 'injs_max_price' => '78750', 'outjs_min_price' => '', 'outjs_max_price' => '78750'),
            29 => array('budget_code' => 'FF-05', 'name' => 'Lemari file 3 laci', 'brands' => 'Brother / VIP / Frontline', 'types' => '3 LACI', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '1785000', 'outjs_min_price' => '', 'outjs_max_price' => '2100000'),
            30 => array('budget_code' => 'FF-06', 'name' => 'Lemari Arsip 3 laci untuk Gudang (plastik)', 'brands' => 'Lokal', 'types' => 'Lokal', 'uom' => 'Unit', 'isAsset' => 'non asset', 'injs_min_price' => '', 'injs_max_price' => '700000', 'outjs_min_price' => '', 'outjs_max_price' => '700000'),
            31 => array('budget_code' => 'FF-07', 'name' => 'Meja 1 biro utk ruang meeting', 'brands' => 'Expo / VIP / Idola / Active / Uno', 'types' => '160x75x75', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '1250000', 'outjs_min_price' => '', 'outjs_max_price' => '1350000'),
            32 => array('budget_code' => 'FF-08', 'name' => 'Meja 1 biro tanpa laci utk salesman', 'brands' => 'Expo / VIP / Idola / Active / Uno', 'types' => '160x75x75', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '1312500', 'outjs_min_price' => '', 'outjs_max_price' => '1417500'),
            33 => array('budget_code' => 'FF-09', 'name' => 'Rak arsip', 'brands' => 'Lokal', 'types' => 'Lokal', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '1250000', 'outjs_min_price' => '', 'outjs_max_price' => '1250000'),
            34 => array('budget_code' => 'FF-10', 'name' => 'Locker Uang (4 Pintu)', 'brands' => 'Lokal', 'types' => 'Lokal', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '1200000', 'outjs_min_price' => '', 'outjs_max_price' => '1500000'),
            35 => array('budget_code' => 'FF-11', 'name' => 'Locker Uang (6 Pintu)', 'brands' => 'Lokal', 'types' => 'Lokal', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '1500000', 'outjs_min_price' => '', 'outjs_max_price' => '1800000'),
            36 => array('budget_code' => 'WE-01', 'name' => 'Hand Pallet 2 ton (BIG)-bahan karet', 'brands' => 'Krisbow / Newlead / Maxiton', 'types' => '685x1220MM', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '4200000', 'outjs_min_price' => '', 'outjs_max_price' => '4800000'),
            37 => array('budget_code' => 'WE-02', 'name' => 'Pallet Kayu', 'brands' => 'Lokal', 'types' => '100 x 120 x 15 cm', 'uom' => 'Pcs', 'isAsset' => 'non asset', 'injs_min_price' => '', 'injs_max_price' => '115000', 'outjs_min_price' => '', 'outjs_max_price' => '157500'),
            38 => array('budget_code' => 'WE-03', 'name' => 'Trolly 150 kg', 'brands' => 'Lokal', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'non asset', 'injs_min_price' => '', 'injs_max_price' => '500000', 'outjs_min_price' => '', 'outjs_max_price' => '650000'),
            39 => array('budget_code' => 'WE-04', 'name' => 'Trolly 300 kg', 'brands' => 'Lokal', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'non asset', 'injs_min_price' => '', 'injs_max_price' => '750000', 'outjs_min_price' => '', 'outjs_max_price' => '750000'),
            40 => array('budget_code' => 'WE-05', 'name' => 'Trolly Roda 4', 'brands' => 'Lokal', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '1400000', 'outjs_min_price' => '', 'outjs_max_price' => '1400000'),
            41 => array('budget_code' => 'WE-06', 'name' => 'Tangga Lipat 2M', 'brands' => 'Lokal', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '1000000', 'outjs_min_price' => '', 'outjs_max_price' => '1000000'),
            42 => array('budget_code' => 'OT-01', 'name' => 'Racking Gudang  (per meter)', 'brands' => 'Lokal', 'types' => '', 'uom' => 'M2', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '600000', 'outjs_min_price' => '', 'outjs_max_price' => '700000'),
            43 => array('budget_code' => 'OT-02', 'name' => 'CCTV 4 kamera', 'brands' => 'Dahua/Hikvision/Hilook', 'types' => 'Sucher', 'uom' => 'Set', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '5000000', 'outjs_min_price' => '', 'outjs_max_price' => '6000000'),
            44 => array('budget_code' => 'OT-03', 'name' => 'CCTV 8 kamera', 'brands' => 'Dahua/Hikvision/Hilook', 'types' => 'Sucher', 'uom' => 'Set', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '9000000', 'outjs_min_price' => '', 'outjs_max_price' => '10000000'),
            45 => array('budget_code' => 'OT-04', 'name' => 'Monitoring Board tanpa kaki', 'brands' => 'Lokal', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '900000', 'outjs_min_price' => '', 'outjs_max_price' => '1400000'),
            46 => array('budget_code' => 'OT-05', 'name' => 'Monitoring Board pakai kaki', 'brands' => 'Lokal', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '1600000', 'outjs_min_price' => '', 'outjs_max_price' => '2000000'),
            47 => array('budget_code' => 'OT-06', 'name' => 'Stavol 5000 watt', 'brands' => 'Lokal', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '3850000', 'outjs_min_price' => '', 'outjs_max_price' => '4400000'),
            48 => array('budget_code' => 'OT-07', 'name' => 'Tandon Air', 'brands' => 'Lokal', 'types' => 'Minimal 2200L', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '', 'injs_max_price' => '2500000', 'outjs_min_price' => '', 'outjs_max_price' => '2500000'),
            49 => array('budget_code' => 'TC-01', 'name' => 'PC Client (Processor Intel Core i3, Memory DDR3/DDR4 4GB, SSD 256GB, HDD 1TB, Windows 64 Pro)', 'brands' => 'Lenovo/HP/Dell', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '9350000', 'injs_max_price' => '11495000', 'outjs_min_price' => '9900000', 'outjs_max_price' => '11495000'),
            50 => array('budget_code' => 'TC-02', 'name' => 'PC Client (Processor Intel Core i5, Memory DDR3/DDR4 4GB, SSD 256GB, HDD 1TB, Windows 64 Pro)', 'brands' => 'Epson/Fujitsu/Canon/Hp', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '11220000', 'injs_max_price' => '13794000', 'outjs_min_price' => '11880000', 'outjs_max_price' => '13794000'),
            51 => array('budget_code' => 'TC-03', 'name' => 'PC Client (Processor Intel Core i7, Memory DDR3/DDR4 4GB, SSD 265GB, HDD 1TB, Windows 64 Pro)', 'brands' => 'Epson/Fujitsu/Canon/Hp', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '13464000', 'injs_max_price' => '16552800', 'outjs_min_price' => '14256000', 'outjs_max_price' => '16552800'),
            52 => array('budget_code' => 'TC-04', 'name' => 'Printer Dot Matrix', 'brands' => 'Epson LX310', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '3038500', 'injs_max_price' => '3296000', 'outjs_min_price' => '3622500', 'outjs_max_price' => '3885000'),
            53 => array('budget_code' => 'TC-05', 'name' => 'Printer Dot Matrix', 'brands' => 'Epson LQ2190', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '9371250', 'injs_max_price' => '10473750', 'outjs_min_price' => '9371250', 'outjs_max_price' => '10473750'),
            54 => array('budget_code' => 'TC-06', 'name' => 'Printer Multifungsi', 'brands' => 'Epson/Canon/Hp', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '3307500', 'injs_max_price' => '4410000', 'outjs_min_price' => '3307500', 'outjs_max_price' => '4410000'),
            55 => array('budget_code' => 'TC-07', 'name' => 'Printer Laserjet', 'brands' => 'Epson/Canon/Hp', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '1653750', 'injs_max_price' => '2205000', 'outjs_min_price' => '1653750', 'outjs_max_price' => '2205000'),
            56 => array('budget_code' => 'TC-08', 'name' => 'Scanner', 'brands' => 'Epson/Canon/Hp', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '1378125', 'injs_max_price' => '1653750', 'outjs_min_price' => '1378125', 'outjs_max_price' => '1653750'),
            57 => array('budget_code' => 'TC-09', 'name' => 'Server', 'brands' => 'Lenovo/HP/Dell', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '20396250', 'injs_max_price' => '22050000', 'outjs_min_price' => '20396250', 'outjs_max_price' => '22050000'),
            58 => array('budget_code' => 'TC-10', 'name' => 'Monitor Server', 'brands' => 'LG/HP/Acer', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '1378125', 'injs_max_price' => '1653750', 'outjs_min_price' => '1378125', 'outjs_max_price' => '1653750'),
            59 => array('budget_code' => 'TC-11', 'name' => 'License SCYLLA', 'brands' => 'Pratesis', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '14883750', 'injs_max_price' => '16537500', 'outjs_min_price' => '14883750', 'outjs_max_price' => '16537500'),
            60 => array('budget_code' => 'TC-12', 'name' => 'UPS 1200va', 'brands' => 'ICA/Prolink', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '1102500', 'injs_max_price' => '1653750', 'outjs_min_price' => '1102500', 'outjs_max_price' => '1653750'),
            61 => array('budget_code' => 'TC-13', 'name' => 'Switch 16 port', 'brands' => 'Dlink/Tplink', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'asset', 'injs_min_price' => '771750', 'injs_max_price' => '992250', 'outjs_min_price' => '771750', 'outjs_max_price' => '992250'),
            62 => array('budget_code' => 'TC-14', 'name' => 'Finger Scan', 'brands' => 'Solution', 'types' => '', 'uom' => 'Unit', 'isAsset' => 'non asset', 'injs_min_price' => '2205000', 'injs_max_price' => '2976750', 'outjs_min_price' => '2205000', 'outjs_max_price' => '2976750'),
            63 => array('budget_code' => 'TC-15', 'name' => 'Kabel Jaringan LAN', 'brands' => 'Belden/Prolink', 'types' => '', 'uom' => 'Meter', 'isAsset' => 'non asset', 'injs_min_price' => '1433250', 'injs_max_price' => '1984500', 'outjs_min_price' => '1433250', 'outjs_max_price' => '1984500'),
            64 => array('budget_code' => 'TC-16', 'name' => 'Connector RJ45 ( per pax)', 'brands' => 'Lokal', 'types' => 'RJ 45', 'uom' => 'Pcs', 'isAsset' => 'non asset', 'injs_min_price' => '110250', 'injs_max_price' => '165375', 'outjs_min_price' => '110250', 'outjs_max_price' => '165375'),
        );

        foreach($data as $budget_data){
            $category_code = explode('-', $budget_data['budget_code'])[0];
            $is_budget_category_exist = BudgetPricingCategory::where("code",$category_code)->first();
            $budget_category = $is_budget_category_exist;
            if(!$budget_category){
                print("Category Code ".$category_code." is not exists \n");
                continue;
            }

            $newBudget = new BudgetPricing;
            $newBudget->budget_pricing_category_id = $budget_category->id;
            $newBudget->code            = $budget_data['budget_code'];
            $newBudget->name            = $budget_data['name'];
            $newBudget->uom             = $budget_data['uom'];
            $newBudget->injs_min_price  = ($budget_data['injs_min_price']) ? doubleval($budget_data['injs_min_price']) : null;
            $newBudget->injs_max_price  = ($budget_data['injs_max_price']) ? doubleval($budget_data['injs_max_price']) : null;
            $newBudget->outjs_min_price = ($budget_data['outjs_min_price']) ? doubleval($budget_data['outjs_min_price']) : null;
            $newBudget->outjs_max_price = ($budget_data['outjs_max_price']) ? doubleval($budget_data['outjs_max_price']) : null;
            $newBudget->isAsset         = ($budget_data['isAsset'] == "asset") ? true : false;
            $newBudget->save();

            $types = explode("/",$budget_data["types"]);
            foreach($types as $type){
                $newBudgetType                     = new BudgetType;
                $newBudgetType->budget_pricing_id  = $newBudget->id;
                $newBudgetType->name               = $type;
            }
            
            $brands = explode("/",$budget_data["brands"]);
            foreach($brands as $brand){
                $newBudgetbrand = new BudgetBrand;
                $newBudgetbrand->budget_pricing_id = $newBudget->id;
                $newBudgetbrand->name = $brand;
                $newBudgetbrand->save();
            }
        }
        DB::commit();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    }
}
