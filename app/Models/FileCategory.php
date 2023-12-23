<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileCategory extends Model
{
    protected $table = 'file_category';
    protected $primaryKey = 'id';

    public function file_completements(){
        return $this->hasMany(FileCompletement::class);
    }
}
