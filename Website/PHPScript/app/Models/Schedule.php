<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $table = 'doctor_schedule';
    protected $primaryKey = 'id';

    public function getslotls(){
    	return $this->hasmany("App\Models\SlotTiming",'schedule_id','id');
    }
}
?>

