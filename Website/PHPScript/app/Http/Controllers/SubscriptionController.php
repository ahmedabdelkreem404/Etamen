<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Session;
use validate;
use Sentinel;
use DB;
use App\Models\Subscription;
use App\Models\Subscriber;
use App\Models\Setting;
use App\Models\Doctors;
use Mail;
use DateTime;
use DateInterval;
use DataTables;
class SubscriptionController extends Controller
{
    
   public function show_subscription(){
       return view("admin.subscription.default");
   }
   
 
    public function subscriber_doc(){
       return view("admin.subscription.subscriber_doc");
   }
   
   public function show_subscriptiontable(){
           $subscription = Subscription::all();
       
           return DataTables::of($subscription)
           
            ->editColumn('month', function ($subscription) {
                return $subscription->month;
            })
            ->editColumn('amount', function ($subscription) {
                $getcurreny = explode("-",Setting::find(1)->currency);
                
                return $getcurreny[1].$subscription->price;
            })  
            ->editColumn('action', function ($subscription) {
                  $payurl=url('admin/edit_subscription_price',array('id'=>$subscription->id));
                return '<a href="'.$payurl.'" class=" btn btn-success" style="color:white !important" >'.__("message.Edit").'</a>';
            }) 
            ->make(true);
    }
    
    
    public function show_subscribetable(){
           $subscription = Subscriber::wherenotnull('doctor_id')->get();
           $data =array();
           foreach($subscription as $s){
               $ls = Doctors::find($s->doctor_id);
               if(isset($ls)){
                   $data[]=$s;
               }
           }
           $subscription = $data;
           return DataTables::of($subscription)
           
            ->editColumn('id', function ($subscription) {
                return $subscription->id;
            })
            ->editColumn('doctor_id', function ($subscription) {
                // return $subscription->doctor_id;
                return Doctors::find($subscription->doctor_id)?Doctors::find($subscription->doctor_id)->name:'';
            })
            ->editColumn('payment_type', function ($subscription) {
                
                if($subscription->payment_type == 1){
                    return  __("message.Braintree");
                }
                if($subscription->payment_type == 2){
                     return __("message.Bank Deposit");
                }else if($subscription->payment_type == 3){
                    return __("message.Razorpay");
                }else if($subscription->payment_type == 4){
                    return __("message.Paystack");
                }
            })
           ->editColumn('amount', function ($subscription) {
                $getcurreny = explode("-",Setting::find(1)->currency);
                return $getcurreny[1].$subscription->amount;
            }) 
           /* ->editColumn('transaction_id', function ($subscription) {
                return $subscription->transaction_id;
            })
            ->editColumn('description', function ($subscription) {
                return $subscription->description;
            })
            ->editColumn('subscription_id', function ($subscription) {
                return $subscription->subscription_id;
            })*/
            ->editColumn('status', function ($subscription) {
                // return $subscription->status;
                if($subscription->status == 1){
                    return __("message.Not Active");
                }
                if($subscription->status == 2){
                     return __("message.Active");
                }
                 if($subscription->status == 3){
                     return __("message.Expired");
                }
                if($subscription->status == 4){
                     return "Reject";
                }
                if($subscription->status == 6){
                     return "Disable";
                }
            })
            /*->editColumn('date', function ($subscription) {
                return $subscription->date;
            })*/
          
            ->editColumn('deposit_image', function ($subscription) {
                if($subscription->deposit_image != "" && $subscription->deposit_image != null){
                    return asset("public/upload/bank_receipt").'/'.$subscription->deposit_image;
                }else{
                   // return asset("public/upload/bank_receipt/images.png");
                }
                
            })
             
            ->editColumn('action', function ($subscription) {
                
               $view = __("message.View");
                $payurl=url('admin/view_subscription_price',array('id'=>$subscription->id));
                // return '<a href="'.$payurl.'" class=" btn btn-success" style="color:white !important" >'.$view.'</a>';
                $bt="";
                $ex="";
                $rx =""; 
                $active =""; 
                $disable =""; 
                if($subscription->status == 2)
                {
                    $disable ='<a onclick="disable_order('.$subscription->id.',6)" rel="tooltip"  class="btn btn-sm btn-warning" >Disable</a>';
                }
                if($subscription->status == 6)
                {
                    $active ='<a onclick="active_order('.$subscription->id.',2)" rel="tooltip" class="btn btn-sm btn-success" >Active</a>';
                }
                return '<a href="'.$payurl.'" class=" btn btn-sm btn-success" style="color:white !important" >'.$view.'</a> '.$disable.$active; 
                
            }) 
            ->make(true);
    }
    
     public function disable_order(Request $request)
    {
      $data = Subscriber::find($request->get("record_id"));
        $data->status = $request->get("status");
        $data->save();
    }

    public function active_order(Request $request)
    {
      $data = Subscriber::find($request->get("record_id"));
        $data->status = $request->get("status");
        $data->save();
    }
    
    public function view_subscription_price($id)
    {
       $data = Subscriber::find($id);
       $data->doctor_id = Doctors::find($data->doctor_id)?Doctors::find($data->doctor_id)->name:'';
       $data->subscription_id = Subscription::find($data->subscription_id)?Subscription::find($data->subscription_id)->month:'';
       return view("admin.subscription.view_subscriber",compact("data","id"));
    }
   
   public function edit_subscription_price($id){
       $data = Subscription::find($id);
       
       return view("admin.subscription.save",compact("data","id"));
   }
   
   public function update_subscriptio_price(Request $request){
        $store = Subscription::find($request->get("id"));
     	if($store){
     	    $store->price=$request->get("price");
         	$store->save();
         	Session::flash('message',__("message.Subscription Price Update Successfully")); 
            Session::flash('alert-class', 'alert-success');
            return redirect()->route("Subscription");
     	}else{
     	    	Session::flash('message',__("message.something getting wrong"));
                Session::flash('alert-class', 'alert-danger');
                return redirect()->route("Subscription");
     	}
     	
   }
   
   


}
