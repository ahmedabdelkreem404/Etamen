<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Session;
use validate;
use Sentinel;
use DB;
use DataTables;
use App\Models\Services;
use App\Models\Doctors;
use App\Models\Setting;
use App\Models\Review;
use App\Models\Newsletter;
use App\Models\Schedule;
use App\Models\BookAppointment;
use App\Models\SlotTiming;
use App\Models\FavoriteDoc;
use App\Models\Contact;
use App\Models\Patient;
use App\Models\Resetpassword;
use App\Models\About;
use App\Models\Privecy;
use App\Models\Doctor_Hoilday;

use App\Models\PaymentGatewayDetail;
use App\Models\PrivecyApp;
use App\Models\DataDeletion;
use Mail;
class FrontController extends Controller
{

    public function showhome(){
       $doctor= DB::table( 'doctors' )
                        ->join( 'review', 'review.doc_id', '=', 'doctors.id' )
                        ->groupBy( 'doctors.id' )
                        ->select( 'doctors.id', DB::raw( 'AVG( review.rating ) as avgratting' ) )
                        ->orderby('id','DESC')
		   				->where("is_approve","1")
                        ->take(8)
                        ->get();
        $main_array=array();
       foreach ($doctor as $k) {
          $ls=Doctors::find($k->id);
          $ls->avgratting=Review::where('doc_id',$k->id)->avg('rating');
          $ls->totalreview=count(Review::where('doc_id',$k->id)->get());
          if(!empty(Session::get("user_id"))&&Session::get('role_id')=='1'){
            $lsfav=FavoriteDoc::where("doctor_id",$k->id)->where("user_id",Session::get("user_id"))->first();
            if($lsfav){
                $ls->is_fav=1;
            }else{
                $ls->is_fav=0;
            }
            
          }else{
            $ls->is_fav=0;
          }
          
          $main_array[]=$ls;
       }
       

       $setting=Setting::find(1);
       $department=Services::take(8)->get();
       return view('user.home')->with("department",$department)->with("doctorlist",$main_array)->with("setting",$setting);
    }
    
     public function deposit_payment(Request $request)
    {
        // dd($request);
        // die("AA");
        if($request->input('payment_type')==2)
        {

            $id = Session::get('user_id');
            $sub_amout=Subscription::where('id',$request->get("sub_plan"))->first();

            $data=new Subscriber();
            $data->doctor_id=$id;
            $data->subscription_id=$request->get("sub_plan");
            $data->payment_type = $request->input('payment_type');
            $data->amount = $sub_amout->price;
            $data->date= $this->getsitedateall();
            $data->description=$request->get("description");
            $data->status ="1";
            if($request->hasFile('file'))
            {
                $file = $request->file('file');
                $filename = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension() ?: 'png';
                $folderName = '/upload/bank_receipt/';
                $picture = time() . '.' . $extension;
                $destinationPath = public_path() . $folderName;
                $request->file('file')->move($destinationPath, $picture);
                $data->deposit_image =$picture;
            }
            $data->save();
            if($data){

                    Session::flash('message',"Subscription Book Successfully");
                    Session::flash('alert', 'sucess');

            }else {
                    Session::flash('message',"Something Getting Worng");
                    Session::flash('alert', 'danger');
            }

            return redirect()->back();
        }

    }

    public function braintree_payment(Request $request)
    {

        if($request->input('payment_type')==1)
        {
            $id = Session::get('user_id');
            $sub_amout=Subscription::where('id',$request->get("sub_plan"))->first();

            $gateway = new \Braintree\Gateway([
                'environment' => env('BRAINTREE_ENV'),
                'merchantId'  => env('BRAINTREE_MERCHANT_ID'),
                'publicKey'   => env('BRAINTREE_PUBLIC_KEY'),
                'privateKey'  => env('BRAINTREE_PRIVATE_KEY')
             ]);
            $nonce = $request->get("payment_method_nonce");
            $result = $gateway->transaction()->sale([
                          'amount' => $sub_amout->price,
                          'paymentMethodNonce' => $nonce,
                          'options' => [
                              'submitForSettlement' => true
                          ]
                      ]);
            if ($result->success) {
                  $transaction = $result->transaction;
                  DB::beginTransaction();
                  try {

                            $data=new Subscriber();
                            $data->doctor_id=$id;
                            $data->payment_type = $request->input('payment_type');
                            $data->amount = $sub_amout->price;
                            $data->date= $this->getsitedateall();
                            $data->subscription_id=$request->get("sub_plan");

                            $data->status ="2";
                            $data->transaction_id=$transaction->id;
                            $data->save();

                            DB::commit();
                            Session::flash('message',"Subscription Book Successfully");
                            Session::flash('alert', 'sucess');

                        }catch (\Exception $e) {
                              DB::rollback();
                                /*$response['success']="0";
                                $response['register']=$e;*/
                                Session::flash('message',"Something Getting Worng");
                                Session::flash('alert', 'danger');
                        }
                 }else{
                        $errorString = "";
                            foreach($result->errors->deepAll() as $error) {
                                $errorString .= 'Error: ' . $error->code . ": " . $error->message . "\n";
                            }
                       /* $response['success']="0";
                        $response['register']=$errorString;*/
                        Session::flash('message',"Something Getting Worng");
                        Session::flash('alert', 'danger');

                }

            return redirect()->back();

        }
    }


