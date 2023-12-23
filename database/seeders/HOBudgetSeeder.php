<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
use App\Models\HOBudgetCategory;
use App\Models\HOBudget;
class HOBudgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        HOBudgetCategory::truncate();
        HOBudget::truncate();
        DB::beginTransaction();
        $seeder = array(
            array("category"=>"ASSET","name"=>"CPU"),
            array("category"=>"ASSET","name"=>"Hardisk Eksternal"),
            array("category"=>"ASSET","name"=>"Laptop"),
            array("category"=>"ASSET","name"=>"Meja Meeting"),
            array("category"=>"ASSET","name"=>"Monitor"),
            array("category"=>"ASSET","name"=>"PC Rakitan (I5-8 Gb)"),
            array("category"=>"ASSET","name"=>"Perlengkapan Dokumentasi Ok's Talk"),
            array("category"=>"ASSET","name"=>"Printer ID Card"),
            array("category"=>"ASSET","name"=>"Printer Multifungsi"),
            array("category"=>"ASSET","name"=>"Server"),
            array("category"=>"ASURANSI","name"=>"Asuransi Aset & Inventaris"),
            array("category"=>"ASURANSI","name"=>"Asuransi Gedung"),
            array("category"=>"ASURANSI","name"=>"Asuransi Lainnya"),
            array("category"=>"ASURANSI","name"=>"Cash In Transit"),
            array("category"=>"ATK & CETAKAN","name"=>"Buku PP"),
            array("category"=>"ATK & CETAKAN","name"=>"Catridge Printer HP 3835 (680) Black"),
            array("category"=>"ATK & CETAKAN","name"=>"Catridge Printer HP 3835 (680) Colour"),
            array("category"=>"ATK & CETAKAN","name"=>"Catridge Printer Lx"),
            array("category"=>"ATK & CETAKAN","name"=>"Cetakan Checklist (Rim)"),
            array("category"=>"ATK & CETAKAN","name"=>"Cetakan Kop Surat"),
            array("category"=>"ATK & CETAKAN","name"=>"Continous Form 1 : 2"),
            array("category"=>"ATK & CETAKAN","name"=>"Continous Form 1 Ply"),
            array("category"=>"ATK & CETAKAN","name"=>"Continous Form 2 : 2"),
            array("category"=>"ATK & CETAKAN","name"=>"Continous Form 2 Ply"),
            array("category"=>"ATK & CETAKAN","name"=>"Dus File"),
            array("category"=>"ATK & CETAKAN","name"=>"Katalog Produk GT"),
            array("category"=>"ATK & CETAKAN","name"=>"Katalog Produk MT"),
            array("category"=>"ATK & CETAKAN","name"=>"Kertas Hvs 70Gr A4"),
            array("category"=>"ATK & CETAKAN","name"=>"Kertas Hvs 70Gr F4"),
            array("category"=>"ATK & CETAKAN","name"=>"Kertas Po 3 Ply"),
            array("category"=>"ATK & CETAKAN","name"=>"Pvc Blank Card"),
            array("category"=>"ATK & CETAKAN","name"=>"Sosialisasi Sop (Banner)"),
            array("category"=>"ATK & CETAKAN","name"=>"Stationery Fare"),
            array("category"=>"ATK & CETAKAN","name"=>"Tinta Epson 664 H, B, M, K"),
            array("category"=>"ATK & CETAKAN","name"=>"Tinta Id Card"),
            array("category"=>"ATK & CETAKAN","name"=>"Toner Printer (Laser Jet 79A)"),
            array("category"=>"ATK & CETAKAN","name"=>"Toner Printer (Laser Jet M12W)"),
            array("category"=>"INTERNET","name"=>"Biznet Internet Connection Existng 140 Mb"),
            array("category"=>"INTERNET","name"=>"HHT"),
            array("category"=>"INTERNET","name"=>"Icon (For Sap)"),
            array("category"=>"INTERNET","name"=>"Icon Internet 60 Mb"),
            array("category"=>"INTERNET","name"=>"Icon Metronet For Sap (Gedung Cyber)"),
            array("category"=>"INTERNET","name"=>"Indihome / Office Wi-Fi"),
            array("category"=>"INVENTARIS","name"=>"Kursi"),
            array("category"=>"INVENTARIS","name"=>"Meja 1/2 Biro"),
            array("category"=>"JASA TENAGA AHLI","name"=>"Perizinan"),
            array("category"=>"JASA TENAGA AHLI","name"=>"Surveillance ISO"),
            array("category"=>"LISENSI","name"=>"Cloud Hosting For Sfa (Digital Ocean) - Sfa Sap4Hana"),
            array("category"=>"LISENSI","name"=>"Cloud Hosting For Sfa (Digital Ocean) - Sfa Scylla"),
            array("category"=>"LISENSI","name"=>"Domain PMA (pinusmerahabadi.co.id)"),
            array("category"=>"LISENSI","name"=>"Fire Base Canvasser Dev/Qa (Baru)"),
            array("category"=>"LISENSI","name"=>"Fire Base Canvasser Production"),
            array("category"=>"LISENSI","name"=>"License Zimbra Mail Periode 2021 - 2026"),
            array("category"=>"LISENSI","name"=>"Microsoft Office"),
            array("category"=>"LISENSI","name"=>"Penyimpanan Cloud (Google Drive)"),
            array("category"=>"LISENSI","name"=>"Sophos Antivirus Malware Spam U/ 150 - 250 Client"),
            array("category"=>"LISENSI","name"=>"Sophos Antivirus Malware Spam U/ 5 Server"),
            array("category"=>"LISENSI","name"=>"Sophos Antivirus Malware Spam U/ 600 Client"),
            array("category"=>"LISENSI","name"=>"Sophos Renewal License Xg-310"),
            array("category"=>"LISENSI","name"=>"Sophos Storage 1 Tahun"),
            array("category"=>"LISENSI","name"=>"Sophos Utm Anti Spam"),
            array("category"=>"LISENSI","name"=>"Teamviewer License"),
            array("category"=>"LISENSI","name"=>"Vps Midleware Scylla (Existing) --> Ke Pma"),
            array("category"=>"LISENSI","name"=>"Zoom License Periode 2021-2022"),
            array("category"=>"PEMELIHARAAN HARDWARE","name"=>"Server Ram & Storage Automation"),
            array("category"=>"PEMELIHARAAN HARDWARE","name"=>"Server Ram & Storage Backup"),
            array("category"=>"PEMELIHARAAN KANTOR","name"=>"Kabel Data Belden Cat 6"),
            array("category"=>"PEMELIHARAAN KANTOR","name"=>"Lan Connector RJ 45"),
            array("category"=>"PEMELIHARAAN KANTOR","name"=>"Pemeliharaan Lainnya"),
            array("category"=>"PEMELIHARAAN KANTOR","name"=>"Pemeliharaan Peralatan Kantor"),
            array("category"=>"PEMELIHARAAN KANTOR","name"=>"Perbaikan Sarana & Prasarana Rutin"),
            array("category"=>"PEMELIHARAAN KANTOR","name"=>"Zimbra Mail Server Maintenance (Per 3 Bulan)"),
            array("category"=>"PEMELIHARAAN SOFTWARE","name"=>"Aruba Wireless Backup"),
            array("category"=>"PEMELIHARAN HARDWARE","name"=>"Power Supply"),
            array("category"=>"PEMELIHARAN HARDWARE","name"=>"Ram PC 4 GB"),
            array("category"=>"PERLENGKAPAN KANTOR","name"=>"Keyboard"),
            array("category"=>"PERLENGKAPAN KANTOR","name"=>"Mouse"),
            array("category"=>"PERLENGKAPAN KANTOR","name"=>"SSD"),
            array("category"=>"POS & LAYANAN DOKUMEN","name"=>"Biaya Kirim Dokumen Klaim"),
            array("category"=>"POS & LAYANAN DOKUMEN","name"=>"Other"),
            array("category"=>"REKRUTMEN","name"=>"Banner lowongan pekerjaan"),
            array("category"=>"REKRUTMEN","name"=>"Company branding"),
            array("category"=>"REKRUTMEN","name"=>"Jobstreet"),
            array("category"=>"REKRUTMEN","name"=>"TA Interview"),
            array("category"=>"RUMAH TANGGA KANTOR","name"=>"Air Minum Cup"),
            array("category"=>"RUMAH TANGGA KANTOR","name"=>"Air Minum Galon"),
            array("category"=>"RUMAH TANGGA KANTOR","name"=>"Art Kantor"),
            array("category"=>"RUMAH TANGGA KANTOR","name"=>"BBM (utk Genset)"),
            array("category"=>"RUMAH TANGGA KANTOR","name"=>"BBM Operasional OB"),
            array("category"=>"RUMAH TANGGA KANTOR","name"=>"Buka Puasa Eksternal"),
            array("category"=>"RUMAH TANGGA KANTOR","name"=>"Buka Puasa Internal"),
            array("category"=>"RUMAH TANGGA KANTOR","name"=>"Coffee Break"),
            array("category"=>"RUMAH TANGGA KANTOR","name"=>"Fogging"),
            array("category"=>"RUMAH TANGGA KANTOR","name"=>"Gas Kompor"),
            array("category"=>"RUMAH TANGGA KANTOR","name"=>"Kebutuhan Rumah Tangga"),
            array("category"=>"RUMAH TANGGA KANTOR","name"=>"Listrik"),
            array("category"=>"RUMAH TANGGA KANTOR","name"=>"Parcel Eksternal"),
            array("category"=>"RUMAH TANGGA KANTOR","name"=>"Parcel Internal"),
            array("category"=>"RUMAH TANGGA KANTOR","name"=>"PBB"),
            array("category"=>"RUMAH TANGGA KANTOR","name"=>"Penangulangan Covid 19"),
            array("category"=>"RUMAH TANGGA KANTOR","name"=>"Perayaan Hut Ri"),
            array("category"=>"RUMAH TANGGA KANTOR","name"=>"Pest Management ( Pest Control)"),
            array("category"=>"RUMAH TANGGA KANTOR","name"=>"PMA Anniversary"),
            array("category"=>"RUMAH TANGGA KANTOR","name"=>"Sedot Wc"),
            array("category"=>"RUMAH TANGGA KANTOR","name"=>"Snack Metting"),
            array("category"=>"RUMAH TANGGA KANTOR","name"=>"Telpon PSTN"),
            array("category"=>"SERAGAM","name"=>"1C"),
            array("category"=>"SERAGAM","name"=>"2A"),
            array("category"=>"SERAGAM","name"=>"2B"),
            array("category"=>"SERAGAM","name"=>"2C"),
            array("category"=>"SERAGAM","name"=>"3A"),
            array("category"=>"SERAGAM","name"=>"3B"),
            array("category"=>"SERAGAM","name"=>"3C"),
            array("category"=>"SERAGAM","name"=>"4A"),
            array("category"=>"SERAGAM","name"=>"4B"),
            array("category"=>"SERAGAM","name"=>"4C"),
            array("category"=>"SERAGAM","name"=>"5A"),
            array("category"=>"SERAGAM","name"=>"5B"),
            array("category"=>"SERAGAM","name"=>"5C"),
            array("category"=>"SERAGAM","name"=>"6A"),
            array("category"=>"SEWA LAINYA","name"=>"Mesin Foto Copy & Hand Dryer"),
            array("category"=>"SEWA LAINYA","name"=>"Sewa Laptop"),
            array("category"=>"TENAGA AHLI","name"=>"Aktuaria Fee"),
            array("category"=>"TENAGA AHLI","name"=>"Konsultan Pajak"),
            array("category"=>"TENAGA AHLI","name"=>"Notaris Fee"),
            array("category"=>"TRAINING","name"=>"Training")
        );
        $ho_budgets = collect($seeder);
        $categories_name = $ho_budgets->pluck('category')->unique();
        foreach($categories_name as $key=>$name){
            if($name != null){
                $new = new HOBudgetCategory;
                $new->name = $name;
                $new->save();
            }
        }
        // $frequencies = ['monthly','quarterly','yearly','if any'];
        foreach ($ho_budgets as $key =>$budget) {
            $new = new HOBudget;
            $selected_category = HOBudgetCategory::where('name',$budget['category'])->first();
            $new->ho_budget_category_id = $selected_category->id;
            $new->name = $budget['name'];

            $code = "HO"."-".str_repeat("0", 3-strlen($key+1)).($key+1);
            $new->code = $code;
            // $new->frequency = $budget['frequency'];
            // $new->frequency = $frequencies[array_rand($frequencies)];
            $new->save();
        }
        DB::commit();
        
    }
}
