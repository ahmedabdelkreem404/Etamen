<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Session;
use validate;
use Sentinel;
use DB;
use DataTables;
use App\Models\BookAppointment;
use App\Models\PaymentGatewayDetail;
use App\Models\Patient;
use DateTime;
use PaytmWallet;
use Razorpay\Api\Api;
use KingFlamez\Rave\Facades\Rave as Flutterwave;
use DateInterval;
use App\Models\TokenData;
use App\Models\User;
use Mail;
use App\Models\Settlement;
use App\Models\Subscription;
use App\Models\Setting;
use App\Models\Subscriber;
use App\Models\Doctors;
use Stripe\Stripe;
use Stripe\Charge;
class MakePaymentController extends Controller
{

    public function show_make_payment(Request $request){
          if($request->get("type")==1){
              $data = BookAppointment::find($request->get('id'));
              $amount = $data->consultation_fees;
          }else{
              $data = Subscriber::find($request->get('id'));
              $amount = $data->amount;
          }
         
         Session::put("type",$request->get("type"));
         $arr = array();
         $data1 = PaymentGatewayDetail::all();       
         foreach ($data1 as $k) {
            $arr[$k->gateway_name."_".$k->key] = $k->value;
         }
         $token = "";
         if(isset($arr['braintree_is_active'])&&$arr['braintree_is_active']=='1'){
            $gateway = new \Braintree\Gateway([
                   'environment' => $arr['braintree_environment'],
                   'merchantId' => $arr['braintree_merchant_id'],
                   'publicKey' => $arr['braintree_public_key'],
                   'privateKey' => $arr['braintree_private_key']
             ]);
             $token=$gateway->ClientToken()->generate();
         }
         return view("payment")->with("data",$data)->with("paymentdetail",$arr)->with("braintree_token",$token)->with("amount",$amount);
    }
    
    public function show_braintree_payment(Request $request){
          if($request->get("type")==1){
              $data = BookAppointment::find($request->get('id'));
              $amount = $data->consultation_fees;
          }else{
              $data = Subscriber::find($request->get('id'));
              $amount = $data->amount;
          }
         
         Session::put("type",$request->get("type"));
         $arr = array();
         $data1 = PaymentGatewayDetail::all();       
         foreach ($data1 as $k) {
            $arr[$k->gateway_name."_".$k->key] = $k->value;
         }
         $token = "";
         if(isset($arr['braintree_is_active'])&&$arr['braintree_is_active']=='1'){
            $gateway = new \Braintree\Gateway([
                   'environment' => $arr['braintree_environment'],
                   'merchantId' => $arr['braintree_merchant_id'],
                   'publicKey' => $arr['braintree_public_key'],
                   'privateKey' => $arr['braintree_private_key']
             ]);
             $token=$gateway->ClientToken()->generate();
         }
         return view("braintree")->with("data",$data)->with("paymentdetail",$arr)->with("braintree_token",$token)->with("amount",$amount);
    }
    
    public function show_pay_razorpay(Request $request){
        
        if($request->get("type")==1){
              $data = BookAppointment::find($request->get('id'));
              $amount = $data->consultation_fees;
          }else{
              $data = Subscriber::find($request->get('id'));
              $amount = $data->amount;
          }
         
         Session::put("type",$request->get("type"));

         $arr = array();
         $data1 = PaymentGatewayDetail::all();       
         foreach ($data1 as $k) {
            $arr[$k->gateway_name."_".$k->key] = $k->value;
         }
         $token = "";
        
         return view("razorpay")->with("data",$data)->with("paymentdetail",$arr)->with("amount",$amount);
    }

    public function payment_success(){
       return view('payment_success');
    }

    public function payment_failed(){
        return view('payment_failed');
    }

