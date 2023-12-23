<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PrDetail extends Model
{
    use SoftDeletes;
    protected $table = 'pr_detail';
    protected $primaryKey = 'id';
    protected $appends = ['asset_numbers_list_text'];

    public function pr(){
        return $this->belongsTo(Pr::class);
    }

    public function ticket_item(){
        return $this->belongsTo(TicketItem::class);
    }

    public function asset_numbers_array(){
        try{
            $list = (array) json_decode($this->asset_number);
            return $list;
        }catch(\Throwable $th){
            return [];
        }
    }

    public function getAssetNumbersListTextAttribute(){
        return implode(', ', $this->asset_numbers_array());
    }
}
