<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Session;
use validate;
use Sentinel;
use DB;
use Mail;
use DataTables;
error_reporting(-1);
ini_set('display_errors', 'On');
use App\Models\Patient;
use App\Models\Doctors;
use App\Models\Setting;
use App\Models\BookAppointment;
use App\Models\Services;
use App\Models\Resetpassword;
use App\Models\FavoriteDoc;
use App\Models\Settlement;
use App\Models\Review;
use DateTime;
use DateInterval;
use PaytmWallet;
use Razorpay\Api\Api;
use App\Models\PaymentGatewayDetail;
use KingFlamez\Rave\Facades\Rave as Flutterwave;
class UserController extends Controller
{

    public function userpostregister(Request $request){
     //   dd($request->all());
     
     $this->validate($request,[
          'email' =>'required|unique:patient',
          'phone' => 'required|unique:patient',
          'name' =>'required',
          'password' => 'required|min:6',
          'password_confirmation' => 'required|min:6|same:password',
          'agree' => 'required',
          
       ]);
       
        $getuser=Patient::where("email",$request->get("email"))->first();
        if($getuser){
            Session::flash('message',__("message.Email Already Existe")); 
            Session::flash('alert-class', 'alert-danger');
            return redirect()->back();
        }else{
            $login_field = "";
            $user_id = "";
                    
            $store=new Patient();
            $store->name=$request->get("name");
            $store->email=$request->get("email");
            $store->password=$request->get("password");
            $store->phone=$request->get("phone");
            
            if(env('ConnectyCube')==true){
                  $login_field = $request->get("phone").rand()."#1";
                  $user_id = $this->signupconnectycude($request->get("name"),$request->get("password"),$request->get("email"),$request->get("phone"),$login_field);
            }
            
            $store->connectycube_user_id = $user_id;
            $store->login_id = $login_field;
            $store->connectycube_password = $request->get("password");
            
            
            $connrctcube = ($store->connectycube_user_id);
            if($connrctcube == "0-email must be unique"){
                    
                Session::flash('message',__("email or password allready added in ConnectCube")); 
                Session::flash('alert-class', 'alert-danger');
                return redirect()->back();
            }
            else
            {     
                $store->save();
                if($request->get("rem_me")==1){
                            setcookie('email', $request->get("email"), time() + (86400 * 30), "/");
                            setcookie('password',$request->get("password"), time() + (86400 * 30), "/");
                            setcookie('rem_me',1, time() + (86400 * 30), "/");
                } 
                
                Session::put("user_id",$store->id);                
                Session::put("role_id",'1');
                Session::flash('message',__("Successful Register")); 
                Session::flash('alert-class', 'alert-success');
                return redirect("userdashboard");
            }
        }
    }
    
    public function postloginuser(Request $request){
        $this->validate($request,[
             'email' =>'required',
             'password' => 'required',
            //  'password_confirmation' => 'required|min:6|same:password',
        ]);

        $getUser=Patient::where("email",$request->get("email"))->where("password",$request->get("password"))->first();
       
        if($getUser){
                if($request->get("rem_me")==1){
                        setcookie('email', $request->get("email"), time() + (86400 * 30), "/");
                        setcookie('password',$request->get("password"), time() + (86400 * 30), "/");
                        setcookie('rem_me',1, time() + (86400 * 30), "/");
                } 

                Session::put("user_id",$getUser->id);                
                Session::put("role_id",'1');
                return redirect("userdashboard");
        }else{
            Session::flash('message',__("message.Login Credentials Are Wrong")); 
            Session::flash('alert-class', 'alert-danger');
            return redirect("patientlogin");
        }
    }