    public function save_braintree(Request $request){
       // dd($request->all());
       
       $data1 = PaymentGatewayDetail::where("gateway_name","braintree")->get();
       if(count($data1)>0){
             $arr = array(); 
            foreach ($data1 as $k) {
               $arr[$k->gateway_name."_".$k->key] = $k->value;
            }
              $gateway = new \Braintree\Gateway([
                           'environment' => $arr['braintree_environment'],
                         'merchantId' => $arr['braintree_merchant_id'],
                         'publicKey' => $arr['braintree_public_key'],
                         'privateKey' => $arr['braintree_private_key']
              ]);
              $amount = $request->get("amount");
              $nonce = $request->get("payment_method_nonce");

              $result = $gateway->transaction()->sale([
                  'amount' => $amount,
                  'paymentMethodNonce' => $nonce,
                  'options' => [
                      'submitForSettlement' => true
                  ]
              ]);
             //  echo "<pre>";print_r($result);exit;
              if ($result->success) {
                            $transaction = $result->transaction;
                            if(Session::get("type")==1){
                                $data = BookAppointment::find($request->get('id'));
                                $date = DateTime::createFromFormat('d', 15)->add(new DateInterval('P1M'));
                                $data->payment_mode="Braintree";
                                $data->transaction_id=$transaction->id;
                                $data->save();
                                $store = new Settlement();
                                $store->book_id = $data->id;
                                $store->status = '0';
                                $store->payment_date = $date->format('Y-m-d');
                                $store->doctor_id = $data->doctor_id;
                                $store->amount = $amount;
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
                            }
                            if(Session::get("type")==2){
                                $data = Subscriber::find($request->get('id'));
                                $data->payment_type="1";
                                $data->transaction_id=$transaction->id;
                                $data->is_complet='1';
                                $data->status='2';
                                $data->save();
                            }
                            return redirect()->route('payment-success');
              } else {
                  $errorString = "";

                  foreach($result->errors->deepAll() as $error) {
                      $errorString .= 'Error: ' . $error->code . ": " . $error->message . "\n";
                  }
                 
                  return redirect()->route('payment-failed');
              }
       }else{
           return redirect()->route('payment-failed');
       }
   }

   public function razor_payment(Request $request){
      
       if($request->get("session_id")==1){
            $data = BookAppointment::find($request->get('id'));
            $amount = $data->consultation_fees;
       }else{
            $data = Subscriber::find($request->get('id'));
            $amount = $data->amount;
       }
       $data1 = PaymentGatewayDetail::where("gateway_name","razorpay")->get();
       if(count($data1)>0){
         $arr = array(); 
          foreach ($data1 as $k) {
            $arr[$k->gateway_name."_".$k->key] = $k->value;
          }
         // echo "<pre>";print_r($arr);exit;
         $input = $request->all();        
           $api = new Api($arr['razorpay_razorpay_key'],$arr['razorpay_razorpay_secert']);
           $payment = $api->payment->fetch($request->get('razorpay_payment_id'));
           
           if($request->get('razorpay_payment_id')) 
           {
               
               try 
               {
                   $response = $api->payment->fetch($request->get('razorpay_payment_id'))->capture(array('amount'=>(int)$amount*100)); 
                   
                     if($request->get("session_id")==1){
                        $date = DateTime::createFromFormat('d', 15)->add(new DateInterval('P1M'));
                        $data->payment_mode="Razorpay";
                        $data->transaction_id=$request->get('razorpay_payment_id');
                        $data->is_completed='1';
                        $data->save();
                        $store = new Settlement();
                        $store->book_id = $data->id;
                        $store->status = '0';
                        $store->payment_date = $date->format('Y-m-d');
                        $store->doctor_id = $data->doctor_id;
                        $store->amount = $amount;
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
                    }
                        if($request->get("session_id")==2){
                            $data->payment_type="3";
                            $data->transaction_id=$request->get('razorpay_payment_id');
                            $data->status='2';
                            $data->is_complet='1';
                            $data->save();
                        }
                        return redirect()->route('payment-success');
                  
               }
               catch (\Exception $e) 
               {
                  
                        $data->delete();
                   
                        return redirect()->route('payment-failed');
               }           
           }
       }else{
           
            return redirect()->route('payment-failed');
       }
      
   }


