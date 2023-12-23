<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AuthorizationDetail;
use App\Models\EmployeeMenuAccess;
class employeeSetAccessbyPosition extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employee:setaccessbyposition';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set akses employee berdasarkan jabatan yang ada di matriks approval';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // ambil semua matriks approval untuk yang ada jabatan AOS
        $authorization_detail = AuthorizationDetail::join('employee_position', 'employee_position.id','=','authorization_detail.employee_position_id')
        ->where('employee_position.name','Area Operational Supervisor')
        ->get();
        // ambil employee id secara unique dari hasil diatas
        $aos_employee_ids = $authorization_detail->pluck('employee_id')->unique();
        // foreach ($aos_employee_ids as $id){
        //     print($id."\n");
        // }
        // set akses employee id untuk bisa full monitoring
        
        $monitoring_accesses = config('customvariable.monitoring_accesses');
        
        foreach($aos_employee_ids as $id){
            try{
                $access = EmployeeMenuAccess::where('employee_id',$id)->first();
                $access->monitoring  = $this->sumArrayGeometry($monitoring_accesses);
                $access->save();
            }catch(\Throwable $th){
                print('Error : '.$th->getMessage());
            }
            
        }

         // ambil semua matriks approval untuk yang ada jabatan BM
         $authorization_detail = AuthorizationDetail::join('employee_position', 'employee_position.id','=','authorization_detail.employee_position_id')
         ->where('employee_position.name','Business Manager')
         ->get();
         // ambil employee id secara unique dari hasil diatas
         $bm_employee_ids = $authorization_detail->pluck('employee_id')->unique();
        //  foreach ($bm_employee_ids as $id){
        //      print($id."\n");
        //  }
         // set akses employee id untuk bisa full monitoring plus download report (8)
         foreach($bm_employee_ids as $id){
             try{
                 $access = EmployeeMenuAccess::where('employee_id',$id)->first();
                 $access->monitoring  = $this->sumArrayGeometry($monitoring_accesses);
                 $access->reporting   = 0;
                 $access->save();
             }catch(\Throwable $th){
                 print('Error : '.$th->getMessage());
             }
             
         }

         // ambil semua matriks approval untuk yang ada jabatan OM
         $authorization_detail = AuthorizationDetail::join('employee_position', 'employee_position.id','=','authorization_detail.employee_position_id')
         ->where('employee_position.name','Operational Manager')
         ->get();
         // ambil employee id secara unique dari hasil diatas
         $om_employee_ids = $authorization_detail->pluck('employee_id')->unique();
         foreach ($om_employee_ids as $id){
             print($id."\n");
         }
         // set akses employee id untuk bisa full monitoring plus download report (8)
         foreach($om_employee_ids as $id){
             try{
                 $access = EmployeeMenuAccess::where('employee_id',$id)->first();
                 $access->monitoring  = $this->sumArrayGeometry($monitoring_accesses);
                 $access->reporting   = 8;
                 $access->save();
             }catch(\Throwable $th){
                 print('Error : '.$th->getMessage());
             }
             
         }
    }
    
    private function sumArrayGeometry($array){
        $value = 0;
        for($i = 0; $i < count($array); $i++){
            $value += pow(2,$i);
        }
        return $value;
    }
}
