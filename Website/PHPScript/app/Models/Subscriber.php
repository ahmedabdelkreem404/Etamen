<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    protected $table = 'subscriber';
    protected $primaryKey = 'id';

        
    public function Doctors(){
        return $this->hasone("App\Models\Doctors",'id','doctor_id');
    }

    public function Subscription(){
        return $this->hasone("App\Models\Subscription",'id','subscription_id');
    }

}
?>