 public function postforgotpassword(Request $request){
                      $checkmobile=Patient::where("email",$request->get("email"))->first();
                      $checkdoctor = Doctors::where("email",$request->get("email"))->first();
                      if($checkmobile){
                          $code=mt_rand(100000, 999999);
                          $store=array();
                          $store['email']=$checkmobile->email;
                          $store['name']=$checkmobile->name;
                          $store['code']=$code;
                          $add=new ResetPassword();
                          $add->user_id=$checkmobile->id;
                          $add->code=$code;
                          $add->type=1;
                          $add->save();
                          try {
                                  Mail::send('email.forgotpassword', ['user' => $store], function($message) use ($store){
                                    $message->to($store['email'],$store['name'])->subject(__("message.System Name"));
                                });
                          } catch (\Exception $e) {
                          }
                          
                           Session::flash('message',__("message.Mail Send Successfully")); 
                           Session::flash('alert', 'sucess');
                           
                      }elseif($checkdoctor){
                           $code=mt_rand(100000, 999999);
                          $store=array();
                          $store['email']=$checkdoctor->email;
                          $store['name']=$checkdoctor->name;
                          $store['code']=$code;
                          $add=new ResetPassword();
                          $add->user_id=$checkdoctor->id;
                          $add->code=$code;
                          $add->type=2;
                          $add->save();
                          try {
                                  Mail::send('email.forgotpassword', ['user' => $store], function($message) use ($store){
                                    $message->to($store['email'],$store['name'])->subject(__("message.System Name"));
                                });
                          } catch (\Exception $e) {
                          }
                          
                           Session::flash('message',__("message.Mail Send Successfully")); 
                           Session::flash('alert', 'sucess');
                      }else{
                            Session::flash('message',__("message.error mail sending")); 
                            Session::flash('alert', 'danger');   
                          
                      }   
                      return redirect()->back();
    }
    public function addnewsletter(Request $request){
		$email = $request->get("email");
        $getemail=Newsletter::where("email",$email)->first();
        if(empty($getemail)){
            $store=new Newsletter();
            $store->email=$email;
            $store->save();
        }        
        return "done";
    }

    public function viewspecialist(){
       $setting=Setting::find(1);
       $department=Services::all();
       return view('user.viewspecialist')->with("department",$department)->with("setting",$setting);
    }
    
    



     public function rattinglinescal($id){
        $totalreview=count(Review::where("doc_id",$id)->get());
        if($totalreview!=0){
           $str5=0;
           $str4=0;
           $str3=0;
           $str2=0;
           $str1=0;
           $str5=count(Review::where("doc_id",$id)->where("rating",5)->get())*100/$totalreview;
           $str4=count(Review::where("doc_id",$id)->where("rating",4)->get())*100/$totalreview;
           $str3=count(Review::where("doc_id",$id)->where("rating",3)->get())*100/$totalreview;
           $str2=count(Review::where("doc_id",$id)->where("rating",2)->get())*100/$totalreview;
           $str1=count(Review::where("doc_id",$id)->where("rating",1)->get())*100/$totalreview;
           return array("start5"=>$str5,"start4"=>$str4,"start3"=>$str3,"start2"=>$str2,"start1"=>$str1);
        }else{
           return array("start5"=>0,"start4"=>0,"start3"=>0,"start2"=>0,"start1"=>0);
        }
     }