    public function show_paystack_payment(Request $request){        
        $data1 = PaymentGatewayDetail::where("gateway_name","paystack")->get();
        Session::put("type",$request->get("type"));
        if(Session::get("type")==1){
            $data = BookAppointment::find($request->get('id'));
            $amount = $data->consultation_fees*100;
        }else{
            $data = Subscriber::find($request->get('id'));
            $amount = $data->amount*100;
        }
       // echo $amount;exit;
        $arr = array(); 
          foreach ($data1 as $k) {
            $arr[$k->gateway_name."_".$k->key] = $k->value;
          }
        $curl = curl_init();
          $email = 'admin@gmail.com';
         // $amount = (int)$data->consultation_fees; 
          $callback_url = route('paystackcallback');
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
            //echo "<pre>";print_r($tranx);exit;
            if($tranx['data']['reference']){
                  if(Session::get("type")==1){
                    $data->payment_mode="Paystack";           
                    $data->transaction_id=$tranx['data']['reference'];
                    $data->is_completed='0';
                    $data->save();  
                   }else{
                        $data->payment_type="4"; 
                        $data->status ='2';
                        $data->transaction_id=$tranx['data']['reference'];
                        $data->save();  
                   }
            }else{
                die('something getting worng');
            }
           
             if(!$tranx['status']){
               print_r('API returned error: ' . $tranx['message']);
             }
             return Redirect($tranx['data']['authorization_url']);
    }

    public function paystackcallback(Request $request){      
      $data1 = PaymentGatewayDetail::where("gateway_name","paystack")->get();
      
      $arr = array(); 
      foreach ($data1 as $k) {
            $arr[$k->gateway_name."_".$k->key] = $k->value;
      }
      $curl = curl_init();
        $reference = $request->get("reference");
        if(!$reference){
          die('No reference supplied');
        }
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_HTTPHEADER => [
            "accept: application/json",
            "authorization: Bearer ".$arr['paystack_secert_key']."", 
            "cache-control: no-cache"
          ],
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        if($err){
         return redirect()->route('payment-failed');
        }
        $tranx = json_decode($response);
        if(!$tranx->status){
         return redirect()->route('payment-failed');
        }
        if('success' == $tranx->data->status){
                           if(Session::get("type")==1){
                                $data = BookAppointment::where("transaction_id",$reference)->first();
                                $date = DateTime::createFromFormat('d', 15)->add(new DateInterval('P1M'));
                                $data->payment_mode="Paystack";
                                $data->transaction_id=$reference;
                                $data->is_completed='1';
                                $data->status='2';
                                $data->save();
                                $store = new Settlement();
                                $store->book_id = $data->id;
                                $store->status = '0';
                                $store->payment_date = $date->format('Y-m-d');
                                $store->doctor_id = $data->doctor_id;
                                $store->amount = $data->consultation_fees;
                                $store->save();
                                $msg="You have a new upcoming appointment";
                                $user=User::find(1);
                                $android=$this->send_notification_android($user->android_key,$msg,$request->get("doctor_id"),"doctor_id",$data->id);
                                $ios=$this->send_notification_IOS($user->ios_key,$msg,$request->get("doctor_id"),"doctor_id",$data->id);
                                try {
                                        $user=Doctors::find($request->get("doctor_id")); 
                                            
                                        $result=Mail::send('email.Ordermsg', ['user' => $user], function($message) use ($user){
                                            $message->to($user->email,$user->name)->subject(__('message.System Name'));
                                        });
                                                          
                                } catch (\Exception $e) {
                                }
                            }
                            if(Session::get("type")==2){
                                $data = Subscriber::where("transaction_id",$reference)->first();
                                $data->payment_type="4";
                                $data->transaction_id=$reference;
                                $data->status='2';
                                $data->is_complet='1';
                                $data->save();
                            }
            return redirect()->route('payment-success');
        }else{ //fail
            return redirect()->route('payment-failed');
        }
    }

