<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'review';
    protected $primaryKey = 'id';

    public function patientls(){
     	return $this->hasone("App\Models\Patient",'id','user_id');
    }
    public function doctorls(){
     	return $this->hasone("App\Models\Doctors",'id','doc_id');
    }
}
?>