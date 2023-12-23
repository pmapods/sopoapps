<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileCompletement extends Model
{
    use SoftDeletes;
    protected $table = 'file_completement';
    protected $primaryKey = 'id';

    public function file_category(){
        return $this->belongsTo(FileCategory::class);
    }
}
