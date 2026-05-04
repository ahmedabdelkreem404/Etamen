<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use Auth;
use Sentinel;
use App\Models\PaymentGatewayDetail;
use App\Models\Seller;
class PaymentSettingController extends Controller
{
    //

    public function show_payment_setting(){
    	 if(Session::get("payment_next")==""){
    	 	Session::put("payment_next",'1');
    	 }
    	 $arr = array();
    	 $data = PaymentGatewayDetail::all();    	 
    	 foreach ($data as $k) {
    	 	$arr[$k->gateway_name."_".$k->key] = $k->value;
    	 }
    	 return view("admin.paymentsetting")->with("arr",$arr);
    }

   

    public function show_update_gateway(Request $request){
    	//dd($request->all());
    	$next = 1;
    	if($request->get("payment_gateway")=="braintree"){
    		$this->admin_update_payment_key("braintree","environment",$request->get("environment"));
    		$this->admin_update_payment_key("braintree","merchant_id",$request->get("merchant_id"));
    		$this->admin_update_payment_key("braintree","public_key",$request->get("public_key"));
    		$this->admin_update_payment_key("braintree","private_key",$request->get("private_key"));
    		$this->admin_update_payment_key("braintree","tokenization_key",$request->get("tokenization_key"));
    		$this->admin_update_payment_key("braintree","is_active",$request->get("is_active"));
    		$next = 2;
    	}else if($request->get("payment_gateway")=="razorpay"){
    		$this->admin_update_payment_key("razorpay","razorpay_key",$request->get("razorpay_key"));
    		$this->admin_update_payment_key("razorpay","razorpay_secert",$request->get("razorpay_secert"));
    		$this->admin_update_payment_key("razorpay","is_active",$request->get("is_active"));
    		$next = 3;
    	}else if($request->get("payment_gateway")=="paystack"){
    		$this->admin_update_payment_key("paystack","public_key",$request->get("public_key"));
    		$this->admin_update_payment_key("paystack","secert_key",$request->get("secert_key"));
    		$this->admin_update_payment_key("paystack","is_active",$request->get("is_active"));
    		$this->admin_update_payment_key("paystack","payment_url","https://api.paystack.co");
    		$this->admin_update_payment_key("paystack","merchant_email",Sentinel::getUser()->email);
    		$next = 4;
    	}else if($request->get("payment_gateway")=="paytm"){
    		$this->admin_update_payment_key("paytm","merchant_id",$request->get("merchant_id"));
    		$this->admin_update_payment_key("paytm","merchant_key",$request->get("merchant_key"));
    		$this->admin_update_payment_key("paytm","merchant_website",$request->get("merchant_website"));
    		$this->admin_update_payment_key("paytm","environment",$request->get("environment"));
    		$this->admin_update_payment_key("paytm","channel",$request->get("channel"));
    		$this->admin_update_payment_key("paytm","industry_type",$request->get("industry_type"));
    		$this->admin_update_payment_key("paytm","is_active",$request->get("is_active"));
    		$next = 5;
    	}else{
    		$this->admin_update_payment_key("rave","public_key",$request->get("public_key"));
    		$this->admin_update_payment_key("rave","secert_key",$request->get("secert_key"));
    		$this->admin_update_payment_key("rave","title",$request->get("title"));
    		$this->admin_update_payment_key("rave","environment",$request->get("environment"));
    		$this->admin_update_payment_key("rave","RAVE_PREFIX","rave");
            $this->admin_update_payment_key("rave","RAVE_SECRET_HASH", env('RAVE_SECRET_HASH', ''));
    		$this->admin_update_payment_key("rave","logo",$request->get("logo"));
    		$this->admin_update_payment_key("rave","is_active",$request->get("is_active"));
            $this->admin_update_payment_key("rave","encryption_key",$request->get("encryption_key"));
            $this->admin_update_payment_key("rave","country",$request->get("country"));
            $this->admin_update_payment_key("rave","currency",$request->get("currency")); 
    		$next = 1;
    	}

    	Session::flash('message',__("message.Payment Key Update Successfully")); 
    	Session::put("payment_next",$next);
        Session::flash('alert-class', 'alert-success');
        return redirect()->route('payment-setting');
    }


    public function admin_update_payment_key($payment_key,$key,$value){
    	$data = PaymentGatewayDetail::where("gateway_name",$payment_key)->where("key",$key)->first();
    	if($data){
    		$data->value = $value;
    		$data->save();
    	}else{
    		$data = new PaymentGatewayDetail();
    		$data->gateway_name = $payment_key;
    		$data->key = $key;
    		$data->value = $value;
    		$data->save();
    	}
    	return 1;
    }

    


    public function overWriteEnvFile($type, $val)
    {
            $path = base_path('.env');
            if (file_exists($path)) {
                $val = '"'.trim($val).'"';
                if(is_numeric(strpos(file_get_contents($path), $type)) && strpos(file_get_contents($path), $type) >= 0){
                    file_put_contents($path, str_replace(
                        $type.'="'.env($type).'"', $type.'='.$val, file_get_contents($path)
                    ));
                }
                else{
                    file_put_contents($path, file_get_contents($path)."\r\n".$type.'='.$val);
                }
            }
        return 1;
    }
}
