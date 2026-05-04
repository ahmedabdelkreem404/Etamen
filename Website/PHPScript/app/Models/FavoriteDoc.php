<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FavoriteDoc extends Model
{
    protected $table = 'favorite_doctors';
    protected $primaryKey = 'id';

     public function doctorls(){
     	return $this->hasone("App\Models\Doctors",'id','doctor_id');
     }
}
?>