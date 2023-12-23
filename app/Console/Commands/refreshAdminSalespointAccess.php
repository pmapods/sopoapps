<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmployeeLocationAccess;
use App\Models\SalesPoint;

class refreshAdminSalespointAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'access:refreshadmin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'refresh salespoint admin location access';

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
        foreach(EmployeeLocationAccess::where('employee_id',1)->get() as $access){
            $access->delete();
        }
        // kasih full akses untuk ke seluruh area
        foreach(SalesPoint::all() as $salespoint){
            $newAccess = new EmployeeLocationAccess;
            $newAccess->employee_id = 1;
            $newAccess->salespoint_id = $salespoint->id;
            $newAccess->save();
        }
    }
}
