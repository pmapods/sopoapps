<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Authorization;
use App\Models\AuthorizationDetail;

class AuthorizationSeeder extends Seeder
{
    /**d
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // ticketing
        $newAuthorization                 = new Authorization;
        $newAuthorization->salespoint_id  = 1;
        $newAuthorization->form_type      = 0;
        $newAuthorization->save();
        $employee_ids = [2,3,4];
        $position_ids = [2,3,4];
        $as = ['Pengaju','Atasan Langsung','Atasan Tidak Langsung'];
        foreach ($employee_ids as $key=>$id){
            $detail                         = new AuthorizationDetail;
            $detail->authorization_id       = $newAuthorization->id;
            $detail->employee_id            = $id;
            $detail->employee_position_id   = $position_ids[$key];
            $detail->sign_as                = $as[$key];
            $detail->level                  = $key+1;
            $detail->save();
        }

        // bidding 
        $newAuthorization                 = new Authorization;
        $newAuthorization->salespoint_id  = 1;
        $newAuthorization->form_type      = 1;
        $newAuthorization->save();
        $employee_ids = [5,6,7];
        $position_ids = [5,6,7];
        $as = ['Diajukan Oleh','Diperiksa Oleh','Disetujui Oleh'];
        foreach ($employee_ids as $key=>$id){
            $detail                         = new AuthorizationDetail;
            $detail->authorization_id       = $newAuthorization->id;
            $detail->employee_id            = $id;
            $detail->employee_position_id   = $position_ids[$key];
            $detail->sign_as                = $as[$key];
            $detail->level                  = $key+1;
            $detail->save();
        }

        // pr 
        $newAuthorization                 = new Authorization;
        $newAuthorization->salespoint_id  = 1;
        $newAuthorization->form_type      = 2;
        $newAuthorization->save();
        $employee_ids = [8,9,10,11];
        $position_ids = [8,9,10,11];
        $as = ['Diperiksa Oleh','Disetujui Oleh','Disetujui Oleh','Disetujui Oleh'];
        foreach ($employee_ids as $key=>$id){
            $detail                         = new AuthorizationDetail;
            $detail->authorization_id       = $newAuthorization->id;
            $detail->employee_id            = $id;
            $detail->employee_position_id   = $position_ids[$key];
            $detail->sign_as                = $as[$key];
            $detail->level                  = $key+1;
            $detail->save();
        }
        
        // po
        $newAuthorization                 = new Authorization;
        $newAuthorization->salespoint_id  = 1;
        $newAuthorization->form_type      = 3;
        $newAuthorization->save();
        $employee_ids = [8,9];
        $position_ids = [8,9];
        $as = ['Dibuat Oleh','Diperiksa dan disetujui oleh'];
        foreach ($employee_ids as $key=>$id){
            $detail                         = new AuthorizationDetail;
            $detail->authorization_id       = $newAuthorization->id;
            $detail->employee_id            = $id;
            $detail->employee_position_id   = $position_ids[$key];
            $detail->sign_as                = $as[$key];
            $detail->level                  = $key+1;
            $detail->save();
        }

        // armadaticketing
        $newAuthorization                 = new Authorization;
        $newAuthorization->salespoint_id  = 1;
        $newAuthorization->form_type      = 7;
        $newAuthorization->save();
        $employee_ids = [2,3,4];
        $position_ids = [2,3,4];
        $as = ['Pengaju','Atasan Langsung','Atasan Tidak Langsung'];
        foreach ($employee_ids as $key=>$id){
            $detail                         = new AuthorizationDetail;
            $detail->authorization_id       = $newAuthorization->id;
            $detail->employee_id            = $id;
            $detail->employee_position_id   = $position_ids[$key];
            $detail->sign_as                = $as[$key];
            $detail->level                  = $key+1;
            $detail->save();
        }
        
        // armadaticketing 
        $newAuthorization                 = new Authorization;
        $newAuthorization->salespoint_id  = 1;
        $newAuthorization->form_type      = 8;
        $newAuthorization->save();
        $employee_ids = [2,3,4];
        $position_ids = [2,3,4];
        $as = ['Pengaju','Atasan Langsung','Atasan Tidak Langsung'];
        foreach ($employee_ids as $key=>$id){
            $detail                         = new AuthorizationDetail;
            $detail->authorization_id       = $newAuthorization->id;
            $detail->employee_id            = $id;
            $detail->employee_position_id   = $position_ids[$key];
            $detail->sign_as                = $as[$key];
            $detail->level                  = $key+1;
            $detail->save();
        }

        // formulir fasilitas
        $newAuthorization                 = new Authorization;
        $newAuthorization->salespoint_id  = 1;
        $newAuthorization->form_type      = 4;
        $newAuthorization->save();
        $employee_ids = [2,3];
        $position_ids = [2,3];
        $as = ['Atasan ybs','Atasan dari atasan ybs'];
        foreach ($employee_ids as $key=>$id){
            $detail                         = new AuthorizationDetail;
            $detail->authorization_id       = $newAuthorization->id;
            $detail->employee_id            = $id;
            $detail->employee_position_id   = $position_ids[$key];
            $detail->sign_as                = $as[$key];
            $detail->level                  = $key+1;
            $detail->save();
        }

        // formulir perpanjangan
        $newAuthorization                 = new Authorization;
        $newAuthorization->salespoint_id  = 1;
        $newAuthorization->form_type      = 6;
        $newAuthorization->save();
        $employee_ids = [2,3,4,5];
        $position_ids = [2,3,4,5];
        $as = ['Yang Mengajukan','Diketahui Oleh','Diketahui Oleh','Disetujui'];
        foreach ($employee_ids as $key=>$id){
            $detail                         = new AuthorizationDetail;
            $detail->authorization_id       = $newAuthorization->id;
            $detail->employee_id            = $id;
            $detail->employee_position_id   = $position_ids[$key];
            $detail->sign_as                = $as[$key];
            $detail->level                  = $key+1;
            $detail->save();
        }

        // formulir mutasi
        $newAuthorization                 = new Authorization;
        $newAuthorization->salespoint_id  = 1;
        $newAuthorization->form_type      = 5;
        $newAuthorization->save();
        $employee_ids = [2,3,4,5,6,7];
        $position_ids = [2,3,4,5,6,7];
        $as = ['Dibuat Oleh','Diperiksa Oleh','Diperiksa Oleh','Diperiksa Oleh','Disetujui Oleh','Disetujui Oleh','Disetujui Oleh'];
        foreach ($employee_ids as $key=>$id){
            $detail                         = new AuthorizationDetail;
            $detail->authorization_id       = $newAuthorization->id;
            $detail->employee_id            = $id;
            $detail->employee_position_id   = $position_ids[$key];
            $detail->sign_as                = $as[$key];
            $detail->level                  = $key+1;
            $detail->save();
        }

        // formulir mutasi
        $newAuthorization                 = new Authorization;
        $newAuthorization->salespoint_id  = 1;
        $newAuthorization->form_type      = 9;
        $newAuthorization->save();
        $employee_ids = [2,3,4,5];
        $position_ids = [2,3,4,5];
        $as = ['Disiapkan Oleh', 'Diperiksa Oleh', 'Diperiksa Oleh', 'Diperiksa Oleh','Disetujui Oleh'];
        foreach ($employee_ids as $key=>$id){
            $detail                         = new AuthorizationDetail;
            $detail->authorization_id       = $newAuthorization->id;
            $detail->employee_id            = $id;
            $detail->employee_position_id   = $position_ids[$key];
            $detail->sign_as                = $as[$key];
            $detail->level                  = $key+1;
            $detail->save();
        }
    }
}
