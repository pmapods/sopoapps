<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HOBudgetUpload extends Model
{
    use SoftDeletes;
    protected $table      = 'ho_budget_upload';
    protected $primaryKey = 'id';
    protected $appends = ['all_pending_quota','all_used_quota'];

    public function budget_upload(){
        return $this->belongsTo(BudgetUpload::class);
    }

    public function getQty($month){
        $values = collect(json_decode($this->values));
        $qty = $values->where('months',$month)->first()->qty ?? -1;
        return $qty;
    }

    public function getValue($month){
        $values = collect(json_decode($this->values));
        $value = $values->where('months',$month)->first()->value;
        return $value;
    }

    
    public function getPendingQuota($month){
        $count = 0;
        $budget_upload_id = $this->budget_upload_id;
        $tickets = Ticket::where('budget_upload_id', $budget_upload_id)
            ->whereMonth('created_at', '=', $month)
            ->whereNotIn('status',[-1,0])
            ->get();
        foreach($tickets as $ticket){
            foreach($ticket->ticket_item->where('isCancelled',false)->whereNotNull('ho_budget_id') as $ticket_item){
                $code = $ticket_item->ho_budget->code;
                // jika kode sesuai dengan budget maka tambahin jumlahnya
                if($code == $this->code){
                    $count  += $ticket_item->count;
                } 
            }
        }
        return $count-$this->getUsedQuota($month);
    }

    // get list of pending quota each month
    public function getAllPendingQuotaAttribute(){
        $list = [];
        for($i = 1; $i <= 12; $i++){
            array_push($list, $this->getPendingQuota($i));
        }
        return $list;
    }

    public function getUsedQuota($month){
        // TODO
        $count = 0;
        return $count;
    }

    // get list of used quota each month
    public function getAllUsedQuotaAttribute(){
        $list = [];
        for($i = 1; $i <= 12; $i++){
            array_push($list, $this->getUsedQuota($i));
        }
        return $list;
    }

    public function getActualQty($month){
        $count = 0;
        $budget_upload_id = $this->budget_upload_id;
        // cek hanya ticket yang status pengadaannya sudah selesai
        $tickets = Ticket::where('budget_upload_id', $budget_upload_id)
            ->whereMonth('created_at', '=', $month)
            ->where('status',7)->get();
        foreach($tickets as $ticket){
            foreach($ticket->po as $po){
                foreach($po->po_detail as $po_detail){
                    // cek po berdasarkan nama
                    $budget_item_name = $this->name;
                    $po_item_name = $po_detail->item_name;
                    // ubah semua ke huruf kecil lalu hilangkan spasi
                    // ceknya menggunakan metode include apakan budget_item_name include di po_item_name
                    $budget_item_name = $this->convertText($budget_item_name);
                    $po_item_name = $this->convertText($po_item_name);
                    if(str_contains($po_item_name,$budget_item_name)){
                        $count += $po_detail->qty;
                    }
                }
            }
        }
        return $count;
    }

    public function getQtyGroupByValue($month){
        $list = [];
        $budget_upload_id = $this->budget_upload_id;
        // cek hanya ticket yang status pengadaannya sudah selesai
        $tickets = Ticket::where('budget_upload_id', $budget_upload_id)
            ->whereMonth('created_at', '=', $month)
            ->where('status',7)->get();
        foreach($tickets as $ticket){
            foreach($ticket->po as $po){
                foreach($po->po_detail as $po_detail){
                    // cek po berdasarkan nama
                    $budget_item_name = $this->name;
                    $po_item_name = $po_detail->item_name;
                    // ubah semua ke huruf kecil lalu hilangkan spasi
                    // ceknya menggunakan metode include apakan budget_item_name include di po_item_name
                    $budget_item_name = $this->convertText($budget_item_name);
                    $po_item_name = $this->convertText($po_item_name);
                    if(str_contains($po_item_name,$budget_item_name)){
                        $new = new \stdClass();
                        $new->qty = $po_detail->qty;
                        $new->value = $po_detail->item_price;
                        array_push($list,$new);
                    }
                }
            }
        }
        // Sample Data
        // $list = [];
        // if($month == 1){
        //     // januari 
        //     $list = [
        //         ["qty"=>'1', "value"=>100000],
        //         ["qty"=>'2', "value"=>200000],
        //     ];
        // }else{
        //     // selain bulan januari
        //     $list = [
        //         ["qty"=>'1', "value"=>100000],
        //         ["qty"=>'2', "value"=>110000],
        //         ["qty"=>'1', "value"=>100000],
        //         ["qty"=>'2', "value"=>130000],
        //         ["qty"=>'1', "value"=>100000],
        //         ["qty"=>'3', "value"=>150000],
        //     ];
        // }
        $list = collect($list);
        $listGroupbyValue = $list->groupBy("value");
        return $listGroupbyValue->toArray();
    }

    public function convertText($string){
        // hilangkan spasi
        $string =  str_replace(" ","",$string);
        $string =  strtolower($string);
        return $string;
    }
}