    public function paystack_callback(Request $request){      
      $data1 = PaymentGatewayDetail::where("gateway_name","paystack")->get();
      
      $arr = array(); 
      foreach ($data1 as $k) {
            $arr[$k->gateway_name."_".$k->key] = $k->value;
      }
      $curl = curl_init();
        $reference = $request->get("reference");
        
        if(!$reference){
          die('No reference supplied');
        }
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_HTTPHEADER => [
            "accept: application/json",
            "authorization: Bearer ".$arr['paystack_secert_key']."", 
            "cache-control: no-cache"
          ],
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
      
        $tranx = json_decode($response);
        if(!$tranx->status){
         return redirect()->route('payment-failed');
        }
        if('success' == $tranx->data->status){
                                $data = BookAppointment::where("transaction_id",$reference)->first();
                                $date = DateTime::createFromFormat('d', 15)->add(new DateInterval('P1M'));
                                $data->payment_mode="Paystack";
                                $data->transaction_id=$reference;
                                $data->is_completed='1';
                                $data->status='2';
                                $data->save();
                                $store = new Settlement();
                                $store->book_id = $data->id;
                                $store->status = '0';
                                $store->payment_date = $date->format('Y-m-d');
                                $store->doctor_id = $data->doctor_id;
                                $store->amount = $data->consultation_fees;
                                $store->save();
                                $msg="You have a new upcoming appointment";
                                $user=User::find(1);
                                $android=$this->send_notification_android($user->android_key,$msg,$request->get("doctor_id"),"doctor_id",$data->id);
                                $ios=$this->send_notification_IOS($user->ios_key,$msg,$request->get("doctor_id"),"doctor_id",$data->id);
                                
                            
            $doctor_id = $data->doctor_id;
            Session::flash('message',__('message.Appointment Book Successfully')); 
            Session::flash('alert-class', 'alert-success');
            return redirect('viewdoctor/'.$doctor_id);
        }else{ //fail
            Session::flash('message',__('message.Something Wrong')); 
            Session::flash('alert-class', 'alert-danger');
            return redirect()->back();
        }
    }
    
    public function show_stripe_payment(Request $request){    
        $setting = Setting::find(1);
        Session::put("type",$request->get("type"));
        if(Session::get("type")==1){
            $data = BookAppointment::find($request->get('id'));
        }else{
            $data = Subscriber::find($request->get('id'));
        }
        return view('stripe',compact('data','setting'));
    }
    
    public function stripe_callback(Request $request){
        Session::put("type",$request->get("type"));
        $id = $request->get("id");
        
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $unique_id = uniqid();
     
        $charge = \Stripe\Charge::create(array(
               'description' => "Amount: ".$request->get("consultation_fees").' - '. $unique_id,
               'source' => $request->get("stripeToken"),                    
               'amount' => (int)($request->get("consultation_fees") * 100), 
               'currency' => env('STRIPE_CURRENCY')
        ));
       
        
        if(Session::get("type")==1){
            $data1 = BookAppointment::find($id);
            $data1->transaction_id = $charge->id;
            $data1->payment_mode = $request->get("payment_type");
            $data1->is_completed = 1;
            $data1->save();    
        }else{
            $data1 = Subscriber::find($id);
            $data1->status='2';
            $data1->is_complet='1';
            $data1->transaction_id = $charge->id;
            $data1->payment_mode = "5";
            $data1->save(); 
        }
          
        return redirect()->route('payment-success');
    }
    
    public function show_rave_payment(Request $request){
       // dd($request->all());
        $reference = Flutterwave::generateReference();
        $data1 = PaymentGatewayDetail::where("gateway_name","rave")->get();
          $arr = array(); 
          
          foreach ($data1 as $k) {
                $arr[$k->gateway_name."_".$k->key] = $k->value;
          }
            Session::put("type",$request->get("type"));
         if(Session::get("type")==1){
            $data = BookAppointment::find($request->get('id'));
            $userinfo = Patient::find($data->user_id);
            $data = [
                        'payment_options' => 'card,banktransfer',
                        'amount' => $data->consultation_fees,
                        'email' => $userinfo->email,
                        'tx_ref' => $reference,
                        'currency' => $arr['rave_currency'],
                        'redirect_url' => route('rave-callback'),
                        'customer' => [
                            'email' => $userinfo->email,
                            "phonenumber" => $userinfo->phone,
                            "name" => $userinfo->name
                        ],
            
                        "customizations" => [
                            "title" => 'Book Appointment',
                            "description" => "Book Appointment"
                        ]
            ];
        }else{
            $data = Subscriber::find($request->get('id'));
            $userinfo = Doctors::find($data->doctor_id);
            $data = [
                        'payment_options' => 'card,banktransfer',
                        'amount' => $data->amount,
                        'email' => $userinfo->email,
                        'tx_ref' => $reference,
                        'currency' => $arr['rave_currency'],
                        'redirect_url' => route('rave-callback'),
                        'customer' => [
                            'email' => $userinfo->email,
                            "phonenumber" => $userinfo->phone,
                            "name" => $userinfo->name
                        ],
            
                        "customizations" => [
                            "title" => 'package',
                            "description" => "package"
                        ]
            ];
        }
         

        $payment = Flutterwave::initializePayment($data);  
        //echo "<pre>";print_r($payment);exit;
        if(Session::get("type")==1){
            $data = BookAppointment::find($request->get('id'));
            $data->transaction_id=$reference;
            $data->is_completed='0';
            $data->save(); 
        }else{
            $data = Subscriber::find($request->get('id'));
            $data->transaction_id=$reference;
            $data->status='1';
            $data->save(); 
        }

        if ($payment['status'] !== 'success') {
            
            return redirect()->route('payment-failed');
        }

        return redirect($payment['data']['link']);
        
    }


