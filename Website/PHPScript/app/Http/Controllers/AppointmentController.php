<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Session;
use validate;
use Sentinel;
use DB;
use App\Models\Doctors;
use App\Models\BookAppointment;
use App\Models\Patient;
use DataTables;
class AppointmentController extends Controller
{
    
    public function showappointment(){
         return view("admin.appointment.default");
    }
   
    public function appointmenttable(){
          $book =BookAppointment::where('is_completed','1')->get();
         
           return DataTables::of($book)
            ->editColumn('id', function ($book) {
                return $book->id;
            })
            ->editColumn('doctor_name', function ($book) {
                return isset($book->doctorls)?$book->doctorls->name:"";
            })
            ->editColumn('patient_name', function ($book) {
                return isset($book->patientls)?$book->patientls->name:"";
            })  
            ->editColumn('date', function ($book) {
                return $book->date." ".$book->slot_name;
            })  
            ->editColumn('phone', function ($book) {
                return $book->phone;
            })  
            ->editColumn('u_desc', function ($book) {

                return isset($book->user_description)?$book->user_description:"";
            })  
            ->editColumn('status', function ($book) {
                if($book->status=='1'){
                     return __("message.Received");
                }else if($book->status=='2'){
                     return __("message.Approved");
                }else if($book->status=='3'){
                     return __("message.In Process");
                }
                else if($book->status=='4'){
                     return __("message.Completed");
                }
                else if($book->status=='5'){
                     return __("message.Rejected");
                }else if($book->status=='6'){
                     return __("message.Refunded");
                }else{
                     return __("message.Absent");
                }
            }) 
            
            ->editColumn('action', function ($book) {
                if($book->status=='5'){
                  $payurl=url('admin/refundappointment',array('id'=>$book->id));
                  return '<a href="'.$payurl.'" class=" btn btn-success" style="color:white !important" >'.__("message.Refund").'</a>';
               }
            })
           
            ->make(true);
    }

    public function latsrappointmenttable(){
       $book =BookAppointment::with('doctorls','patientls')->where('is_completed','1')->where("date",date("Y-m-d"))->get();
           return DataTables::of($book)
            ->editColumn('id', function ($book) {
                return $book->id;
            })
            ->editColumn('doctor_name', function ($book) {
                return isset($book->doctorls)?$book->doctorls->name:"";
            })
            ->editColumn('patient_name', function ($book) {
                return isset($book->patientls)?$book->patientls->name:"";
            })  
            ->editColumn('date', function ($book) {
                return $book->date." ".$book->slot_name;
            })  
            ->editColumn('phone', function ($book) {
                return $book->phone;
            })  
            ->editColumn('u_desc', function ($book) {

                return isset($book->user_description)?$book->user_description:"";
            })  
            ->editColumn('status', function ($book) {
                if($book->status=='1'){
                     return __("message.Received");
                }else if($book->status=='2'){
                     return __("message.Approved");
                }else if($book->status=='3'){
                     return __("message.In Process");
                }
                else if($book->status=='4'){
                     return __("message.Completed");
                }
                else if($book->status=='5'){
                     return __("message.Rejected");
                }else if($book->status=='6'){
                     return __("message.Refunded");
                }else{
                     return __("message.Absent");
                }
            }) 
           
            ->make(true);
    }

    public function changeappstatus($id,$status){
        $data=BookAppointment::find($id);
        $data->status=$status;
        $data->save();
        if($status==3){//in process
            $msg=__("message.Appointment In Process");
        }elseif($status==4){//complete
            $msg=__("message.Appointment In Complete");
        }else{//reject
            $msg=__("message.Appointment In Reject");
        }
        Session::flash('message',$msg); 
        Session::flash('alert-class', 'alert-success');
        return redirect("admin/appointment");
    }

    public function show_refundappointment($id){            
            $data=BookAppointment::find($id);
            if($data){
                $result = $this->refunded_order_amount($data->payment_mode,$data->transaction_id,$data->consultation_fees);
                $data->status = 6;
                $data->save();
                $msg = __("message.Refund Successfully");
                Session::flash('message',$msg); 
                Session::flash('alert-class', 'alert-success');
                return redirect()->back();
            }else{
                Session::flash('message',__("message.something getting wrong")); 
                Session::flash('alert-class', 'alert-danger');
                return redirect()->back();
            }            
    }

     public function refunded_order_amount($method,$token,$amount){
       
        if($method=="braintree"){
                $gateway = new \Braintree\Gateway([
                    'environment' => env('BRAINTREE_ENV'),
                    'merchantId' => env('BRAINTREE_MERCHANT_ID'),
                    'publicKey' => env('BRAINTREE_PUBLIC_KEY'),
                    'privateKey' => env('BRAINTREE_PRIVATE_KEY')
                 ]);
                 $result = $gateway->transaction()->refund($token);
                 if($result->success==true){
                    return 1;
                 }else{
                    return 0;
                 }
        }else if($method=="stripe"){
                \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                $refund = \Stripe\Refund::create([
                    'charge' => $token,
                    'amount' => (int)($amount * 100),  // For 10 $
                ]);

        }else{
            return 1;
        }
    }

     public function notification($act){
      $data=array();
      if($act==1){
         $result=$this->haveOrdersNotification();
           $orderdata=$this->haveOrdersdata();
            if(isset($result)){
               $data = array(
                      "status" => http_response_code(),
                      "request" => "success",
                      "response" => array(
                      "message" => __("message.Request Completed Successfully"),
                      "total" => $result,
                      "orderdata"=>$orderdata
               )
             );
           }
           $updatenotify=$this->updatenotify();

      }
      else{
           $result=$this->haveOrdersNotification();
           $orderdata=$this->haveOrdersdata();
            if(isset($result)){
               $data = array(
                      "status" => http_response_code(),
                      "request" => "success",
                      "response" => array(
                      "message" => __("message.Request Completed Successfully"),
                      "total" => $result,
                      "orderdata"=>$orderdata
               )
             );
           }
       }
       return $data;
     }

     public function haveOrdersNotification(){
        $order=BookAppointment::where("notify",'1')->get();
        return count($order);
     }
      public function haveOrdersdata(){
        $order=BookAppointment::where("notify",'1')->get();
        return count($order);
     }

     public function updatenotify(){
      $order=BookAppointment::where("notify",'1')->get();
      foreach ($order as $k) {
         $k->notify='0';
         $k->save();
      }
      return "done";
     }
}
