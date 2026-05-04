<?php

namespace App\Http\Controllers\API;
error_reporting(-1);
ini_set('display_errors', 'On');
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Session;
use validate;
use Sentinel;
use Response;
use Validator;
use DB;
use DataTables;
use Stripe\Stripe;
use Stripe\Charge;
use App\Models\User;
use App\Models\Services;
use App\Models\Review;
use App\Models\Doctors;
use App\Models\Patient;
use App\Models\TokenData;
use App\Models\Resetpassword;
use App\Models\BookAppointment;
use App\Models\SlotTiming;
use App\Models\Doctor_Hoilday;
use App\Models\Schedule;
use App\Models\Reportspam;
use App\Models\Settlement;
use Hash;
use Mail;
use DateTime;
use DateInterval;
class ApiControllernew extends Controller
{
   
  public function showsearchdoctor(Request $request){
        $response = array("status" => "0", "register" => "Validation error");
           $rules = [
                      'term' => 'required'                
                    ];                    
            $messages = array(
                      'term.required' => "term is required"
            );
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                  $message = '';
                  $messages_l = json_decode(json_encode($validator->messages()), true);
                  foreach ($messages_l as $msg) {
                         $message .= $msg[0] . ", ";
                  }
                  $response['msg'] = $message;
            } else {
                     $data=Doctors::Where('name', 'like', '%' . $request->get("term") . '%')->select("id","name","address","image","department_id")->paginate(10);
                     if($data){
                         
                         foreach ($data as $k) {
                             $dr=Services::find($k->department_id);
                             if($dr){
                                 $k->department_name=$dr->name;
                             }else{
                                 $k->department_name="";
                             }
                             $k->image=asset('public/upload/doctors').'/'.$k->image;
                            unset($data->department_id);
                         }
                        $response = array("status" =>1, "msg" => "Search Result","data"=>$data);
                     }else{
                        $response = array("status" =>0, "msg" => "No Result Found");
                     }
           }
           return json_encode($response, JSON_NUMERIC_CHECK);

   }

   public function nearbydoctor(Request $request){
       $response = array("status" => "0", "register" => "Validation error");
           $rules = [
                      'lat' => 'required',
                      'lon'=>'required'               
                    ];                    
            $messages = array(
                      'lat.required' => "lat is required",
                      'lon.required'=>'lon is requied'
            );
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                  $message = '';
                  $messages_l = json_decode(json_encode($validator->messages()), true);
                  foreach ($messages_l as $msg) {
                         $message .= $msg[0] . ", ";
                  }
                  $response['msg'] = $message;
            } else {
                      $lat = $request->get("lat");
                      $lon =  $request->get("lon");
                         
                      $data=DB::table("doctors")
                          ->select("doctors.id","doctors.name","doctors.address","doctors.department_id","doctors.image"
                              ,DB::raw("6371 * acos(cos(radians(" . $lat . ")) 
                              * cos(radians(doctors.lat)) 
                              * cos(radians(doctors.lon) - radians(" . $lon . ")) 
                              + sin(radians(" .$lat. ")) 
                              * sin(radians(doctors.lat))) AS distance"))
                              ->orderby('distance')->WhereNotNull("doctors.lat")->paginate(10);
                    
                     if($data){
                        
                         foreach ($data as $k) {
                             $department=Services::find($k->department_id);
                             $k->department_name=isset($department)?$department->name:"";
                             $k->image=asset("public/upload/doctors").'/'.$k->image;
                             unset($k->department_id);
                         }
                        $response = array("status" =>1, "msg" => "Search Result","data"=>$data);
                     }else{
                        $response = array("status" =>0, "msg" => "No Result Found");
                     }
                    
           }
           return json_encode($response, JSON_NUMERIC_CHECK);

   }

   public function postregisterpatient(Request $request){
        $response = array("success" => "0", "register" => "Validation error");
        $rules = [
            'phone' => 'required',
            'password'=>'required',
            'token' => 'required',
            'email'=>'required',
            'name'=>'required'
        ];
       
         $messages = array(
                  'phone.required' => "Mobile No is required",
                  'password.required' => "password is required",
                  'token.required' => "token is required",
                  'phone.unique'=>"Mobile Number Already Register",
                  'email.required'=>'Email is required',
                  'name.required'=>'name is required'
            );
       
        $validator = Validator::make($request->all(), $rules,$messages);

        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {
           $getuser=Patient::where("phone",$request->get("phone"))->first();
           if(empty($getuser)){//update token
                      $getemail=Patient::where("email",$request->get("email"))->first();
                          if($getemail){
                              $response['success']="0";
                              $response['register']="Email Id Already Register";
                          }
                          else{
                                  $inset=new Patient();
                                  $inset->phone=$request->get("phone");
                                  $inset->name=$request->get("name");
                                  $inset->password=$request->get("password");
                                  $inset->email=$request->get("email");
                                  $inset->save();
                                  $store=TokenData::where("token",$request->get("token"))->update(["user_id"=>$inset->id]);
                                  $response['success']="1";
                                  $response['register']=array("user_id"=>$inset->id,"name"=>$request->get("name"),"phone"=>$inset->phone,"email"=>$inset->email);
                          }
             
           }else{
                 $response['success']="0";
                 $response['register']="Mobile Number Already Register";
           }
           
        }
        return json_encode($response, JSON_NUMERIC_CHECK);
   }

   public function storetoken(Request $request){
        $response = array("success" => "0", "register" => "Validation error");
        $rules = [
            'type' => 'required',
            'token' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $response['register'] = "enter your data perfectly";
        } else {
              $store=new TokenData();
              $store->token=$request->get("token");
              $store->type=$request->get("type");
              $store->save();
              $response['success']="1";
              $response['headers']=array("Access-Control-Allow-Origin"=>"*","Access-Control-Allow-Credentials"=>true,"Access-Control-Allow-Headers"=>"Origin,Content-Type,X-Amz-Date,Authorization,X-Api-Key,X-Amz-Security-Token","Access-Control-Allow-Methods"=>"POST, OPTIONS,GET");
              $response['register']="Registered";
          
        }
        return json_encode($response, JSON_NUMERIC_CHECK);
   }
   
   public function getalldoctors(){
        $data = Doctors::take(26)->get();
        return Response::json($data);
   }
   
   public function showlogin(Request $request){
      $response = array("success" => "0", "register" => "Validation error");
        $rules = [
            'email' => 'required',
            'token' => 'required',
            "login_type" => 'required'
        ];
        if($request->input('login_type')=='1'){
              $rules['password'] = 'required';
        }
        if($request->input('login_type')=='2'||$request->input('login_type')=='3'||$request->input('login_type')=='4'){
              $rules['name']='required';
        }
        $messages = array(
                  'email.required' => "Email is required",
                  'password.required' => "password is required",
                  'token.required' => "token is required",
                  'login_type.required' => "login type is required",
                  'name.required' => "name is required"
        );       
        $validator = Validator::make($request->all(), $rules,$messages);

        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {

              if($request->input('login_type')=='1'){
                 $getuser=Patient::where("email",$request->get("email"))->where("password",$request->get("password"))->first();
                  if($getuser){//update token
                           $store=TokenData::where("token",$request->get("token"))->first();
                           if($store){
                               $store->user_id=$getuser->id;
                               $store->save();
                           }
                            $getuser->login_type = $request->get("login_type");
                            $getuser->save();
                           if($getuser->profile_pic!=""){
                              $image=asset("public/upload/profile").'/'.$getuser->profile_pic;
                           }else{
                               $image=asset("public/upload/profile/profile.png");
                           }
                           $response['success']="1";
                           $response['headers']=array('Access-Control-Allow-Origin'=>'*');
                           $response['register']=array("user_id"=>$getuser->id,"name"=>$getuser->name,"phone"=>$getuser->phone,"email"=>$getuser->email,"profile_pic"=>$image);
                   }
                   else{//in vaild user
                         $data=Patient::where("phone",$request->get("phone"))->first();
                         if($data){
                              $response['success']="0";
                              $response['register']="Invaild Password";
                         }else{
                              $response['success']="0";
                              $response['register']="Invaild Mobile No";
                         }
                       
                   }

              }else if($request->input('login_type')=='2' || $request->input('login_type')=='3' ||$request->input('login_type')=='4'){
                    $getuser=Patient::where("email",$request->get("email"))->first();
                    if($getuser){//update patient
                          $imgdata=$getuser->profile_pic;
                          $png_url = "";
                          if($request->get("image")!=""){
                            $png_url = "profile-".mt_rand(100000, 999999).".png";
                            $path = public_path().'/upload/profile/' . $png_url;
                            $content=$this->file_get_contents_curl($request->get("image"));
                            $savefile = fopen($path, 'w');
                            fwrite($savefile, $content);
                            fclose($savefile);
                            $img=public_path().'/upload/profile/' . $png_url;
                            $getuser->login_type = $request->get("login_type");
                            $getuser->profile_pic=$png_url;
                            $getuser->save();
                          }
                          if($imgdata!=$png_url && $imgdata!=""){
                              $image_path = public_path() ."/upload/profile/".$imgdata;
                                if(file_exists($image_path)&&$imgdata!="") {
                                    try {
                                          unlink($image_path);
                                    }catch(Exception $e) {}                        
                                }
                          }
                          $store=TokenData::where("token",$request->get("token"))->first();
                          if($store){
                               $store->user_id=$getuser->id;
                               $store->save();
                          }
                          if($getuser->profile_pic!=""){
                              $image=asset("public/upload/profile").'/'.$getuser->profile_pic;
                          }else{
                               $image=asset("public/upload/profile/profile.png");
                           }
                           $response['success']="1";
                           $response['headers']=array('Access-Control-Allow-Origin'=>'*');
                           $response['register']=array("user_id"=>$getuser->id,"name"=>$getuser->name,"phone"=>$getuser->phone,"email"=>$getuser->email,"profile_pic"=>$image);
                    }
                    else{//register patient
                         $getuser = new Patient();
                         $getuser->email = $request->get("email");
                         $getuser->name = $request->get("name");
                         $getuser->login_type = $request->get("login_type");
                         $png_url = "";
                         if($request->get("image")!=""){
                            $png_url = "profile-".mt_rand(100000, 999999).".png";
                            $path = public_path().'/upload/profile/' . $png_url;
                            $content=$this->file_get_contents_curl($request->get("image"));
                            $savefile = fopen($path, 'w');
                            fwrite($savefile, $content);
                            fclose($savefile);
                            $img=public_path().'/upload/profile/' . $png_url;
                            $getuser->profile_pic=$png_url;
                          }
                          $getuser->save();
                          $store=TokenData::where("token",$request->get("token"))->first();
                          if($store){
                               $store->user_id=$getuser->id;
                               $store->save();
                          }
                          if($getuser->profile_pic!=""){
                              $image=asset("public/upload/profile").'/'.$getuser->profile_pic;
                          }else{
                               $image=asset("public/upload/profile/profile.png");
                           }
                           $response['success']="1";
                           $response['headers']=array('Access-Control-Allow-Origin'=>'*');
                           $response['register']=array("user_id"=>$getuser->id,"name"=>$getuser->name,"phone"=>$getuser->phone,"email"=>$getuser->email,"profile_pic"=>$image);
                    }
              }else{
                 $data=Patient::where("phone",$request->get("phone"))->first();
                 if($data){
                      $response['success']="0";
                      $response['register']="Invaild Password";
                 }else{
                      $response['success']="0";
                      $response['register']="Invaild Mobile No";
                 }
              }
        }
        return json_encode($response, JSON_NUMERIC_CHECK);
   }
   
      public function file_get_contents_curl($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);       
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
   public function doctorregister(Request $request){
      $response = array("success" => "0", "register" => "Validation error");
        $rules = [
            'phone' => 'required',
            'password'=>'required',
            'email'=>'required',
            'name'=>'required',
            'token' =>'required'
        ];
       
         $messages = array(
                  'phone.required' => "Mobile No is required",
                  'password.required' => "password is required",
                  'token.required' => "token is required",
                  'email.required'=>'Email is required',
                  'name.required'=>'name is required'
            );
       
        $validator = Validator::make($request->all(), $rules,$messages);

        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {
           $getuser=Doctors::where("email",$request->get("email"))->first();
           if(empty($getuser)){//update token
                    $inset=new Doctors();
                    $inset->phoneno=$request->get("phone");
                    $inset->name=$request->get("name");
                    $inset->password=$request->get("password");
                    $inset->email=$request->get("email");
                    $inset->save();
                    $store=TokenData::where("token",$request->get("token"))->update(["doctor_id"=>$inset->id]);
                    $response['success']="1";
                    $response['register']=array("doctor_id"=>$inset->id,"name"=>$request->get("name"),"phone"=>$inset->phoneno,"email"=>$inset->email);
                        
             
           }else{
                 $response['success']="0";
                 $response['register']="Email Already Register";
           }
           
        }
        return json_encode($response, JSON_NUMERIC_CHECK);

   }

   public function doctorlogin(Request $request){
        $response = array("success" => "0", "register" => "Validation error");
        $rules = [
            'email' => 'required',
            'password'=>'required',
            'token' => 'required'
        ];
       
         $messages = array(
                  'email.required' => "Email is required",
                  'password.required' => "password is required",
                  'token.required' => "token is required"
            );
       
        $validator = Validator::make($request->all(), $rules,$messages);

        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {
           $getuser=Doctors::where("email",$request->get("email"))->where("password",$request->get("password"))->first();
          
           if($getuser){//update token
                   $store=TokenData::where("token",$request->get("token"))->first();
                   if($store){
                       $store->doctor_id=$getuser->id;
                       $store->save();
                   }
                   $response['success']="1";
                   $response['register']=array("doctor_id"=>$getuser->id,"name"=>$getuser->name,"phone"=>$getuser->phone,"email"=>$getuser->email);
              
           }
           else{//in vaild user
                 $data=Doctors::where("email",$request->get("email"))->first();
                 if($data){
                      $response['success']="0";
                      $response['register']="Invaild Password";
                 }else{
                      $response['success']="0";
                      $response['register']="Invaild Email";
                 }
               
           }
        }
        return json_encode($response, JSON_NUMERIC_CHECK);

   }

   public function getspeciality(){
          //$data=Services::select('id','name','icon')->paginate(10);
          $data =Services::select('id','name','icon')->get();
          if(count($data)>0){
              foreach ($data as $d) {
                 $d->total_doctors=count(Doctors::where("department_id",$d->id)->get());
                 $d->icon=asset("public/upload/services").'/'.$d->icon;
              }
              $response['success']="1";
              $response['register']="Speciality List";
              $response['data']=$data;
          }else{
              $response['success']="0";
              $response['register']="Speciality Not Found";              
          }

          return json_encode($response, JSON_NUMERIC_CHECK);

   }

   public function bookappointment(Request $request){
      $response = array("success" => "0", "register" => "Validation error");
        $rules = [
            'user_id' => 'required',
            'doctor_id'=>'required',
            'date' => 'required',
            'slot_id' => 'required',
            'slot_name' => 'required',
            'phone' => 'required',
            'user_description' => 'required',
            'payment_type'=>'required',
            'consultation_fees'=>'required'
        ];
       
        if($request->input('payment_type') == "2"){
            $rules['stripeToken'] = 'required';
        }
        if($request->input('payment_type') == "1"){
            $rules['payment_method_nonce'] = 'required';
        }
         $messages = array(
                  'user_id.required' => "user_id is required",
                  'doctor_id.required' => "doctor_id is required",
                  'date.required' => "date is required",
                  'slot_id.required' => "slot_id is required",
                  'slot_name.required' => "slot_name is required",
                  'phone.required' => "phone is required",
                  'user_description.required' => "user_description is required",
                  'payment_method_nonce.required'=>"payment_method_nonce is required",
                  'consultation_fees.requierd'=>"consultation_fees is required",
                  "payment_type.required"=>"Payment Type is Required",
                  "stripeToken.required"=>"stripeToken is required"
            );
       
        $validator = Validator::make($request->all(), $rules,$messages);

        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {
                  $getappointment=BookAppointment::where("date",$request->get("date"))->where("slot_id",$request->get("slot_id"))->first();
                  if($getappointment){
                          $response['success']="0";
                          $response['register']="Slot Already Booked";
                  }else{
                      
                        if($request->input('payment_type')==2){ 
                            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                                $unique_id = uniqid(); 
                                $charge = \Stripe\Charge::create(array(
                                       'description' => "Amount: ".$request->get("consultation_fees").' - '. $unique_id,
                                       'source' => $request->get("stripeToken"),                    
                                       'amount' => (int)($request->get("consultation_fees") * 100), 
                                       'currency' => env('STRIPE_CURRENCY')
                                ));

                                DB::beginTransaction();
                                  try {
                                      $date = DateTime::createFromFormat('d', 15)->add(new DateInterval('P1M'));
                                      $data=new BookAppointment();
                                      $data->user_id=$request->get("user_id");
                                      $data->doctor_id=$request->get("doctor_id");
                                      $data->slot_id=$request->get("slot_id");
                                      $data->slot_name=$request->get("slot_name");
                                      $data->date=$request->get("date");
                                      $data->phone=$request->get("phone");
                                      $data->user_description=$request->get("user_description");
                                      $data->transaction_id=$charge->id;
                                      $data->payment_mode="Stripe";
                                      $data->consultation_fees = $request->get("consultation_fees");
                                      $data->save();
                                          $store = new Settlement();
                                          $store->book_id = $data->id;
                                          $store->status = '0';
                                          $store->payment_date = $date->format('Y-m-d');
                                          $store->doctor_id = $data->doctor_id;
                                          $store->amount = $request->get("consultation_fees");
                                          $store->save();
                                          $msg=__("apimsg.You have a new upcoming appointment");
                                          $user=User::find(1);
                                          $android=$this->send_notification_android($user->android_key,$msg,$request->get("doctor_id"),"doctor_id",$data->id);
                                          $ios=$this->send_notification_IOS($user->ios_key,$msg,$request->get("doctor_id"),"doctor_id",$data->id);
                                           try {
                                                      $user=Doctors::find($request->get("doctor_id")); 
                                                      $user->msg=$msg;
                                                      
                                                      $result=Mail::send('email.Ordermsg', ['user' => $user], function($message) use ($user){
                                                         $message->to($user->email,$user->name)->subject(__('message.System Name'));
                                                      });
                                                  
                                              } catch (\Exception $e) {
                                             }
                                                $response['success']="1";
                                                $response['register']="Appointment Book Successfully";
                                                $response['data']=$data->id;
                                               DB::commit();
                                        }catch (\Exception $e) {
                                              DB::rollback();
                                                $response['success']="0";
                                                $response['register']=$e;
                                        }
                        }
                        if($request->input('payment_type')==1){
                            $gateway = new \Braintree\Gateway([
                                  'environment' => env('BRAINTREE_ENV'),
                                  'merchantId'  => env('BRAINTREE_MERCHANT_ID'),
                                  'publicKey'   => env('BRAINTREE_PUBLIC_KEY'),
                                  'privateKey'  => env('BRAINTREE_PRIVATE_KEY')
                             ]);
                            $nonce = $request->get("payment_method_nonce");
                            $result = $gateway->transaction()->sale([
                                          'amount' => $request->get("consultation_fees"),
                                          'paymentMethodNonce' => $nonce,
                                          'options' => [
                                              'submitForSettlement' => true
                                          ]
                                      ]);
                            if ($result->success) {
                                  $transaction = $result->transaction;
                                  DB::beginTransaction();
                                  try {
                                      $date = DateTime::createFromFormat('d', 15)->add(new DateInterval('P1M'));
                                      $data=new BookAppointment();
                                      $data->user_id=$request->get("user_id");
                                      $data->doctor_id=$request->get("doctor_id");
                                      $data->slot_id=$request->get("slot_id");
                                      $data->slot_name=$request->get("slot_name");
                                      $data->date=$request->get("date");
                                      $data->phone=$request->get("phone");
                                      $data->user_description=$request->get("user_description");
                                      $data->payment_mode="Braintree";
                                      $data->transaction_id=$transaction->id;
                                      $data->consultation_fees = $request->get("consultation_fees");
                                      $data->save();
                                       $store = new Settlement();
                                          $store->book_id = $data->id;
                                          $store->status = '0';
                                          $store->payment_date = $date->format('Y-m-d');
                                          $store->doctor_id = $data->doctor_id;
                                          $store->amount = $request->get("consultation_fees");
                                          $store->save();
                                          $msg=__("apimsg.You have a new upcoming appointment");
                                          $user=User::find(1);
                                          $android=$this->send_notification_android($user->android_key,$msg,$request->get("doctor_id"),"doctor_id",$data->id);
                                          $ios=$this->send_notification_IOS($user->ios_key,$msg,$request->get("doctor_id"),"doctor_id",$data->id);
                                           try {
                                                      $user=Doctors::find($request->get("doctor_id")); 
                                                      $user->msg=$msg;
                                                      
                                                      $result=Mail::send('email.Ordermsg', ['user' => $user], function($message) use ($user){
                                                         $message->to($user->email,$user->name)->subject(__('message.System Name'));
                                                      });
                                                  
                                              } catch (\Exception $e) {
                                             }
                                                $response['success']="1";
                                                $response['register']="Appointment Book Successfully";
                                                $response['data']=$data->id;
                                               DB::commit();
                                        }catch (\Exception $e) {
                                              DB::rollback();
                                                $response['success']="0";
                                                $response['register']=$e;
                                        }
                                 }else{
                                        $errorString = "";
                                            foreach($result->errors->deepAll() as $error) {
                                                $errorString .= 'Error: ' . $error->code . ": " . $error->message . "\n";
                                            }
                                        $response['success']="0";
                                        $response['register']=$errorString;
                                }
                        }else{
                                 DB::beginTransaction();
                                  try {
                                      $date = DateTime::createFromFormat('d', 15)->add(new DateInterval('P1M'));
                                      $data=new BookAppointment();
                                      $data->user_id=$request->get("user_id");
                                      $data->doctor_id=$request->get("doctor_id");
                                      $data->slot_id=$request->get("slot_id");
                                      $data->slot_name=$request->get("slot_name");
                                      $data->date=$request->get("date");
                                      $data->phone=$request->get("phone");
                                      $data->user_description=$request->get("user_description");
                                      $data->payment_mode="COD";
                                      //$data->transaction_id=$transaction->id;
                                      $data->consultation_fees = $request->get("consultation_fees");
                                      $data->save();
                                       $store = new Settlement();
                                          $store->book_id = $data->id;
                                          $store->status = '0';
                                          $store->payment_date = $date->format('Y-m-d');
                                          $store->doctor_id = $data->doctor_id;
                                          $store->amount = $request->get("consultation_fees");
                                          $store->save();
                                          $msg=__("apimsg.You have a new upcoming appointment");
                                          $user=User::find(1);
                                          $android=$this->send_notification_android($user->android_key,$msg,$request->get("doctor_id"),"doctor_id",$data->id);
                                          $ios=$this->send_notification_IOS($user->ios_key,$msg,$request->get("doctor_id"),"doctor_id",$data->id);
                                           try {
                                                      $user=Doctors::find($request->get("doctor_id")); 
                                                      $user->msg=$msg;
                                                      
                                                      $result=Mail::send('email.Ordermsg', ['user' => $user], function($message) use ($user){
                                                         $message->to($user->email,$user->name)->subject(__('message.System Name'));
                                                      });
                                                  
                                              } catch (\Exception $e) {
                                             }
                                                $response['success']="1";
                                                $response['register']="Appointment Book Successfully";
                                                $response['data']=$data->id;
                                               DB::commit();
                                        }catch (\Exception $e) {
                                              DB::rollback();
                                                $response['success']="0";
                                                $response['register']=$e;
                                        }
                        }                     
                  }                  
        }
        return json_encode($response, JSON_NUMERIC_CHECK);

   }

   public function viewdoctor(Request $request){
       $response = array("success" => "0", "register" => "Validation error");
        $rules = [
            'doctor_id'=>'required',
        ];
       
         $messages = array(
                  'doctor_id.required' => "doctor_id is required"
            );
       
        $validator = Validator::make($request->all(), $rules,$messages);

        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {
                  $getdetail=Doctors::find($request->get("doctor_id"));
                  if(empty($getdetail)){
                          $response['success']="0";
                          $response['register']="Doctor Not Found";
                  }else{
                          $getdepartment=Services::find($getdetail->department_id);
                          if($getdepartment){
                              $getdetail->department_name=$getdepartment->name;

                          }else{
                              $getdetail->department_name="";
                          }
                          $getdetail->avgratting=Review::where('doc_id',$request->get("doctor_id"))->avg('rating');
                          $getdetail->total_review=count(Review::where('doc_id',$request->get("doctor_id"))->get());
                          $getdetail->image=asset('public/upload/doctors').'/'.$getdetail->image;
                          $response['success']="1";
                          $response['register']="Doctor Get Successfully";
                          $response['data']=$getdetail;
                  }                  
        }
        return json_encode($response, JSON_NUMERIC_CHECK);

   }

   public function addreview(Request $request){
        $response = array("success" => "0", "register" => "Validation error");
        $rules = [
            'user_id'=>'required',
            'rating'=>'required',
            'doc_id'=>'required',
            'description'=>'required'
        ];
       
         $messages = array(
                  'user_id.required' => "user_id is required",
                  'rating.required' => "rating is required",
                  'doc_id.required' => "doc_id is required",
                  'description.required' => "description is required"
            );
       
        $validator = Validator::make($request->all(), $rules,$messages);

        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {
                 
                   $store=new Review();
                   $store->user_id=$request->get("user_id");
                   $store->doc_id=$request->get("doc_id");
                   $store->rating=$request->get("rating");
                   $store->description=$request->get("description");
                   $store->save();
                          $response['success']="1";
                          $response['register']="Review Add Successfully";
                          $response['data']=$store;
                                   
        }
        return json_encode($response, JSON_NUMERIC_CHECK);

   }

   public function getslotdata(Request $request){
        $response = array("success" => "0", "register" => "Validation error");
        $rules = [
            'doctor_id'=>'required',
            'date'=>'required',

        ];
       
         $messages = array(
                  'doctor_id.required' => "doctor_id is required",
                  'date.required' => "rating is required"
            );
       
        $validator = Validator::make($request->all(), $rules,$messages);

        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {
                          $day=date('N',strtotime($request->get("date")))-1;
                          $data=Schedule::with('getslotls')->where("doctor_id",$request->get("doctor_id"))->where("day_id",$day)->get();
                          $main=array();
                          if(count($data)>0){
                                foreach ($data as $k) {
                                     $slotlist=array();
                                     $slotlist['title']=$k->start_time." - ".$k->end_time;
                                    if(count($k->getslotls)>0){
                                        foreach ($k->getslotls as $b) {
                                            $ka=array();
                                            $getappointment=BookAppointment::where("date",$request->get("date"))->where("slot_id",$b->id)->first();
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
                          if(empty($slotlist)){
                              $response['success']="0";
                              $response['register']="Slot Not Found";
                          }else{
                              $response['success']="1";
                              $response['register']="Get Slot Successfully";
                              $response['data']=$main;
                          }
                          
                                   
        }
        return json_encode($response, JSON_NUMERIC_CHECK);

   }

   public function getlistofdoctorbyspecialty(Request $request){
      $response = array("success" => "0", "register" => "Validation error");
        $rules = [
            'department_id'=>'required',
            'lat'=>'required',
            'lon'=>'required'
        ];
       
        $messages = array(
                  'department_id.required' => "department_id is required",
                  'lat.required' => "lat is required",
                  'lon.required' => "lon is required"
        );
        $validator = Validator::make($request->all(), $rules,$messages);
        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {
                    $lat = $request->get('lat');
                    $lon = $request->get("lon");
                    $data =  $data=DB::table("doctors")
                            ->where("department_id",$request->get("department_id"))
                          ->select("doctors.id","doctors.name","doctors.address","doctors.email","doctors.phoneno","doctors.department_id","doctors.image"
                              ,DB::raw("6371 * acos(cos(radians(" . $lat . ")) 
                              * cos(radians(doctors.lat)) 
                              * cos(radians(doctors.lon) - radians(" . $lon . ")) 
                              + sin(radians(" .$lat. ")) 
                              * sin(radians(doctors.lat))) AS distance"))
                              ->orderby('distance')->WhereNotNull("doctors.lat")->paginate(10);
                        
                          if(count($data)==0){
                              $response['success']="0";
                              $response['register']="Doctors Not Found";
                          }else{
                                 foreach ($data as $d) {
                                    $dp=Services::find($d->department_id);
                                    if($dp){
                                         $d->department_name=$dp->name;
                                    }
                                    $d->image=asset('public/upload/doctors').'/'.$d->image;
                                 }
                              $response['success']="1";
                              $response['register']="Doctors List Successfully";
                              $response['data']=$data;
                          }     
        }
        return json_encode($response, JSON_NUMERIC_CHECK);

   }

   public function userspastappointment(Request $request){
        $response = array("success" => "0", "register" => "Validation error");
        $rules = [
            'user_id'=>'required'
        ];
       
        $messages = array(
                  'user_id.required' => "user_id is required"
        );
        $validator = Validator::make($request->all(), $rules,$messages);
        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {
                          $data=BookAppointment::where("user_id",$request->get("user_id"))->select("id","doctor_id","date","slot_name as slot",'phone')->orderby('id',"DESC")->paginate(15);
                          if(count($data)==0){
                              $response['success']="0";
                              $response['register']="Appointment Not Found";
                          }else{
                            $new=array();
                                 foreach ($data as $d) {
                                     $a=array();
                                     
                                     $doctors=Doctors::find($d->doctor_id);
                                     $department=Services::find($doctors->department_id);
                                     if($doctors){
                                         $d->name=$doctors->name;
                                         $d->address=$doctors->address;
                                         $d->image=isset($doctors->image)?asset('public/upload/doctors').'/'.$doctors->image:"";
                                          $d->department_name=isset($department)?$department->name:"";
                                     }else{
                                          $d->name="";
                                          $d->address="";
                                          $d->image="";
                                          $d->department_name="";
                                     }
                                     unset($d->department_id);
                                     unset($d->doctor_id);
                                     unset($d->doctorls);
                                        if($d->status=='1'){
                                              $d->status=__("message.Received");
                                        }else if($d->status=='2'){
                                              $d->status=__("message.Approved");
                                        }else if($d->status=='3'){
                                              $d->status=__("message.In Process");
                                        }else if($d->status=='4'){
                                              $d->status=__("message.Completed");
                                        }else if($d->status=='5'){
                                              $d->status=__("message.Rejected");
                                        }else{
                                               $d->status=__("message.Absent");
                                        }
                                     
                                 }
                              $response['success']="1";
                              $response['register']="Appointment List Successfully";
                              $response['data']=$data;
                              
                          }     
        }
        return json_encode($response, JSON_NUMERIC_CHECK);

   }

   public function usersupcomingappointment(Request $request){
       $response = array("success" => "0", "register" => "Validation error");
        $rules = [
            'user_id'=>'required'
        ];
       
        $messages = array(
                  'user_id.required' => "user_id is required"
        );
        $validator = Validator::make($request->all(), $rules,$messages);
        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {
                          $data=BookAppointment::where("date",">=",date('Y-m-d'))->select("id","doctor_id","date","slot_name as slot",'phone')->where("user_id",$request->get("user_id"))->paginate(15);
                          if(count($data)==0){
                              $response['success']="0";
                              $response['register']="Appointment Not Found";
                          }else{
                                foreach ($data as $d) {
                                     $a=array();
                                     
                                     $doctors=Doctors::find($d->doctor_id);
                                     $department=Services::find($doctors->department_id);
                                     if($doctors){
                                         $d->name=$doctors->name;
                                         $d->address=$doctors->address;
                                         $d->image=isset($doctors->image)?asset('public/upload/doctors').'/'.$doctors->image:"";
                                          $d->department_name=isset($department)?$department->name:"";
                                     }else{
                                          $d->name="";
                                          $d->address="";
                                          $d->image="";
                                          $d->department_name="";
                                     }
                                     unset($d->department_id);
                                     unset($d->doctor_id);
                                     unset($d->doctorls);
                                     
                                      if($d->status=='1'){
                                              $d->status=__("message.Received");
                                        }else if($d->status=='2'){
                                              $d->status=__("message.Approved");
                                        }else if($d->status=='3'){
                                              $d->status=__("message.In Process");
                                        }else if($d->status=='4'){
                                              $d->status=__("message.Completed");
                                        }else if($d->status=='5'){
                                              $d->status=__("message.Rejected");
                                        }else{
                                               $d->status=__("message.Absent");
                                        }
                                     //$new[]=$a;
                                 }
                              $response['success']="1";
                              $response['register']="Appointment List Successfully";
                              $response['data']=$data;
                          }     
        }
        return json_encode($response, JSON_NUMERIC_CHECK);

   }


   public function reviewlistbydoctor(Request $request){
        $response = array("success" => "0", "register" => "Validation error");
        $rules = [
            'doctor_id'=>'required'
        ];
        
       
        $messages = array(
                  'doctor_id.required' => "doctor_id is required"
        );
        $validator = Validator::make($request->all(), $rules,$messages);
        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {
                          $data=Review::with('patientls')->where("doc_id",$request->get("doctor_id"))->orderby('id','DESC')->select('id','user_id','rating','description')->get();
                          if(count($data)==0){
                              $response['success']="0";
                              $response['register']="Review Not Found";
                          }else{
                                $main_array=array();
                                foreach ($data as $d) {
                                    $ls=array();
                                    $ls['name']=isset($d->patientls->name)?$d->patientls->name:"";
                                    $ls['rating']=isset($d->rating)?$d->rating:"";
                                    $ls['description']=isset($d->description)?$d->description:"";
                                    $ls['image']=isset($d->patientls->profile_pic)?asset('public/upload/profile').'/'.$d->patientls->profile_pic:"";
                                    $ls['phone']=isset($d->patientls->phone)?$d->phone:"";
                                    $main_array[]=$ls;
                                }
                                 
                              $response['success']="1";
                              $response['register']="Review List Successfully";
                              $response['data']=$main_array;
                          }     
        }
        return json_encode($response, JSON_NUMERIC_CHECK);

   }

   public function doctorpastappointment(Request $request){
      $response = array("success" => "0", "register" => "Validation error");
        $rules = [
            'doctor_id'=>'required'
        ];
       
        $messages = array(
                  'doctor_id.required' => "doctor_id is required"
        );
        $validator = Validator::make($request->all(), $rules,$messages);
        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {
                          $data=BookAppointment::orderby('id',"DESC")->where("doctor_id",$request->get("doctor_id"))->select("date","id","slot_name as slot","user_id","phone","status")->paginate(10);
                          if(count($data)==0){
                              $response['success']="0";
                              $response['register']="Appointment Not Found";
                          }else{
                                 foreach ($data as $d) {
                                     $user=Patient::find($d->user_id);
                                     if($user){
                                         $d->name=$user->name;
                                         $d->image=isset($user->profile_pic)?asset('public/upload/profile').'/'.$user->profile_pic:"";
                                     }else{
                                         $d->name="";
                                         $d->image="";
                                         
                                     }
                                      if($d->status=='1'){
                                              $d->status=__("message.Received");
                                        }else if($d->status=='2'){
                                              $d->status=__("message.Approved");
                                        }else if($d->status=='3'){
                                              $d->status=__("message.In Process");
                                        }else if($d->status=='4'){
                                              $d->status=__("message.Completed");
                                        }else if($d->status=='5'){
                                              $d->status=__("message.Rejected");
                                        }else{
                                               $d->status=__("message.Absent");
                                        }
                                      unset($d->user_id);
                                 }
                              $response['success']="1";
                              $response['register']="Appointment List Successfully";
                              $response['data']=$data;
                          }     
        }
        return json_encode($response, JSON_NUMERIC_CHECK);

   }


   public function doctoruappointment(Request $request){
      $response = array("success" => "0", "register" => "Validation error");
        $rules = [
            'doctor_id'=>'required'
        ];
       
        $messages = array(
                  'doctor_id.required' => "doctor_id is required"
        );
        $validator = Validator::make($request->all(), $rules,$messages);
        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {
                          $data=BookAppointment::where("date",">=",date('Y-m-d'))->where("doctor_id",$request->get("doctor_id"))->select("date","id","slot_name as slot","user_id","phone","status")->paginate(10);
                          if(count($data)==0){
                              $response['success']="0";
                              $response['register']="Appointment Not Found";
                          }else{
                                
                                 foreach ($data as $d) {
                                     $user=Patient::find($d->user_id);
                                    if($user){
                                         $d->name=$user->name;
                                         $d->image=isset($user->profile_pic)?asset('public/upload/profile').'/'.$user->profile_pic:"";
                                     }else{
                                         $d->name="";
                                         $d->image="";
                                         
                                     }
                                      if($d->status=='1'){
                                              $d->status=__("message.Received");
                                        }else if($d->status=='2'){
                                              $d->status=__("message.Approved");
                                        }else if($d->status=='3'){
                                              $d->status=__("message.In Process");
                                        }else if($d->status=='4'){
                                              $d->status=__("message.Completed");
                                        }else if($d->status=='5'){
                                              $d->status=__("message.Rejected");
                                        }else{
                                               $d->status=__("message.Absent");
                                        }
                                      unset($d->user_id);
                                 }
                              $response['success']="1";
                              $response['register']="Appointment List Successfully";
                              $response['data']=$data;
                          }     
        }
        return json_encode($response, JSON_NUMERIC_CHECK);

   }
   
   public function doctordetail(Request $request){
        $response = array("success" => "0", "register" => "Validation error");
        $rules = [
            'doctor_id'=>'required'
        ];
       
        $messages = array(
                  'doctor_id.required' => "doctor_id is required"
        );
        $validator = Validator::make($request->all(), $rules,$messages);
        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {
                          $data=Doctors::find($request->get("doctor_id"));
                          
                          if(empty($data)){
                              $response['success']="0";
                              $response['register']="Doctor Not Found";
                          }else{
                              $d=Services::find($data->department_id);
                              $data->department_name=isset($d)?$d->name:"";
                              unset($data->department_id);
                              $data->image=asset("public/upload/doctors").'/'.$data->image;
                              $data->avgratting=round(Review::where("doc_id",$request->get("doctor_id"))->avg('rating'));
                              $response['success']="1";
                              $response['register']="Doctor Get Successfully";
                              $response['data']=$data;
                          }     
        }
        return json_encode($response, JSON_NUMERIC_CHECK);

   }
  
     public function appointmentdetail(Request $request){
       $response = array("success" => "0", "register" => "Validation error");
        $rules = [
            'id'=>'required',
            'type'=>'required'
        ];
       
        $messages = array(
                  'id.required' => "id is required",
                  'type.required' => "type is required"
        );
        $validator = Validator::make($request->all(), $rules,$messages);
        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {
                    $data=BookAppointment::with('doctorls','patientls')->find($request->get("id"));
                    $ls=array();
                    if($data){
                         if($request->get("type")==1){ //patients
                                $ls['image']=isset($data->doctorls->image)?asset("public/upload/doctors").'/'.$data->doctorls->image:"";
                                $ls['name']=isset($data->doctorls)?$data->doctorls->name:"";
                                /*if($data->status=='1'){
                                      $ls['status']=__("message.Received");
                                }else if($data->status=='2'){
                                      $ls['status']=__("message.Approved");
                                }else if($data->status=='3'){
                                      $ls['status']=__("message.In Process");
                                }else if($data->status=='4'){
                                      $ls['status']=__("message.Completed");
                                }else if($data->status=='5'){
                                      $ls['status']=__("message.Rejected");
                                }else{
                                       $ls['status']=__("message.Absent");
                                }*/
                                
                                $ls['status'] = $data->status;
                                $ls['date']=$data->date;
                                $ls['slot']=$data->slot_name;
                                $ls['phone']=isset($data->doctorls)?$data->doctorls->phoneno:"";;
                                $ls['email']=isset($data->doctorls)?$data->doctorls->email:"";;
                                $ls['description']=$data->user_description;
                                $ls['id']=$data->id;  
                                if($data->prescription_file!=""){
                                    $ls['prescription'] = asset('public/upload/prescription').'/'.$data->prescription_file;
                                }else{
                                    $ls['prescription'] = "";
                                }
                                
                            }else{ //doctor
                                $ls['image']=isset($data->patientls->profile_pic)?asset("public/upload/profile").'/'.$data->patientls->profile_pic:"";
                                $ls['name']=isset($data->patientls)?$data->patientls->name:"";
                                /* if($data->status=='1'){
                                      $ls['status']=__("message.Received");
                                }else if($data->status=='2'){
                                      $ls['status']=__("message.Approved");
                                }else if($data->status=='3'){
                                      $ls['status']=__("message.In Process");
                                }else if($data->status=='4'){
                                      $ls['status']=__("message.Completed");
                                }else if($data->status=='5'){
                                      $ls['status']=__("message.Rejected");
                                }else{
                                       $ls['status']=__("message.Absent");
                                }*/
                                 $ls['status'] = $data->status;
                                $ls['date']=$data->date;
                                $ls['slot']=$data->slot_name;
                                $ls['phone']=$data->phone;
                                $ls['email']=isset($data->patientls)?$data->patientls->email:"";;
                                $ls['description']=$data->user_description;
                                $ls['id']=$data->id;   
                                 if($data->prescription_file!=""){
                                    $ls['prescription'] = asset('public/upload/prescription').'/'.$data->prescription_file;
                                }else{
                                    $ls['prescription'] = "";
                                }
                            }
                            $response['success']="1";
                            $response['register']="Appointment Detail Get Successfully";
                            $response['data']=$ls;
                    }else{
                             $response['success']="0";
                             $response['register']="Appointment Not Found";
                    }
                   
        }
        return json_encode($response, JSON_NUMERIC_CHECK);

   }

    public function doctoreditprofile(Request $request){
       $response = array("success" => "0", "register" => "Validation error");
        $rules = [
                    "doctor_id"=>'required',
                    "name"=>'required',
                    "email"=>"required",
                    "aboutus"=>"required",
                    "working_time"=>"required",
                    "address"=>"required",
                    "lat"=>"required",
                    "lon"=>"required",
                    "phoneno"=>"required",
                    "services"=>"required",
                    "healthcare"=>"required",
                    "department_id"=>"required",
                    "facebook_url"=>"required",
                    "twitter_url"=>"required",
                    "consultation_fees"=>"required"
                    //"time_json"=>"required"
        ];
       
        $messages = array(
                  'doctor_id.required' => "doctor_id is required",
                  'name.required' => "name is required",
                  'email.required' => "email is required",
                  'aboutus.required' => "aboutus is required",
                  'working_time.required' => "working_time is required",
                  'address.required' => "address is required",
                  'lat.required' => "lat is required",
                  'lon.required' => "lon is required",
                  'phoneno.required' => "phoneno is required",
                  'services.required' => "services is required",
                  'healthcare.required' => "healthcare is required",
                  'department_id.required' => "department_id is required",
                  'facebook_url.required' => "facebook_url is required",
                  'twitter_url.required' => "twitter_url is required",
                  'consultation_fees.required'=>"consultation_fees is required"
                  //'time_json.required' => "time_json is required"
        );
        $validator = Validator::make($request->all(), $rules,$messages);
        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {

                    
                        $store=Doctors::find($request->get("doctor_id"));
                        if($store){
                              DB::beginTransaction();
                              try {
                                    $img_url=$store->image;
                                    $rel_url=$store->image;         
                                         if ($request->file('image')) 
                                    {
                                       
                                              $file = $request->file('image');
                                             $filename = $file->getClientOriginalName();
                                             $extension = $file->getClientOriginalExtension() ?: 'png';
                                             $folderName = '/upload/doctors/';
                                             $picture = time() . '.' . $extension;
                                             $destinationPath = public_path() . $folderName;
                                             $request->file('image')->move($destinationPath, $picture);
                                             $img_url =$picture;                
                                              $image_path = public_path() ."/upload/doctors/".$rel_url;
                                                if(file_exists($image_path)&&$rel_url!="") {
                                                    try {
                                                         unlink($image_path);
                                                    }
                                                    catch(Exception $e) {
                                                      
                                                    }                        
                                              }
                                    }
                                    $store->name=$request->get("name");
                                    $store->department_id=$request->get("department_id");
                                    $store->password=$request->get("password");
                                    $store->phoneno=$request->get("phoneno");
                                    $store->aboutus=$request->get("aboutus");
                                    $store->services=$request->get("services");
                                    $store->healthcare=$request->get("healthcare");
                                    $store->address=$request->get("address");
                                    $store->lat=$request->get("lat");
                                    $store->lon=$request->get("lon");
                                    $store->facebook_url=$request->get("facebook_url");
                                    $store->twitter_url=$request->get("twitter_url");
                                    $store->email=$request->get("email");
                                    $store->working_time=$request->get("working_time");
                                    $store->consultation_fees = $request->get("consultation_fees");
                                    $store->image=$img_url;
                                    $store->save();
                                    if($request->get("time_json")!=""){
                                        $datadesc = json_decode($request->get("time_json"), true);
                                        $arr = $datadesc['timing'];
                                        $i=0;
                                        $removedata=Schedule::where("doctor_id",$request->get("doctor_id"))->get();
                                      if(count($removedata)>0){
                                        foreach ($removedata as $k) {
                                            $findslot=SlotTiming::where("schedule_id",$k->id)->delete();
                                            $k->delete();
                                        }
                                     }                  
                                    foreach ($arr as $k) {
                                       foreach ($k as $l) {
                                            $getslot=$this->getslotvalue($l['start_time'],$l['end_time'],$l['duration']);
                                            $store=new Schedule();
                                            $store->doctor_id=$request->get("doctor_id");
                                            $store->day_id=$i;
                                            $store->start_time=$l['start_time'];
                                            $store->end_time=$l['end_time'];
                                            $store->duration=$l['duration'];
                                            $store->save();
                                            foreach ($getslot as $g) {
                                                $aslot=new SlotTiming();
                                                $aslot->schedule_id=$store->id;
                                                $aslot->slot=$g;
                                                $aslot->save();
                                            }
                                       }
                                       $i++;
                                    }
                                    }
                                    DB::commit();
                                    $response['success']="1";
                                    $response['register']="Profile Update Successfully";
                           }catch(Exception $e){
                                 DB::rollback();
                                  $response['success']="0";
                                  $response['register']="Something Wrong";
                            }
                        }else{
                               $response['success']="0";
                                $response['register']="Doctor Not Found";
                        }
        }
        return json_encode($response, JSON_NUMERIC_CHECK);

   }

     public function getslotvalue($start_time,$end_time,$duration){       
         $datetime1 = strtotime($start_time);
         $datetime2 = strtotime($end_time);
         $interval  = abs($datetime2 - $datetime1);
         $minutes   = round($interval / 60);         
         $noofslot=$minutes /$duration;
         $slot=array();
         if($noofslot>0){
            for ($i=0; $i <$noofslot; $i++) { 
                $a=$duration*$i;
                $slot[]=date("h:i A",strtotime("+".$a." minutes", strtotime($start_time)));
            }
         }
         return $slot;
     }
     
     public function getdoctorschedule(Request $request){
          $response = array("success" => "0", "register" => "Validation error");
        $rules = [
            'doctor_id'=>'required'
        ];
       
        $messages = array(
                  'doctor_id.required' => "doctor_id is required"
        );
        $validator = Validator::make($request->all(), $rules,$messages);
        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {
                          $data=Doctors::find($request->get("doctor_id"));
                        
                          if(empty($data)){
                              $response['success']="0";
                              $response['register']="Doctor Not Found";
                          }else{
                              $data=Schedule::with('getslotls')->where("doctor_id",$request->get("doctor_id"))->get();
                              $response['success']="1";
                              $response['register']="Doctor Get Successfully";
                              $response['data']=$data;
                          }     
        }
        return json_encode($response, JSON_NUMERIC_CHECK);

     }

     public function usereditprofile(Request $request){
        $response = array("success" => "0", "register" => "Validation error");
        $rules = [
            'id'=>'required',
            'name'=>'required',
            'email'=>'required',
            'phone'=>'required',
            'password'=>'required'
        ];
       
        $messages = array(
                  'id.required' => "id is required",
                  'name.required' => "name is required",
                  'email.required' => "email is required",
                  'phone.required' => "phone is required",
                  'password.required' => "password is required"
        );
        $validator = Validator::make($request->all(), $rules,$messages);
        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {
                          $data1=Patient::find($request->get("id"));
                        
                          if(empty($data1)){
                              $response['success']="0";
                              $response['register']="Patient Not Found";
                          }else{
                              
                              $checkemail=Patient::where("email",$request->get("email"))->where("id",'!=',$request->get("id"))->first();
                              if($checkemail){
                                  $response['success']="0";
                                  $response['register']="Email Already Use By Other User";
                              }else{
                                    $img_url=$data1->profile_pic;
                                    $rel_url=$data1->profile_pic;         
                                    if ($request->file('image')) 
                                    {
                                        
                                             $file = $request->file('image');
                                             $filename = $file->getClientOriginalName();
                                             $extension = $file->getClientOriginalExtension() ?: 'png';
                                             $folderName = '/upload/profile/';
                                             $picture = time() . '.' . $extension;
                                             $destinationPath = public_path() . $folderName;
                                             $request->file('image')->move($destinationPath, $picture);
                                             $img_url =$picture;                
                                              $image_path = public_path() ."/upload/profile/".$rel_url;
                                                if(file_exists($image_path)&&$rel_url!="") {
                                                    try {
                                                         unlink($image_path);
                                                    }
                                                    catch(Exception $e) {
                                                      
                                                    }                        
                                              }
                                    }
                                  $data1->name=$request->get("name");
                                  $data1->email=$request->get("email");
                                  $data1->password=$request->get("password");
                                  $data1->phone=$request->get("phone");
                                  $data1->profile_pic=$img_url;
                                  $data1->save();
                                  $response['success']="1";
                                   $response['register']="User Get Successfully";
                                   $response['data']=$data1;
                              }
                              
                          }     
        }
       return json_encode($response, JSON_NUMERIC_CHECK);

     }
     
     public function saveReportspam(Request $request){
    $response = array("success" => "0", "register" => "Validation error");
        $rules = [
            'user_id'=>'required',
            'title'=>'required',
            'description'=>'required'
        ];
       
        $messages = array(
                  'user_id.required' => "user_id is required",
                  'title.required' => "title is required",
                  'description.required' => "description is required"
        );
        $validator = Validator::make($request->all(), $rules,$messages);
        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {
                          
                          $store=new Reportspam();
                          $store->user_id=$request->get("user_id");
                          $store->subject=$request->get("title");
                          $store->description=$request->get("description");
                          $store->save();
                                  $response['success']="1";
                                   $response['register']="Report Send Successfully";
                                   $response['data']=$store;
                             
                              
                         
        }
        return json_encode($response, JSON_NUMERIC_CHECK);

     }
     
     public function user_reject_appointment(Request $request){
        $response = array("success" => "0", "register" => "Validation error");
        $rules = [
            'user_id'=>'required',
            'id'=>'required'
        ];
       
        $messages = array(
                  'user_id.required' => "user_id is required",
                  'id.required' => "id is required"
        );
        $validator = Validator::make($request->all(), $rules,$messages);
        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {
                          
                    $data=BookAppointment::where("id",$request->get("id"))->where("user_id",$request->get("user_id"))->first();
                    if($data){
                        $data->status=5;
                        $data->save();
                        $response['success']="1";
                        $response['register']="Appointment Reject Successfully";
                    }else{
                         $response['success']="0";
                    $response['register']="Appointment Not Found";
                    }
                   
        }
        return json_encode($response, JSON_NUMERIC_CHECK);

         
     }
     
     public function appointmentstatuschange(Request $request){
        $response = array("success" => "0", "msg" => "Validation error");
        $rules = [
            'app_id'=>'required',
            'status'=>'required'
        ];
        if($request->input('status')==4){
            $rules['prescription'] = 'required';
        }
       
        $messages = array(
                  'app_id.required' => "app_id is required",
                  'status.required' => "status is required",
                  "prescription"=>"prescription is required"
        );
        $validator = Validator::make($request->all(), $rules,$messages);
        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {
                          
                 $getapp=BookAppointment::with('doctorls','patientls')->find($request->get("app_id"));
                 if($getapp){
                            $getapp->status=$request->get("status");
                            if ($request->hasFile('prescription')) 
                              {
                                 $file = $request->file('prescription');
                                 $filename = $file->getClientOriginalName();
                                 $extension = $file->getClientOriginalExtension() ?: 'png';
                                 $folderName = '/upload/prescription/';
                                 $picture = time() . '.' . $extension;
                                 $destinationPath = public_path() . $folderName;
                                 $request->file('prescription')->move($destinationPath, $picture);
                                 $getapp->prescription_file =$picture;
                             }
                            $getapp->save();
                            if($request->get("status")=='3'){ // in process
                                $msg=__("apimsg.Your Appointment  has been accept by")." ".$getapp->doctorls->name." ".__("apimsg.for time")."".$getapp->date.' '.$getapp->slot_name;
                            }
                            else if($request->get("status")=='5'){ //reject
                                $msg=__("apimsg.Your Appointment  has been reject By")." ".$getapp->doctorls->name;
                                Settlement::where("book_id",$request->get("app_id"))->delete();
                            }else if($request->get("status")=='4'){//complete
                                $msg=__("apimsg.Your Appointment  with")." ".$getapp->doctorls->name." is completed";
                            }else if($request->get("status")=='0'){//absent
                                $msg=__("apimsg.You were absent on your appointment with")." ".$getapp->doctorls->name;
                            }else{
                                $msg="";
                            }
                            $user=User::find(1);
                            
                            $android=$this->send_notification_android($user->android_key,$msg,$getapp->user_id,"user_id",$getapp->id);
                            $ios=$this->send_notification_IOS($user->ios_key,$msg,$getapp->user_id,"user_id",$getapp->id);
                            $response['success']="1";
                            $response['msg']=$msg;
                              try {
                                   if($getapp->prescription_file!=""){
                                        $user=Patient::find($getapp->user_id); 
                                        $user->msg=$msg;
                                        $user->prescription = $getapp->prescription_file;
                                         $user->email="redixbit.jalpa@gmail.com";
                                          $result=Mail::send('email.Ordermsg', ['user' => $user], function($message) use ($user){
                                             $message->to($user->email,$user->name)->subject(__('message.System Name'));
                                             $message->attach(asset('public/upload/prescription').'/'.$user->prescription);
                                             
                                          });
                                   }else{
                                        $user=Patient::find($getapp->user_id); 
                                        $user->msg=$msg;
                                         //$user->email="redixbit.jalpa@gmail.com";
                                          $result=Mail::send('email.Ordermsg', ['user' => $user], function($message) use ($user){
                                             $message->to($user->email,$user->name)->subject(__('message.System Name'));
                                             
                                          });
                                   }
                                      
                                  
                              } catch (\Exception $e) {
                              }
                 }else{
                        $response['success']="0";
                        $response['msg']="Appointment Not Found";
                 }       
        }
        return json_encode($response, JSON_NUMERIC_CHECK);

     }
     
     public function send_notification_android($key,$msg,$id,$field,$order_id){
      if (env('EXTERNAL_SERVICES', false) == false) { return 0; }
        $getuser=TokenData::where("type",1)->where($field,$id)->get();
        
        $i=0;
        if(count($getuser)!=0){   

               $reg_id = array();
               foreach($getuser as $gt){
                   $reg_id[]=$gt->token;
               }
               $regIdChunk=array_chunk($reg_id,1000);
               foreach ($regIdChunk as $k) {
                       $registrationIds =  $k;    
                        $message = array(
                            'message' => $msg,
                            'title' =>  __('message.notification')
                          );
                        $message1 = array(
                            'body' => $msg,
                            'title' =>  __('message.notification'),
                            'type'=>$field,
                            'order_id'=>$order_id,
                            'click_action'=>'FLUTTER_NOTIFICATION_CLICK'
                        );
                        //echo "<pre>";print_r($message1);exit;
                       $fields = array(
                          'registration_ids'  => $registrationIds,
                          'data'              => $message1,
                          'notification'      =>$message1
                       );
                       
                      // echo "<pre>";print_r($fields);exit;
                       $url = '';
                       $headers = array(
                         'Authorization: key='.$key,// . $api_key,
                         'Content-Type: application/json'
                       );
                      $json =  json_encode($fields);   
                      $ch = curl_init();
                      curl_setopt($ch, CURLOPT_URL, $url);
                      curl_setopt($ch, CURLOPT_POST, true);
                      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                      curl_setopt($ch, CURLOPT_POSTFIELDS,$json);
                      $result = curl_exec($ch);   
                      //echo "<pre>";print_r($result);exit;
                      if ($result === FALSE){
                         die('Curl failed: ' . curl_error($ch));
                      }     
                     curl_close($ch);
                     $response[]=json_decode($result,true);
               }
              $succ=0;
               foreach ($response as $k) {
                  $succ=$succ+$k['success'];
               }
             if($succ>0)
              {
                   return 1;
              }
            else
               {
                  return 0;
               }
        }
        return 0;
     }
    public function send_notification_IOS($key,$msg,$id,$field,$order_id){
      if (env('EXTERNAL_SERVICES', false) == false) { return 0; }
      $getuser=TokenData::where("type",2)->where($field,$id)->get();
         if(count($getuser)!=0){               
               $reg_id = array();
               foreach($getuser as $gt){
                   $reg_id[]=$gt->token;
               }
                
              $regIdChunk=array_chunk($reg_id,1000);
               foreach ($regIdChunk as $k) {
                       $registrationIds =  $k;    
                       $message = array(
                            'message' => $msg,
                            'title' =>  __('message.notification')
                          );
                        $message1 = array(
                            'body' => $msg,
                            'title' =>  __('message.notification'),
                            'type'=>$field,
                            'order_id'=>$order_id,
                            'click_action'=>'FLUTTER_NOTIFICATION_CLICK'
                        );
                       $fields = array(
                          'registration_ids'  => $registrationIds,
                          'data'              => $message1,
                          'notification'=>$message1
                       );
                       $url = '';
                       $headers = array(
                         'Authorization: key='.$key,// . $api_key,
                         'Content-Type: application/json'
                       );
                      $json =  json_encode($fields);   
                      $ch = curl_init();
                      curl_setopt($ch, CURLOPT_URL, $url);
                      curl_setopt($ch, CURLOPT_POST, true);
                      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                      curl_setopt($ch, CURLOPT_POSTFIELDS,$json);
                      $result = curl_exec($ch);   
                      if ($result === FALSE){
                         die('Curl failed: ' . curl_error($ch));
                      }     
                     curl_close($ch);
                     $response[]=json_decode($result,true);
               }
              $succ=0;
               foreach ($response as $k) {
                  $succ=$succ+$k['success'];
               }
             if($succ>0)
              {
                   return 1;
              }
            else
               {
                  return 0;
               }
        }
        return 0;
     }
     
    public function forgotpassword(Request $request){
        $response = array("success" => "0", "msg" => "Validation error");
        $rules = [
            'type'=>'required',
            'email'=>'required'
        ];
       
        $messages = array(
                  'type.required' => "type is required",
                  'email.required' => "email is required"
        );
        $validator = Validator::make($request->all(), $rules,$messages);
        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {
                    if($request->get("type")==1){ //patient
                        $checkmobile=Patient::where("email",$request->get("email"))->first();
                    }else{ // doctor
                        $checkmobile=Doctors::where("email",$request->get("email"))->first();
                    }
                      if($checkmobile){
                          $code=mt_rand(100000, 999999);
                          $store=array();
                          $store['email']=$checkmobile->email;
                          $store['name']=$checkmobile->name;
                          $store['code']=$code;
                          $add=new ResetPassword();
                          $add->user_id=$checkmobile->id;
                          $add->code=$code;
                          $add->type=$request->get("type");
                          $add->save();
                          try {
                                  Mail::send('email.forgotpassword', ['user' => $store], function($message) use ($store){
                                    $message->to($store['email'],$store['name'])->subject(__("message.System Name"));
                                });
                          } catch (\Exception $e) {
                          }
                            $response['success']="1";
                            $response['msg']="Mail Send Successfully"; 
                      }else{
                            $response['success']="0";
                            $response['msg']="Email Not Found";   
                          
                      }       
                 
        }
        return json_encode($response, JSON_NUMERIC_CHECK);

    }
    
    public function getholiday(Request $request){
        $response = array("success" => "0", "msg" => "Validation error");
        $rules = [
            'doctor_id'=>'required'
        ];
       
        $messages = array(
                  'doctor_id.required' => "doctor_id is required"
        );
        $validator = Validator::make($request->all(), $rules,$messages);
        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {
                    $data = Doctor_Hoilday::where("doctor_id",$request->get("doctor_id"))->orderby('id','DESC')->get();
                    if(count($data)>0){
                        $response['success']="1";
                        $response['msg']="Get Hoilday List";
                        $response['data']=$data;
                    }else{
                        $response['success']="0";
                        $response['msg']="No Holiday Found";
                    }
        }
        return json_encode($response, JSON_NUMERIC_CHECK);

    }
    
    public function saveholiday(Request $request){
        $response = array("success" => "0", "msg" => "Validation error");
        $rules = [
            'doctor_id'=>'required',
            'id'=>'required',
            'start_date'=>'required',
            'end_date'=>'required',
            'description'=>'required'
            
        ];
       
        $messages = array(
             'doctor_id.required' => "doctor_id is required",
             'id.required' => "id is required",
             'start_date.required' => "start_date is required",
             'end_date.required' => "end_date is required",
             'description.required' => "description is required",
        );
        $validator = Validator::make($request->all(), $rules,$messages);
        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {
                    if($request->get('id')==0){
                        $store = new Doctor_Hoilday();
                        $store->doctor_id = $request->get("doctor_id");
                        $store->start_date = $request->get("start_date");
                        $store->end_date = $request->get("end_date");
                        $store->description = $request->get("description");
                        $store->save();
                        $response['success']="1";
                        $response['msg']="Hoilday Add Successfully";
                        $response['data']=$store;
                    }else{
                        $store = Doctor_Hoilday::find($request->get('id'));
                        if($store){
                            $store->doctor_id = $request->get("doctor_id");
                            $store->start_date = $request->get("start_date");
                            $store->end_date = $request->get("end_date");
                            $store->description = $request->get("description");
                            $store->save();
                             $response['success']="1";
                        $response['msg']="Hoilday Update Successfully";
                        $response['data']=$store;
                        }else{
                            $response['success']="0";
                            $response['msg']="Data Not Update";
                        }
                    }
        }
        return json_encode($response, JSON_NUMERIC_CHECK);

    }
    
    public function deleteholiday(Request $request){
        $response = array("success" => "0", "msg" => "Validation error");
        $rules = [
            'id'=>'required'
        ];
       
        $messages = array(
                  'id.required' => "id is required"
        );
        $validator = Validator::make($request->all(), $rules,$messages);
        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {
                    $date = $request->get("date");
                    $data = Doctor_Hoilday::find($request->get("id"));
                    if(!empty($data)){
                        $data->delete();
                        $response['success']="1";
                        $response['msg']="Holiday Delete Successfully";
                    }else{
                        $response['success']="0";
                        $response['msg']="Hoilday Not Found";
                    }
        }
        return json_encode($response, JSON_NUMERIC_CHECK);

    }
    
    public function checkholiday(Request $request){
        $response = array("success" => "0", "msg" => "Validation error");
        $rules = [
            'doctor_id'=>'required',
            'date'=>'required'
        ];
       
        $messages = array(
                  'doctor_id.required' => "doctor_id is required",
                  'date.required' => "date is required"
        );
        $validator = Validator::make($request->all(), $rules,$messages);
        if ($validator->fails()) {
            $message = '';
                $messages_l = json_decode(json_encode($validator->messages()), true);
                foreach ($messages_l as $msg) {
                    $message .= $msg[0] . ", ";
                }
                $response['register'] = $message;
        } else {
                    $date = $request->get("date");
                    $data = Doctor_Hoilday::where("start_date","<=",$date)->where("end_date",">=",$date)->where("doctor_id",$request->get("doctor_id"))->first();
                   // echo "<pre>";print_r($data);exit;
                    if(empty($data)){
                        $response['success']="1";
                        $response['msg']="Working Day";
                    }else{
                        $response['success']="0";
                        $response['msg']="Hoilday";
                    }
        }
        return json_encode($response, JSON_NUMERIC_CHECK);

    }
}