    public function userdashboard(Request $request){
       if(Session::get("user_id")!=""&&Session::get("role_id")=='1'){
          $setting=Setting::find(1);
          $type=$request->get("type");
          $bookdata=array();
          $totalappointment=count(BookAppointment::with("doctorls")->where("user_id",Session::get("user_id"))->get());
          $completeappointment=count(BookAppointment::with("doctorls")->where("user_id",Session::get("user_id"))->where("status",4)->get());
          $pendingappointment=count(BookAppointment::with("doctorls")->where("user_id",Session::get("user_id"))->where("status","!=",4)->get());
          if($type==2){ //past
              $bookdata=BookAppointment::with("doctorls")->where("user_id",Session::get("user_id"))->where("date","<",date('Y-m-d'))->paginate(10);
          }elseif($type==3){ //upcoming
              $bookdata=BookAppointment::with("doctorls")->where("user_id",Session::get("user_id"))->where("date",">",date('Y-m-d'))->paginate(10);
          }else{ //today
              $bookdata=BookAppointment::with("doctorls")->where("user_id",Session::get("user_id"))->where("date",date('Y-m-d'))->paginate(10);
          }
          foreach ($bookdata as $b) {
              if(isset($b->doctorls->department_id)){
                  $data=Services::find($b->doctorls->department_id);
                   if($data){
                      $b->department_name=$data->name;
                   }else{
                      $b->department_name="";
                   }
                  
              }else{
                   $b->department_name="";
              }
          }

          $userdata=Patient::find(Session::get("user_id"));
          if(empty($userdata)){
              $this->logout();
          }
          return view("user.patient.dashboard")->with("setting",$setting)->with("bookdata",$bookdata)->with("type",$type)->with("totalappointment",$totalappointment)->with("completeappointment",$completeappointment)->with("pendingappointment",$pendingappointment)->with("userdata",$userdata);
       }else{
          return redirect("/");
       }
       
    }

    public function logout(){
       Session::forget("user_id");
       Session::forget("role_id");
       return redirect("/");
    }