    public function rave_callback(Request $request){
        $transactionID = Flutterwave::getTransactionIDFromCallback();
        $data = Flutterwave::verifyTransaction($transactionID);
       
        if(Session::get("type")==1){
              $data1 = BookAppointment::where("transaction_id",$data['data']['tx_ref'])->first();
              $data1->is_completed = 1;
            //   $data1->payment_mode = "Flutterwave";
              $data1->save();    
        }else{
            $data1 = Subscriber::where("transaction_id",$data['data']['tx_ref'])->first();
            $data1->status='2';
            $data1->is_complet='1';
            //  $data1->payment_mode = "Flutterwave";
            $data1->save(); 
        }
          
        return redirect()->route('payment-success');
    }

    public function store_paytm_data(Request $request){
      $data1 = PaymentGatewayDetail::where("gateway_name","paytm")->get();
      $arr = array(); 
        Session::put("type",$request->get("type"));
      foreach ($data1 as $k) {
            $arr[$k->gateway_name."_".$k->key] = $k->value;
      }
       if(Session::get("type")==1){
            $data = BookAppointment::find($request->get('id'));
            $amount = $data->consultation_fees;
        }else{
            $data = Subscriber::find($request->get('id'));
            $amount = $data->amount;
        }
        $o_id=$request->get('id')."-".$request->get("type");
   $payment = PaytmWallet::with('receive');

            $payment->prepare([
          'order' => $o_id,
          'user' => 'redixbit',
          'mobile_number' => '9904444091',
          'email' => 'redixbit.user10@gmail.com',
          'amount' => $amount,
          'callback_url' => route('paytmstatus')
        ]);   


        
        return $payment->receive();
    }

    public function paymentpaytmCallback(Request $request){
        $transaction = PaytmWallet::with('receive');
        
        $response = $transaction->response();
        // dd($response);
        $order_id = $transaction->getOrderId();
        $arr=explode("-",$order_id);
        $o_id=$arr[0];
        $type=$arr[1];
        
      
        if($transaction->isSuccessful()){
            if($type==1 || $type==3){
              $data1 = BookAppointment::find($o_id);
              $data1->is_completed = 1;
              $data1->transaction_id=$transaction->getTransactionId();
              $data1->save();
               
            }else{
                    $data1 = Subscriber::find($o_id);
                    $data1->payment_type="Paytm";
                    $data1->transaction_id=$transaction->getTransactionId();
                    $data1->status='2';
                    $data1->save();
                                
            }
            if($type==1 || $type==2){
                 return redirect()->route('payment-success');
             }
             else if($type==3){
                    Session::flash('message',__('message.Appointment Book Successfully')); 
                    Session::flash('alert-class', 'alert-success');
                    return redirect()->back();
             }
            
        }else {
             if($type==1 || $type==2){
                 return redirect()->route('payment-failed');
             }
             else if($type==3){
                  Session::flash('message',"technical error please try again"); 
                    Session::flash('alert-class', 'alert-danger');
                    return redirect()->back();
             }
            
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