    public function viewdoctor($id){
        $data=Doctors::with('departmentls')->find($id);
        if($data){
            $data->reviewslist=Review::with('patientls')->where("doc_id",$data->id)->get();
            $data->avgratting=Review::where("doc_id",$data->id)->avg('rating');
            $data->totalreview=count(Review::where("doc_id",$data->id)->get());
            $data->startrattinglines=$this->rattinglinescal($data->id);
        }else{
			return redirect("/");
		}
        
        
        $day=date('N',strtotime(date("Y-m-d")))-1;
        $datasc=Schedule::with('getslotls')->where("doctor_id",$id)->where("day_id",$day)->get();
        $main=array();
        if(count($datasc)>0){
            foreach ($datasc as $k) {
                $slotlist=array();
                $slotlist['id']=$k->id;
                $slotlist['title']=$k->start_time." - ".$k->end_time;
                if(count($k->getslotls)>0){
                  foreach ($k->getslotls as $b) {
                      $ka=array();
                      $getappointment=BookAppointment::where("date",date("Y-m-d"))->where("slot_id",$b->id)->first();
                      $ka['id']=$b->id;
                      $ka['name']=$b->slot;
                      if($getappointment){
                          $ka['is_book']='1';
                      }else{
                          $ka['is_book']='0';
                      }
                      $slotlist['slottime'][]=$ka;
                      
                  }
              }
              $main[]=$slotlist;
              
            } 
        }
       
        $setting=Setting::find(1);
        $gateway = new \Braintree\Gateway([
                      'environment' => env('BRAINTREE_ENV'),
                      'merchantId'  => env('BRAINTREE_MERCHANT_ID'),
                      'publicKey'   => env('BRAINTREE_PUBLIC_KEY'),
                      'privateKey'  => env('BRAINTREE_PRIVATE_KEY')
                 ]);
        $token=$gateway->ClientToken()->generate();
        $date = $this->getsitedate();
        
        $arr = array();
         $data1 = PaymentGatewayDetail::all();       
         foreach ($data1 as $k) {
            $arr[$k->gateway_name."_".$k->key] = $k->value;
         }
         
        $getdoctorhoilday = Doctor_Hoilday::where("start_date","<=",$date)->where("end_date",">=",$date)->where("doctor_id",$id)->first();
        return view("user.viewdoctor")->with("data",$data)->with("setting",$setting)->with("schedule",$main)->with("token",$token)->with("getdoctorhoilday",$getdoctorhoilday)->with("paymentdetail",$arr);
    }

    public function searchdoctor(Request $request){
        $setting=Setting::find(1);
        $services=Services::all();
        $term=$request->get("term");
        $type=$request->get("type");
        if(!empty($term)&&!empty($type)){//11
            $doctorslist=Doctors::with('departmentls')->where("department_id",$type)->Where('name', 'like', '%' . $term . '%')->where("is_approve","1")->paginate(10);
        }else if(!empty($term)&&empty($type)){//10
            $doctorslist=Doctors::with('departmentls')->where("is_approve","1")->Where('name', 'like', '%' . $term . '%')->paginate(10);
        }else if(empty($term)&&!empty($type)){//01
            $doctorslist=Doctors::with('departmentls')->where("is_approve","1")->where("department_id",$type)->paginate(10);
        }else{//00
            $doctorslist=Doctors::with('departmentls')->where("is_approve","1")->paginate(10);
        }

          if(!empty($term)&&!empty($type)){//11
            $doctorslistmap=Doctors::with('departmentls')->where("is_approve","1")->where("department_id",$type)->Where('name', 'like', '%' . $term . '%')->get();
        }else if(!empty($term)&&empty($type)){//10
            $doctorslistmap=Doctors::with('departmentls')->where("is_approve","1")->Where('name', 'like', '%' . $term . '%')->get();
        }else if(empty($term)&&!empty($type)){//01
            $doctorslistmap=Doctors::with('departmentls')->where("is_approve","1")->where("department_id",$type)->get();
        }else{//00
            $doctorslistmap=Doctors::with('departmentls')->where("is_approve","1")->get();
        }


           
        foreach ($doctorslist as $k) {
            $k->avgratting=Review::where('doc_id',$k->id)->avg('rating');
            $k->totalreview=count(Review::where('doc_id',$k->id)->get());
            if(!empty(Session::get("user_id"))&&Session::get('role_id')=='1'){
              $lsfav=FavoriteDoc::where("doctor_id",$k->id)->where("user_id",Session::get("user_id"))->first();
              if($lsfav){
                  $k->is_fav=1;
              }else{
                  $k->is_fav=0;
              }
              
            }else{
              $k->is_fav=0;
            }
        }   
       
        return view("user.searchdoctor")->with("services",$services)->with("setting",$setting)->with("doctorlist",$doctorslist)->with("term",$term)->with("type",$type)->with("doctorslistmap",$doctorslistmap);
    }

