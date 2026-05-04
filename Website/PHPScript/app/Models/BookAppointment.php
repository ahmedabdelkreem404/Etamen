<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookAppointment extends Model
{
    protected $table = 'book_appointment';
    protected $primaryKey = 'id';

     public function doctorls(){
     	return $this->hasone("App\Models\Doctors",'id','doctor_id');
     }

      public function patientls(){
     	return $this->hasone("App\Models\Patient",'id','user_id');
     }
  
}
?>