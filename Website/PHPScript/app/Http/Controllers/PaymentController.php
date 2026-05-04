<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Session;
use validate;
use Sentinel;
use DB;
use App\Models\Doctors;
use App\Models\Services;
use App\Models\SlotTiming;
use App\Models\Schedule;
use App\Models\Complement_settlement;
use App\Models\Review;
use App\Models\Setting;
use App\Models\Patient;
use App\Models\BookAppointment;
use App\Models\Settlement;
use DataTables;
use App\Models\TokenData;
use App\Models\User;
use App\Models\Contact;
use App\Models\Newsletter;
use Mail;
use DateTime;
use DateInterval;
class PaymentController extends Controller
{
    
   public function show_pending_payment(){
       
       return view("admin.payment.pending");
   }
   
   public function show_news(){
       return view("admin.news");
   }
   
   public function show_contact_list(){
       return view("admin.contact");
   }
   
   public function sendnews(Request $request){
          $msg=$request->get("news");
          $getall=Newsletter::all();
          $setting=Setting::find(1);
          foreach($getall as $g){
              $data=array();
              $data['email']=$g->email;
              $data['msg']=$msg;
                try {
                      $result=Mail::send('email.news', ['user' => $data], function($message) use ($data){
                         $message->to($data['email'],'customer')->subject(__('messages.site_name'));
                      });
            
               } catch (\Exception $e) {
               }
        
          }
       Session::flash('message',__('messages.News Send Successfully'));
       Session::flash('alert-class', 'alert-success');
       return redirect()->back();
     }
     
   public function contact_list_table(){
            $book = Contact::all();
       
           return DataTables::of($book)
           ->editColumn('id', function ($book) {
                return $book->id;
            })
            ->editColumn('name', function ($book) {
                return $book->name;
            })
            ->editColumn('email', function ($book) {
                return $book->email;
            })
            ->editColumn('phone', function ($book) {
                return $book->phone;
            })
            ->editColumn('subject', function ($book) {
                return $book->subject;
            })
            ->editColumn('message', function ($book) {
                return $book->message;
            })
            
           
            ->make(true);
   }
   
   public function show_pendingpaymenttable(){
        $book =DB::table('settlement')->select( DB::raw('DISTINCT(doctor_id) as doctor_id '),DB::raw('sum(amount) as amount'),DB::raw('count(*) as total_booking') )->where("status",'0')->whereMonth("payment_date","<=",9)->groupBy('doctor_id')->get();
       
           return DataTables::of($book)
           
            ->editColumn('doctor_name', function ($book) {
                return Doctors::find($book->doctor_id)?Doctors::find($book->doctor_id)->name:'';
            })
            ->editColumn('amount', function ($book) {
                return $book->amount;
            })  
            ->editColumn('total_booking', function ($book) {
                return $book->total_booking;
            })  
            ->editColumn('action', function ($book) {
                  $payurl=url('admin/payamount',array('doctor_id'=>$book->doctor_id));
                return '<a href="'.$payurl.'" class=" btn btn-success" style="color:white !important" >'.__("message.pay amount").'</a>';
            }) 
           
            ->make(true);
   }
   
   public function show_payamount($doc_id){
        $book =DB::table('settlement')->select( DB::raw('DISTINCT(doctor_id) as doctor_id '),DB::raw('sum(amount) as amount'),DB::raw('count(*) as total_booking') )->where("status",'0')->whereMonth("payment_date","<=",9)->where("doctor_id",$doc_id)->groupBy('doctor_id')->first();
        $book->name = Doctors::find($book->doctor_id)?Doctors::find($book->doctor_id)->name:'';
        return view("admin.payment.addpay")->with("book",$book);
   }
   
   public function updatepayment(Request $request){
       
        $date = DateTime::createFromFormat('d', 15)->add(new DateInterval('P1M'));
        $store = new Complement_settlement();
        $store->translation_id = $request->get("translation_id");
        $store->amount = $request->get("amount");
        $store->date = $date->format('Y-m-d H:i:s');
        $store->doctor_id = $request->get("doctor_id");
        $store->save();
        DB::table('settlement')->where("status",'0')->whereMonth("payment_date","<=",9)->where("doctor_id",$request->get("doctor_id"))->update(["status"=>1,"settlement_id"=>$store->id]);
        
        Session::flash('message',__("message.Payment Store Successfully")); 
        Session::flash('alert-class', 'alert-success');
        return redirect('admin/pending_payment');
   }
   
   public function complete_payment(){
       return view("admin.payment.complete");
   }
   
   public function show_completepaymenttable(){
        $book =Complement_settlement::all();
       
           return DataTables::of($book)
           
            ->editColumn('doctor_name', function ($book) {
                return Doctors::find($book->doctor_id)?Doctors::find($book->doctor_id)->name:'';
            })
            ->editColumn('amount', function ($book) {
                return $book->amount;
            })  
            ->editColumn('date', function ($book) {
                return $book->date;
            })  
            ->editColumn('translation_id', function ($book) {
                return $book->translation_id;
            }) 
           
            ->make(true);
   }

}
