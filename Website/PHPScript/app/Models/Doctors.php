<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctors extends Model
{
    protected $table = 'doctors';
    protected $primaryKey = 'id';
  
     public function departmentls(){
     	return $this->hasone("App\Models\Services",'id','department_id');
     }
   
     public function reviewls(){
     	return $this->hasmany("App\Models\Review",'doc_id','id');
     }
}
?>