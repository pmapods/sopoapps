<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use DB;

class refreshPOSAPElogTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sap:refreshpoelogtable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh data di table po sap e log';

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
        sleep(5);
        $current_time = now();
        try {
            DB::beginTransaction();
            $curl = curl_init();
            switch (config('app.env')) {
                case 'local':
                    // development
                    // $po_url = "http://103.111.82.19:8000/sap/bc/zrvpods?sap-client=110&pgmna=zmmr0002&s_ebeln=";
                    // $po_url = "http://103.111.82.20:8000/sap/bc/zrvpods?sap-client=200&pgmna=zmmr0002&s_ebeln=";
                    $po_url = "http://103.111.82.21:8000/sap/bc/zrvpods?sap-client=300&pgmna=zmmr0002";
                    break;
                case 'development':
                    //  QAS
                    // $po_url = "http://103.111.82.20:8000/sap/bc/zrvpods?sap-client=200&pgmna=zmmr0002&s_ebeln=";
                    $po_url = "http://103.111.82.21:8000/sap/bc/zrvpods?sap-client=300&pgmna=zmmr0002";
                    break;
                case 'production':
                    // Production
                    $po_url = "http://103.111.82.21:8000/sap/bc/zrvpods?sap-client=300&pgmna=zmmr0002";
                    break;
                default:
                    $po_url = "";
                    break;
            }
            curl_setopt_array($curl, array(
                CURLOPT_URL => $po_url,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_FAILONERROR => true,
                CURLOPT_HTTPHEADER => array(
                    'Cookie: sap-usercontext=sap-client=110'
                ),
            ));

            $response = curl_exec($curl);
            if (curl_errno($curl)) {
                $error_msg = curl_error($curl);
            }
            curl_close($curl);
            if (isset($error_msg)) {
                throw new \Exception($error_msg);
            } else {
                $response = json_decode($response);
                foreach ($response as $key => $item) {
                    DB::table('po_sap_e_log')->insert([
                        [
                            'data' => json_encode($item),
                            'date_eta' => $item->eindt,
                            'created_at' => $current_time,
                            'updated_at' => $current_time,
                        ]
                    ]);
                }
            }
            DB::commit();
            Log::info('PO SAP E LOG REFRESH SUCCESS');
            print('PO SAP E LOG REFRESH SUCCESS');
        } catch (\Exception $ex) {
            DB::rollback();
            print('PO SAP E LOG REFRESH ERROR : ' . $ex->getMessage() . "(" . $ex->getLine() . ")");
            Log::error('PO SAP E LOG REFRESH ERROR : ' . $ex->getMessage() . "(" . $ex->getLine() . ")");
        }
    }
}