    public function makeappointment(Request $request){
   
        $this->validate($request, [
            "date"    => "required",
            "slot"    => "required",
            "phone_no"    => "required",
            "message"  => "required"
        ]);
        $slot=explode("#",$request->get("slot"));
        $getappointment=BookAppointment::where("date",date("Y-m-d",strtotime($request->get("date"))))->where("slot_id",isset($slot[0])?$slot[0]:"")->first();
        if($getappointment){
            Session::flash('message',__('message.Slot Already Booked')); 
            Session::flash('alert-class', 'alert-danger');
            return redirect()->back();
        }else{

            if($request->get("payment_type")=="stripe"){
                
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
                    $data->user_id=Session::has("user_id");
                    $data->doctor_id=$request->get("doctor_id");
                    $data->slot_id=isset($slot[0])?$slot[0]:"";
                    $data->slot_name=isset($slot[1])?$slot[1]:"";
                    $data->date=date("Y-m-d",strtotime($request->get("date")));
                    $data->phone=$request->get("phone");
                    $data->payment_mode="Stripe";
                    $data->user_description=$request->get("message");
                    $data->transaction_id=$charge->id;
                    $data->consultation_fees = $request->get("consultation_fees");
                    $data->is_completed = 1;
                    $data->save();
                    $store = new Settlement();
                    $store->book_id = $data->id;
                    $store->status = '0';
                    $store->payment_date = $date->format('Y-m-d');
                    $store->doctor_id = $data->doctor_id;
                    $store->amount = $request->get("consultation_fees");
                    $store->save(); 
                    DB::commit();
                    Session::flash('message',__('message.Appointment Book Successfully')); 
                    Session::flash('alert-class', 'alert-success');
                    return redirect()->back();
                }catch (\Exception $e) {
                          DB::rollback();
                          Session::flash('message',$e); 
                          Session::flash('alert-class', 'alert-danger');
                          return redirect()->back();
                }
            }
            else if($request->get("payment_type")=="Braintree"){
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
                              $data->user_id=Session::get("user_id");
                              $data->doctor_id=$request->get("doctor_id");
                              $data->slot_id=isset($slot[0])?$slot[0]:"";
                              $data->slot_name=isset($slot[1])?$slot[1]:"";
                               $data->date=date("Y-m-d",strtotime($request->get("date")));
                              $data->phone=$request->get("phone_no");
                              $data->user_description=$request->get("message");
                              $data->payment_mode="Braintree";
                              $data->transaction_id=$transaction->id;
                              $data->consultation_fees = $request->get("consultation_fees");
                              $data->is_completed = 1;
                              $data->save();
                              $store = new Settlement();
                              $store->book_id = $data->id;
                              $store->status = '0';
                              $store->payment_date = $date->format('Y-m-d');
                              $store->doctor_id = $data->doctor_id;
                              $store->amount = $request->get("consultation_fees");
                              $store->save();
                              DB::commit();
                              Session::flash('message',__('message.Appointment Book Successfully')); 
                              Session::flash('alert-class', 'alert-success');
                              return redirect()->back();
                      }catch (\Exception $e) {
                              DB::rollback();
                              Session::flash('message',$e); 
                              Session::flash('alert-class', 'alert-danger');
                              return redirect()->back();
                      }
                }else{
                        $errorString = "";
                        foreach($result->errors->deepAll() as $error) {
                            $errorString .= 'Error: ' . $error->code . ": " . $error->message . "\n";
                        }
                        Session::flash('message',$errorString); 
                        Session::flash('alert-class', 'alert-danger');
                        return redirect()->back();
                }
            }
            else if($request->get("payment_type")=="cod"){
                
             
                DB::beginTransaction();
                try {
                      $date = DateTime::createFromFormat('d', 15)->add(new DateInterval('P1M'));

                      $data=new BookAppointment();
                      $data->user_id=Session::get("user_id");
                      $data->doctor_id=$request->get("doctor_id");
                      $data->slot_id=isset($slot[0])?$slot[0]:"";
                      $data->slot_name=isset($slot[1])?$slot[1]:"";
                       $data->date=date("Y-m-d",strtotime($request->get("date")));
                      $data->phone=$request->get("phone_no");
                      $data->user_description=$request->get("message");
                      $data->payment_mode="COD";
                    //   $data->transaction_id="";
                      $data->is_completed = "1";
                      $data->consultation_fees = $request->get("consultation_fees");
                      $data->save();
                      $store = new Settlement();
                      $store->book_id = $data->id;
                      $store->status = '0';
                      $store->payment_date = $date->format('Y-m-d');
                      $store->doctor_id = $data->doctor_id;
                      $store->amount = $request->get("consultation_fees");
                      $store->save();
                      DB::commit();
                      Session::flash('message',__('message.Appointment Book Successfully')); 
                      Session::flash('alert-class', 'alert-success');
                      return redirect()->back();
                }catch (\Exception $e) {
                      DB::rollback();
                      Session::flash('message',$e); 
                      Session::flash('alert-class', 'alert-danger');
                      return redirect()->back();
                }
                
            }
            else if($request->get("payment_type")=="Flutterwave"){
               
                $reference = Flutterwave::generateReference();
                 
                $data1 = PaymentGatewayDetail::where("gateway_name","rave")->get();
              
                $arr = array(); 
                foreach ($data1 as $k) {
                        $arr[$k->gateway_name."_".$k->key] = $k->value;
                }
                  
                    $user = Session::get("user_id");
                    $userinfo = Patient::find($user);
                  
                    $data = [
                                'payment_options' => 'card,banktransfer',
                                'amount' => $request->get("consultation_fees"),
                                'email' => $userinfo->email,
                                'tx_ref' => $reference,
                                'currency' => $arr['rave_currency'],
                                'redirect_url' => route('web-callback'),
                                'customer' => [
                                    'email' => $userinfo->email,
                                    "phonenumber" => $request->get("phone_no"),
                                    "name" => $userinfo->name
                                ],
                    
                                "customizations" => [
                                    "title" => 'Book Appointment',
                                    "description" => "Book Appointment"
                                ]
                    ];
                    
                $payment = Flutterwave::initializePayment($data); 
                // echo "<pre>";print_r($payment);exit;
                
                 
                          $date = DateTime::createFromFormat('d', 15)->add(new DateInterval('P1M'));
                          $data=new BookAppointment();
                          $data->user_id=Session::get("user_id");
                          $data->doctor_id=$request->get("doctor_id");
                          $data->slot_id=isset($slot[0])?$slot[0]:"";
                          $data->slot_name=isset($slot[1])?$slot[1]:"";
                          $data->date=date("Y-m-d",strtotime($request->get("date")));
                          $data->phone=$request->get("phone_no");
                          $data->user_description=$request->get("message");
                          $data->payment_mode="rave";
                          $data->transaction_id=$reference;
                          $data->consultation_fees = $request->get("consultation_fees");
                          $data->save();
                          $store = new Settlement();
                          $store->book_id = $data->id;
                          $store->status = '0';
                          $store->payment_date = $date->format('Y-m-d');
                          $store->doctor_id = $data->doctor_id;
                          $store->amount = $request->get("consultation_fees");
                          $store->save();
                          DB::commit();
                         
                
                if ($payment['status'] !== 'success') {
                    return redirect()->route('payment-failed');
                    Session::flash('message',$errorString); 
                    Session::flash('alert-class', 'alert-danger');
                    return redirect()->back();
                }else{
                    return redirect($payment['data']['link']);
                   
                }
               
            }
            else if($request->get("payment_type")=="Razorpay"){
                $data1 = PaymentGatewayDetail::where("gateway_name","razorpay")->get();
                $arr = array(); 
                if(count($data1)>0){
                    foreach ($data1 as $k) {
                        $arr[$k->gateway_name."_".$k->key] = $k->value;
                    }
                }
                // echo "<pre>";print_r($arr);exit;
                $input = $request->all();    
                
                // $api = new Api($arr['razorpay_razorpay_key'],$arr['razorpay_razorpay_secert']);
                // $payment = $api->payment->fetch($request->get('razorpay_payment_id'));
                // $response = $api->payment->fetch($request->get('razorpay_payment_id'))->capture(array('amount'=>(int)$amount*100)); 
               
                DB::beginTransaction();
                try {
                      $date = DateTime::createFromFormat('d', 15)->add(new DateInterval('P1M'));

                      $data=new BookAppointment();
                      $data->user_id=Session::get("user_id");
                      $data->doctor_id=$request->get("doctor_id");
                      $data->slot_id=isset($slot[0])?$slot[0]:"";
                      $data->slot_name=isset($slot[1])?$slot[1]:"";
                       $data->date=date("Y-m-d",strtotime($request->get("date")));
                      $data->phone=$request->get("phone_no");
                      $data->user_description=$request->get("message");
                      $data->payment_mode="Razorpay";
                      $data->transaction_id=$request->get('razorpay_payment_id');
                      $data->consultation_fees = $request->get("consultation_fees");
                       $data->is_completed = 1;
                      $data->save();
                      $store = new Settlement();
                      $store->book_id = $data->id;
                      $store->status = '0';
                      $store->payment_date = $date->format('Y-m-d');
                      $store->doctor_id = $data->doctor_id;
                      $store->amount = $request->get("consultation_fees");
                      $store->save();
                      DB::commit();
                      Session::flash('message',__('message.Appointment Book Successfully')); 
                      Session::flash('alert-class', 'alert-success');
                      return redirect()->back();
                }catch (\Exception $e) {
                      DB::rollback();
                      Session::flash('message',$e); 
                      Session::flash('alert-class', 'alert-danger');
                      return redirect()->back();
                }
            }  
            else if($request->get("payment_type")=="Paytm"){
               
                
                DB::beginTransaction();
                try {
                      $date = DateTime::createFromFormat('d', 15)->add(new DateInterval('P1M'));

                      $data=new BookAppointment();
                      $data->user_id=Session::get("user_id");
                      $data->doctor_id=$request->get("doctor_id");
                      $data->slot_id=isset($slot[0])?$slot[0]:"";
                      $data->slot_name=isset($slot[1])?$slot[1]:"";
                      $data->date=date("Y-m-d",strtotime($request->get("date")));
                      $data->phone=$request->get("phone_no");
                      $data->user_description=$request->get("message");
                      $data->payment_mode="Paytm";
                      $data->consultation_fees = $request->get("consultation_fees");
                      
                      $data->save();
                      $store = new Settlement();
                      $store->book_id = $data->id;
                      $store->status = '0';
                      $store->payment_date = $date->format('Y-m-d');
                      $store->doctor_id = $data->doctor_id;
                      $store->amount = $request->get("consultation_fees");
                      $store->save();
                      
                        $data1 = PaymentGatewayDetail::where("gateway_name","paytm")->get();
                        $arr = array(); 
                
                        if(count($data1)>0){
                            foreach ($data1 as $k) {
                                $arr[$k->gateway_name."_".$k->key] = $k->value;
                            }
                        }
                        $book_id = $data->id;
                        $o_id=$book_id."-3";
                        $payment = PaytmWallet::with('receive');
                        $amount =  $request->get("consultation_fees");
                        $payment->prepare([
                            'order' => $o_id,
                            'user' => 'redixbit',
                            'mobile_number' => '9904444091',
                            'email' => 'redixbit.user10@gmail.com',
                            'amount' => $amount,
                            'callback_url' => route('paytmstatus')
                        ]);   
                
                      DB::commit();
                      return $payment->receive();
                      
                }catch (\Exception $e) {
                      DB::rollback();
                      Session::flash('message',$e); 
                      Session::flash('alert-class', 'alert-danger');
                      return redirect()->back();
                }
            } 
            else if($request->get("payment_type")=="Paystack"){
                
                $data1 = PaymentGatewayDetail::where("gateway_name","paystack")->get();
                
                $arr = array(); 
                foreach ($data1 as $k) {
                        $arr[$k->gateway_name."_".$k->key] = $k->value;
                }
                  
               
                $curl = curl_init();
                $email = 'admin@gmail.com';
                $amount = $request->get("consultation_fees");
                $callback_url = route('paystack_callback');
                // echo "<pre>";print_r($amount);exit;
                            
                  curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => json_encode([
                      'amount'=>$amount,
                      'email'=>$email,
                      'callback_url' => $callback_url
                    ]),
                    CURLOPT_HTTPHEADER => [
                      "authorization: Bearer ".$arr['paystack_secert_key']."", 
                      "content-type: application/json",
                      "cache-control: no-cache"
                    ],
                  ));
                  $response = curl_exec($curl);
                  $err = curl_error($curl);
                  if($err){
                    die('Curl returned error: ' . $err);
                  }
                  $tranx = json_decode($response, true);   
                //   echo "<pre>";print_r($tranx);exit;
                            
                if($tranx['data']['reference']){  
                    DB::beginTransaction();
                    try {
                          $date = DateTime::createFromFormat('d', 15)->add(new DateInterval('P1M'));
    
                          $data=new BookAppointment();
                          $data->user_id=Session::get("user_id");
                          $data->doctor_id=$request->get("doctor_id");
                          $data->slot_id=isset($slot[0])?$slot[0]:"";
                          $data->slot_name=isset($slot[1])?$slot[1]:"";
                          $data->date=date("Y-m-d",strtotime($request->get("date")));
                          $data->phone=$request->get("phone_no");
                          $data->user_description=$request->get("message");
                          $data->payment_mode="Paystack";
                          $data->is_completed='0';
                          $data->transaction_id=$tranx['data']['reference'];
                          $data->consultation_fees = $request->get("consultation_fees");
                          $data->save();
                          $store = new Settlement();
                          $store->book_id = $data->id;
                          $store->status = '0';
                          $store->payment_date = $date->format('Y-m-d');
                          $store->doctor_id = $data->doctor_id;
                          $store->amount = $request->get("consultation_fees");
                          $store->save();
                          DB::commit();
                             
                    }catch (\Exception $e) {
                              DB::rollback();
                    }
                }else{
                    die('something getting worng');
                }
           
                if(!$tranx['status']){
                    print_r('API returned error: ' . $tranx['message']);
                }
                return Redirect($tranx['data']['authorization_url']);
             
            }
        }
    }
    
    // public function paystack_callback(Request $request){      
    //   $data1 = PaymentGatewayDetail::where("gateway_name","paystack")->get();
      
    //   $arr = array(); 
    //   foreach ($data1 as $k) {
    //         $arr[$k->gateway_name."_".$k->key] = $k->value;
    //   }
    //   $curl = curl_init();
    //     $reference = $request->get("reference");
    //     if(!$reference){
    //       die('No reference supplied');
    //     }
    //     curl_setopt_array($curl, array(
    //       CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
    //       CURLOPT_RETURNTRANSFER => true,
    //       CURLOPT_HTTPHEADER => [
    //         "accept: application/json",
    //         "authorization: Bearer ".$arr['paystack_secert_key']."", 
    //         "cache-control: no-cache"
    //       ],
    //     ));
    //     $response = curl_exec($curl);
    //     $err = curl_error($curl);
    //     if($err){
    //      return redirect()->route('payment-failed');
    //     }
    //     $tranx = json_decode($response);
    //     if(!$tranx->status){
    //      return redirect()->route('payment-failed');
    //     }
    //     if('success' == $tranx->data->status){
    //         if(Session::get("type")==1){
    //             $data = BookAppointment::where("transaction_id",$reference)->first();
    //             $date = DateTime::createFromFormat('d', 15)->add(new DateInterval('P1M'));
    //             $data->payment_mode="Paystack";
    //             $data->transaction_id=$reference;
    //             $data->is_completed='1';
    //             $data->status='2';
    //             $data->save();
    //             $store = new Settlement();
    //             $store->book_id = $data->id;
    //             $store->status = '0';
    //             $store->payment_date = $date->format('Y-m-d');
    //             $store->doctor_id = $data->doctor_id;
    //             $store->amount = $data->consultation_fees;
    //             $store->save();
    //             $msg="You have a new upcoming appointment";
    //             $user=User::find(1);
    //             $android=$this->send_notification_android($user->android_key,$msg,$request->get("doctor_id"),"doctor_id",$data->id);
    //             $ios=$this->send_notification_IOS($user->ios_key,$msg,$request->get("doctor_id"),"doctor_id",$data->id);
    //             try {
    //                     $user=Doctors::find($request->get("doctor_id")); 
                            
    //                     $result=Mail::send('email.Ordermsg', ['user' => $user], function($message) use ($user){
    //                         $message->to($user->email,$user->name)->subject(__('message.System Name'));
    //                     });
                                          
    //             } catch (\Exception $e) {
    //             }
    //         }
    //         if(Session::get("type")==2){
    //             $data = Subscriber::where("transaction_id",$reference)->first();
    //             $data->payment_type="4";
    //             $data->transaction_id=$reference;
    //             $data->status='2';
    //             $data->is_complet='1';
    //             $data->save();
    //         }
    //         $doctor_id = $data->doctor_id;
    //         Session::flash('message',__('message.Appointment Book Successfully')); 
    //         Session::flash('alert-class', 'alert-success');
    //         return redirect('viewdoctor/'.$doctor_id);
    //     }else{ //fail
    //         Session::flash('message',__('message.Something Wrong')); 
    //         Session::flash('alert-class', 'alert-danger');
    //         return redirect()->back();
    //     }
    // }
    
    public function web_rave_callback(Request $request){
        $transactionID = Flutterwave::getTransactionIDFromCallback();
      
        $data = Flutterwave::verifyTransaction($transactionID);
            $data1 = BookAppointment::where("transaction_id",$data['data']['tx_ref'])->first();
            $data1->is_completed = 1;
            $data1->save();    
        
        $doctor_id = $data1->doctor_id;
        Session::flash('message',__('message.Appointment Book Successfully')); 
        Session::flash('alert-class', 'alert-success');
        return redirect('viewdoctor/'.$doctor_id);
    }
    
   

    public function userfavorite($doc_id){
        if(Session::has("user_id")&&Session::get("role_id")=='1'){
            $getFav=FavoriteDoc::where("doctor_id",$doc_id)->where("user_id",Session::get("user_id"))->first();
            if($getFav){
               $msg=__('message.Doctor remove in Favorite list');
               $op="0";
               $getFav->delete();
            }else{
               $store=new FavoriteDoc();
               $store->doctor_id=$doc_id;
               $store->user_id=Session::get("user_id");
               $store->save();
               $op='1';
               $msg=__('message.Doctor add in Favorite list');
            }  
            $data=array("msg"=>$msg,"class"=>"alert-success","op"=>$op); 
        }else{
            $data=array("msg"=>__('message.Please')." <a href=".url('patientlogin').">".__('message.Login')."</a> ".__('message.Your Account')."","class"=>"alert-danger","op"=>'0'); 
        }

        return json_encode($data);
    }

    public function favouriteuser(){
       if(Session::get("user_id")!=""&&Session::get("role_id")=='1'){
          $setting=Setting::find(1);
          $userdata=Patient::find(Session::get('user_id'));
          $userfavorite=FavoriteDoc::with("doctorls")->where("user_id",Session::get("user_id"))->paginate(9);
          foreach ($userfavorite as $k) {  
                if($k->doctorls){
                    $k->doctorls->avgratting=Review::where('doc_id',$k->doctor_id)->avg('rating');
                    $k->doctorls->totalreview=count(Review::where('doc_id',$k->doctor_id)->get());
                    $k->doctorls->is_fav=1;
                    if(isset($k->doctorls->department_id)&&$k->doctorls->department_id!=""){
                        $getservice=Services::find($k->doctorls->department_id);
                        $k->doctorls->department_name=$getservice->name;
                    }else{
                        $k->doctorls->department_name="";
                    }
                           
                }
          }   
          return view("user.patient.favourite")->with("userdata",$userdata)->with("setting",$setting)->with("userfavorite",$userfavorite);
       }
       else{
          return redirect('/');
       }
    }

    public function viewschedule(){
       if(Session::get("user_id")!=""&&Session::get("role_id")=='1'){
          $setting=Setting::find(1);
          $userdata=Patient::find(Session::get('user_id'));          
          return view("user.patient.scheduleappointment")->with("userdata",$userdata)->with("setting",$setting);
       }
       else{
          return redirect('/');
       }
    }
    
    public function viewappointment($id){
        if(Session::get("user_id")!=""&&Session::get("role_id")=='1'){
          $setting=Setting::find(1);
          $userdata=Patient::find(Session::get('user_id')); 
          $viewappointment = BookAppointment::with('doctorls')->find($id);
          return view("user.doctor.viewappoint")->with("userdata",$userdata)->with("setting",$setting)->with("viewappointment",$viewappointment);
       }
       else{
          return redirect('/');
       }
    }

    public function changepassword(){
      if(Session::get("user_id")!=""&&Session::get("role_id")=='1'){
        $setting=Setting::find(1);
        $userdata=Patient::find(Session::get("user_id"));
        return view("user.patient.changepassword")->with("userdata",$userdata)->with("setting",$setting);
      }else{
         return redirect('/');
      }
    }

    public function checkuserpwd(Request $request){
        $data=Patient::find(Session::get("user_id"));
        if($data){
            if($data->password==$request->get("cpwd")){
                return 1;
            }else{
                return 0;
            }
        }else{
           return redirect("/");
        }
    }

    public function updateuserpassword(Request $request){
          $data=Patient::find(Session::get("user_id"));
          $data->password=$request->get("npwd");
          $data->save();
          Session::flash('message',__('message.Password Change Successfully')); 
          Session::flash('alert-class', 'alert-success');
          return redirect()->back();
    }

    public function userreview(){
      if(Session::get("user_id")!=""&&Session::get("role_id")=='1'){
          $setting=Setting::find(1);
          $userdata=Patient::find(Session::get("user_id"));
          $datareview=Review::with("doctorls")->where("user_id",Session::get("user_id"))->orderby("id","DESC")->get();
          foreach ($datareview as $k) {
             $ddp=Services::find($k->doctorls->department_id);
             if($ddp){
                $k->doctorls->department_name=$ddp->name;
             }else{
                $k->doctorls->department_name="";
             }
          }
          return view("user.patient.review")->with("setting",$setting)->with("userdata",$userdata)->with("datareview",$datareview);
      }else{
          return redirect("/");
      }
    }

    public function usereditprofile(){
        if(Session::get("user_id")!=""&&Session::get("role_id")=='1'){
          $setting=Setting::find(1);
          $userdata=Patient::find(Session::get("user_id"));
          return view("user.patient.editprofile")->with("setting",$setting)->with("userdata",$userdata);
        }else{
          return redirect("/");
        }
    }

    public function updateuserprofile(Request $request){
      $user=Patient::find(Session::get("user_id"));
      $findemail=Patient::where("email",$request->get("email"))->where("id","!=",Session::get("user_id"))->first();
      if($findemail){
           Session::flash('message',__('message.Email Id Already Use By Other User')); 
           Session::flash('alert-class', 'alert-danger');
           return redirect()->back();
      }else{

           $img=$user->profile_pic;
           $rel_url=$user->profile_pic;
           if ($request->hasFile('image')) 
              {
                  $file = $request->file('image');
                  $filename = $file->getClientOriginalName();
                  $extension = $file->getClientOriginalExtension() ?: 'png';
                  $folderName = '/upload/profile/';
                  $picture = time() . '.' . $extension;
                  $destinationPath = public_path() . $folderName;
                  $request->file('image')->move($destinationPath, $picture);
                  $img =$picture;                
                  $image_path = public_path() ."/upload/profile/".$rel_url;
                  if(file_exists($image_path)&&$rel_url!="") {
                      try {
                            unlink($image_path);
                      }catch(Exception $e) {
                                                  
                      }                        
                  }
            }
           $user->name=$request->get("name");
           $user->email=$request->get("email");
           $user->phone=$request->get("phone");
           $user->profile_pic=$img;
           $user->save();
           Session::flash('message',__('message.Profile Update Successfully')); 
           Session::flash('alert-class', 'alert-success');
           return redirect()->back();
      }
      //dd($request->all());
    }
    
    
      public function resetpassword($code){
            $setting = Setting::find(1);
            $data=Resetpassword::where("code",$code)->first();
            if($data){
              return view('user.resetpwd')->with("id",$data->user_id)->with("code",$code)->with("type",$data->type)->with("setting",$setting);
            }else{
              return view('user.resetpwd')->with("msg",__('message.Code Expired'))->with("setting",$setting);
            }
      }
      public function resetnewpwd(Request $request){
           $setting = Setting::find(1);
            if($request->get('id')==""){
                return view('user.resetpwd')->with("msg",__('message.pwd_reset'))->with("setting",$setting);
            }else{
                if($request->get("type")==1){
                     $user=Patient::find($request->get('id'));
                }else{
                    $user=Doctors::find($request->get('id'));
                }
                $user->password=$request->get('npwd');
                $user->save();
                $codedel=Resetpassword::where('user_id',$request->get("id"))->delete();
                return view('user.pwdsucess')->with("msg",__('message.pwd_reset'))->with("setting",$setting);
            }
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



}