    public function contactus(){
        $setting=Setting::find(1);
        return view("user.contactus")->with("setting",$setting);
    }
    
    public function privacy_policy(){
        $setting=Setting::find(1);
        return view("user.privacy_policy")->with("setting",$setting);
    }

    public function aboutus(){
        $setting=Setting::find(1);
        $data=About::find(1);
        return view("user.aboutus",compact('setting','data'));
    }

    public function patientlogin(){
        $setting=Setting::find(1);
        return view("user.patient.login")->with("setting",$setting);
    }

    public function patientregister(){
        $setting=Setting::find(1);
        return view("user.patient.register")->with("setting",$setting);
    }

    public function forgotpassword(){
       $setting=Setting::find(1);
       return view("user.patient.forgot")->with("setting",$setting);
    }

    public function doctorlogin(){
       $setting=Setting::find(1);
       return view("user.doctor.login")->with("setting",$setting);
    }

    public function doctorregister(){
       $setting=Setting::find(1);
       return view("user.doctor.register")->with("setting",$setting);
    }

    public function getslotlist(Request $request){
        $data=SlotTiming::where("schedule_id",$request->get("s_id"))->get();
        $date = $request->get("date");
        $getdoctorhoilday = Doctor_Hoilday::where("start_date","<=",$date)->where("end_date",">=",$date)->where("doctor_id",$request->get("doctor_id"))->first();
        if(empty($getdoctorhoilday)){
            foreach ($data as $k) {
                      $getappointment=BookAppointment::where("date",date("Y-m-d",strtotime($request->get("date"))))->where("slot_id",$k->id)->first();
                      if($getappointment){
                          $k->is_book='1';
                      }else{
                          $k->is_book='0';
                      }
            }
            return json_encode($data);
        }else{
            return 0;
        }
        
    }

    public function getschedule(Request $request){
      $day=date('N',strtotime($request->get("date")))-1;
      $datasc=Schedule::with('getslotls')->where("doctor_id",$request->get("doctor_id"))->where("day_id",$day)->get();
      return json_encode($datasc);
    }

    public function savecontact(Request $request){
        $store=new Contact();
        $store->name=$request->get("name");
        $store->email=$request->get("email");
        $store->phone=$request->get("phone");
        $store->subject=$request->get("subject");
        $store->message=$request->get("message");
        $store->save();
        Session::flash('message',__('message.Thank you for getting in touch!')); 
        Session::flash('alert', 'danger');
        return redirect()->back();
    }
    
    public function privacy_front_app(){
        $data=About::find(1);
        $setting=Setting::find(1);
        return view('user.privacy',compact('data','setting'));
    }
    
    public function accountdeletion(){
        $data=About::find(1);
        $setting=Setting::find(1);
        return view('user.accountdeletion',compact('data','setting'));
    }

    public function about(){
      $data=About::find(1);
      $setting=Setting::find(1);
      return view('admin.about',compact('data','setting'));
    }
    
    public function admin_privacy(){
      $data=About::find(1);
      $setting=Setting::find(1);
      return view('admin.terms',compact('data','setting'));
    }
    
    public function privacy_admin(){
      $data=About::find(1);
      $setting=Setting::find(1);
      return view('user.terms',compact('data','setting'));
    }
    
    public function app_privacy(){
      $data=About::find(1);
      $setting=Setting::find(1);
      return view('admin.privecy-app',compact('data','setting'));
    }
    
    public function data_deletion(){
      $data=About::find(1);
      $setting=Setting::find(1);
      return view('admin.data-deletion',compact('data','setting'));
    }
    
   public function edit_about(Request $request){
      $data=About::find(1);
       $setting=Setting::find(1);
       $data->about = $request->get('about');
       $data->save();
      return redirect('admin/about');
    }
    
    public function edit_terms(Request $request){
      $data=About::find(1);
      $setting=Setting::find(1);
      $data->trems = $request->get('trems');
       $data->save();
      return redirect('admin/Terms_condition');
    }
    
    public function edit_app_privacy(Request $request){
      $data=About::find(1);
      $setting=Setting::find(1);
      $data->privacy = $request->get('privacy');
       $data->save();
      return redirect('admin/app_privacy');
    }
    
    public function edit_data_deletion(Request $request){
      $data=About::find(1);
      $setting=Setting::find(1);
      $data->data_deletion = $request->get('data_deletion');
       $data->save();
      return redirect('admin/data_deletion');
    }
}
