<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FileCategory;
use App\Models\FileCompletement;

class FileCompletementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $category_names = [
            "Perbaikan Area Gudang",
            "Pallet Kayu",
            "Refill APAR",
            "Printer",
            "AC",
            "CCTV",
            "Barang IT / Non IT (Kelengkapan Kantor)",
            "Armada Asset",
            "Renovasi Kantor (Sekat, Teralis dsb)",
            "ATK",
            "ART",
            "Asset IT",
            "Non IT",
            "Ban Kendaraan",
            "Disposal",
        ];
        $category_item_count = [5,4,2,5,4,4,4,5,5,2,2,7,6,4,3];
        $items = [
            "Penawaran resmi dari 2 vendor (dilengkapi KTP dan NPWP)",
            "Foto Dokumentasi kerusakan",
            "BA Kerusakan Gudang",
            "Akta Sewa Gudang",
            "Layout Gudang",
            "Penawaran resmi dari 2 vendor (dilengkapi KTP dan NPWP)",
            "Jumlah As is dan To be",
            "Layout Gudang",
            "Perhitungan luas gudang",
            "Penawaran 2 vendor (KOP/KTP, NPWP)",
            "Dokumentasi APAR (tgl expired, Quantity)",
            "Penawaran resmi dari 2 vendor (dilengkapi KTP dan NPWP)",
            "BA Kerusakan dari Area",
            "Surat Keterangan Kerusakan/hasil pemerikasaan dari vendor",
            "Dokumentasi printer (mencantumkan nomor asset)",
            "Contoh Hasil Print out",
            "Penawaran resmi dari 2 vendor (dilengkapi KTP dan NPWP)",
            "BA Kerusakan dari Area ",
            "Surat Keterangan Kerusakan/hasil pemerikasaan dari vendor",
            "Dokumentasi AC (mencantumkan nomor asset)",
            "Penawaran resmi dari 2 vendor (dilengkapi KTP dan NPWP)",
            "BA Kerusakan dari Area (Foto Kerusakan)",
            "Surat Keterangan Kerusakan/hasil pemerikasaan dari vendor",
            "Dokumentasi CCTV (mencantumkan nomor asset)",
            "Penawaran resmi dari 2 vendor (dilengkapi KTP dan NPWP)",
            "BA Kerusakan dari Area",
            "Surat Keterangan Kerusakan/hasil pemerikasaan dari vendor",
            "Dokumentasi Kerusakan",
            "Penawaran resmi dari 2 vendor (dilengkapi KTP dan NPWP)",
            "BA Kerusakan dari Area / history perbaikan",
            "Surat Keterangan Kerusakan/hasil pemerikasaan dari vendor",
            "Dokumentasi Kerusakan",
            "Dokumentasi kendaraan (plat nopol, no asset, tampak depan, kanan, kiri, belakang)",
            "Penawaran resmi dari 2 vendor (dilengkapi KTP dan NPWP)",
            "Dokumentasi Area kantor",
            "Layout Kantor",
            "Akta Sewa Gudang",
            "Area",
            "Penawaran resmi dari 2 vendor (dilengkapi KTP dan NPWP)",
            "Buffer Stok ATK",
            "Penawaran resmi dari 2 vendor (dilengkapi KTP dan NPWP)",
            "Buffer Stok ART",
            "Vendor Nasional / 2 vendor lokal",
            "Form IO",
            "BA Kerusakan dari Area (Foto Kerusakan)",
            "Surat Keterangan Kerusakan/hasil pemerikasaan dari vendor",
            "Dokumentasi Kerusakan (Jika menganti barang IT yang rusak)",
            "Form Disposal Asset/Slip Setoran Penjualan Disposal",
            "Penawaran resmi dari 2 vendor (dilengkapi KTP dan NPWP)",
            "Dokumentasi Asset/ Inventaris (jika asset/inventaris rusak)",
            "Form Disposal Asset/Slip Setoran Penjualan Disposal",
            "BA Kerusakan dari Area",
            "Surat Keterangan Kerusakan/hasil pemerikasaan dari vendor",
            "Form IO",
            "Penawaran resmi dari 2 vendor (dilengkapi KTP dan NPWP)",
            "Dokumentasi kendaraan (plat nopol, no asset, tampak depan, kanan, kiri, belakang)",
            "Dokumentasi Ban Rusak",
            "BA Kerusakan dari Area / history perbaikan",
            "Penawaran resmi dari 2 vendor (dilengkapi KTP dan NPWP)",
            "Dokumentasi asset/inventaris yang akan di disposal",
            "Surat Keterangan Kerusakan/hasil pemerikasaan dari vendor menyatakan tidak bisa diperbaiki",
        ];

        $filenames =[
            "Perbaikan_Area_Gudang_Penawaran_resmi_dari_2_vendor_(dilengkapi_KTP_dan_NPWP)_Area",
            "Perbaikan_Area_Gudang_Foto_Dokumentasi_kerusakan_Area",
            "Perbaikan_Area_Gudang_BA_Kerusakan_Gudang_Area",
            "Perbaikan_Area_Gudang_Akta_Sewa_Gudang_Area",
            "Perbaikan_Area_Gudang_Layout_Gudang_Area",
            "Pallet_Kayu_Penawaran_resmi_dari_2_vendor_(dilengkapi_KTP_dan_NPWP)_Area",
            "Pallet_Kayu_Jumlah_As_is_dan_To_be_Area",
            "Pallet_Kayu_Layout_Gudang_Area",
            "Pallet_Kayu_Perhitungan_luas_gudang_Area",
            "Refill_APAR_Penawaran_2_vendor_(KOP/KTP,_NPWP)_Area",
            "Refill_APAR_Dokumentasi_APAR_(tgl_expired,_Quantity)_Area",
            "Printer_Penawaran_resmi_dari_2_vendor_(dilengkapi_KTP_dan_NPWP)_Area",
            "Printer_BA_Kerusakan_dari_Area_Area",
            "Printer_Surat_Keterangan_Kerusakan/hasil_pemerikasaan_dari_vendor_Area",
            "Printer_Dokumentasi_printer_(mencantumkan_nomor_asset)_Area",
            "Printer_Contoh_Hasil_Print_out_Area",
            "AC_Penawaran_resmi_dari_2_vendor_(dilengkapi_KTP_dan_NPWP)_Area",
            "AC_BA_Kerusakan_dari_Area__Area",
            "AC_Surat_Keterangan_Kerusakan/hasil_pemerikasaan_dari_vendor_Area",
            "AC_Dokumentasi_AC_(mencantumkan_nomor_asset)_Area",
            "CCTV_Penawaran_resmi_dari_2_vendor_(dilengkapi_KTP_dan_NPWP)_Area",
            "CCTV_BA_Kerusakan_dari_Area_(Foto_Kerusakan)_Area",
            "CCTV_Surat_Keterangan_Kerusakan/hasil_pemerikasaan_dari_vendor_Area",
            "CCTV_Dokumentasi_CCTV_(mencantumkan_nomor_asset)_Area",
            "Barang_IT_/_Non_IT_(Kelengkapan_Kantor)_Penawaran_resmi_dari_2_vendor_(dilengkapi_KTP_dan_NPWP)_Area",
            "Barang_IT_/_Non_IT_(Kelengkapan_Kantor)_BA_Kerusakan_dari_Area_Area",
            "Barang_IT_/_Non_IT_(Kelengkapan_Kantor)_Surat_Keterangan_Kerusakan/hasil_pemerikasaan_dari_vendor_Area",
            "Barang_IT_/_Non_IT_(Kelengkapan_Kantor)_Dokumentasi_Kerusakan_Area",
            "Armada_Aset_Penawaran_resmi_dari_2_vendor_(dilengkapi_KTP_dan_NPWP)_Area",
            "Armada_Aset_BA_Kerusakan_dari_Area_/_history_perbaikan_Area",
            "Armada_Aset_Surat_Keterangan_Kerusakan/hasil_pemerikasaan_dari_vendor_Area",
            "Armada_Aset_Dokumentasi_Kerusakan_Area",
            "Armada_Aset_Dokumentasi_kendaraan_(plat_nopol,_no_asset,_tampak_depan,_kanan,_kiri,_belakang)_Area",
            "Renovasi_Kantor_Penawaran_resmi_dari_2_vendor_(dilengkapi_KTP_dan_NPWP)_Area",
            "Renovasi_Kantor_Dokumentasi_Area_kantor_Area",
            "Renovasi_Kantor_Layout_Kantor_Area",
            "Renovasi_Kantor_Akta_Sewa_Gudang_Area",
            "Renovasi_Kantor_Area",
            "ATK_Penawaran_resmi_dari_2_vendor_(dilengkapi_KTP_dan_NPWP)_Area",
            "ATK_Buffer_Stok_ATK_Area",
            "ART_Penawaran_resmi_dari_2_vendor_(dilengkapi_KTP_dan_NPWP)_Area",
            "ART_Buffer_Stok_ART_Area",
            "Aset_IT_Vendor_Nasional_/_2_vendor_lokal_Area",
            "Aset_IT_Form_IO_Area",
            "Aset_IT_Form_FRI_Area",
            "Aset_IT_BA_Kerusakan_dari_Area_(Foto_Kerusakan)_Area",
            "Aset_IT_Surat_Keterangan_Kerusakan/hasil_pemerikasaan_dari_vendor_Area",
            "Aset_IT_Dokumentasi_Kerusakan_(Jika_menganti_barang_IT_yang_rusak)_Area",
            "Aset_IT_Form_Disposal_Asset/Slip_Setoran_Penjualan_Disposal_Area",
            "Non_IT_Penawaran_resmi_dari_2_vendor_(dilengkapi_KTP_dan_NPWP)_Area",
            "Non_IT_Dokumentasi_Asset/_Inventaris_(jika_asset/inventaris_rusak)_Area",
            "Non_IT_Form_Disposal_Asset/Slip_Setoran_Penjualan_Disposal_Area",
            "Non_IT_BA_Kerusakan_dari_Area_Area",
            "Non_IT_Surat_Keterangan_Kerusakan/hasil_pemerikasaan_dari_vendor_Area",
            "Non_IT_Form_IO_Area",
            "Ban_Kendaraan_Penawaran_resmi_dari_2_vendor_(dilengkapi_KTP_dan_NPWP)_Area",
            "Ban_Kendaraan_Dokumentasi_kendaraan_(plat_nopol,_no_asset,_tampak_depan,_kanan,_kiri,_belakang)_Area",
            "Ban_Kendaraan_Dokumentasi_Ban_Rusak_Area",
            "Ban_Kendaraan_BA_Kerusakan_dari_Area_/_history_perbaikan_Area",
            "Disposal_Penawaran_resmi_dari_2_vendor_(dilengkapi_KTP_dan_NPWP)_Area",
            "Disposal_Dokumentasi_asset/inventaris_yang_akan_di_disposal_Area",
            "Disposal_Surat_Keterangan_Kerusakan/hasil_pemerikasaan_dari_vendor_menyatakan_tidak_bisa_diperbaiki_Area",
        ];
        $count = 0;
        foreach($category_names as $key=>$category_item){
            $category = new FileCategory;
            $category->name = $category_item;
            $category->save();
            for($i = 0; $i <$category_item_count[$key]; $i++){
                $file = new FileCompletement;
                $file->file_category_id = $category->id;
                $file->name = $items[$count];
                $file->filename = $filenames[$count];
                $file->save();
                $count++;
            }
        }
    }
}
