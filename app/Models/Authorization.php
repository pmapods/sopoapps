<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Authorization extends Model
{
    protected $table = 'authorization';
    protected $primaryKey = 'id';
    protected $appends = ['salespoint_name'];

    public function salespoint()
    {
        try {
            return $this->belongsTo(SalesPoint::class);
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function getSalespointNameAttribute()
    {
        if (is_numeric($this->salespoint_id)) {
            return $this->salespoint->name;
        } else {
            return ucwords($this->salespoint_id);
        }
    }

    public function authorization_detail()
    {
        return $this->hasMany(AuthorizationDetail::class);
    }

    public function form_type_name()
    {
        switch ($this->form_type) {
            case 0:
                return 'PO Sewa';
                break;
            case 1:
                return 'PO Jual';
                break;
            case 2:
                return 'PO Custom';
                break;
            default:
                return 'form_type_undefined';
                break;
        }
    }

    public function list()
    {
        // id
        // as_text
        // name
        $data = [];
        foreach ($this->authorization_detail as $list) {
            $authorlist = [
                "id" => $list->employee->id,
                "as_text" => $list->sign_as,
                "position_id" => $list->employee_position_id,
                "position" => $list->employee_position->name,
                "name" => $list->employee->name,
            ];
            array_push($data, $authorlist);
        }
        return json_encode($data);
    }
}
