<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use DateTime;
use DateTimeZone;
use App\Models\Setting;
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getsitedate(){
            $setting=Setting::find(1);
            $date_zone=array();
            $timezone=$this->generate_timezone_list();
                foreach($timezone as $key=>$value){
                      if($setting->timezone==$key){
                              $date_zone=$value;
                      }
                }
            date_default_timezone_set($date_zone);   
            return date('Y-m-d');                    
     }
      static public function generate_timezone_list(){
          static $regions = array(
                     DateTimeZone::AFRICA,
                     DateTimeZone::AMERICA,
                     DateTimeZone::ANTARCTICA,
                     DateTimeZone::ASIA,
                     DateTimeZone::ATLANTIC,
                     DateTimeZone::AUSTRALIA,
                     DateTimeZone::EUROPE,
                     DateTimeZone::INDIAN,
                     DateTimeZone::PACIFIC,
                 );
                  $timezones = array();
                  foreach($regions as $region) {
                            $timezones = array_merge($timezones, DateTimeZone::listIdentifiers($region));
                  }

                  $timezone_offsets = array();
                  foreach($timezones as $timezone) {
                       $tz = new DateTimeZone($timezone);
                       $timezone_offsets[$timezone] = $tz->getOffset(new DateTime);
                  }
                 asort($timezone_offsets);
                 $timezone_list = array();
    
                 foreach($timezone_offsets as $timezone=>$offset){
                          $offset_prefix = $offset < 0 ? '-' : '+';
                          $offset_formatted = gmdate('H:i', abs($offset));
                          $pretty_offset = "UTC{$offset_prefix}{$offset_formatted}";
                          $timezone_list[] = "$timezone";
                 }

                 return $timezone_list;
                ob_end_flush();
       }

       public function gettimezonename($timezone_id){
              $getall=$this->generate_timezone_list();
              foreach ($getall as $k=>$val) {
                 if($k==$timezone_id){
                     return $val;
                 }
              }
       }
       
         public function getsitedatetime(){
            $setting=Setting::find(1);
            $date_zone=array();
            $timezone=$this->generate_timezone_list();
                foreach($timezone as $key=>$value){
                      if($setting->timezone==$key){
                              $date_zone=$value;
                      }
                }
            date_default_timezone_set($date_zone);   
            return date('Y-m-d H:i:s');                    
     }
public function getsitecurrenttime(){
            $setting=Setting::find(1);
            $date_zone=array();
            $timezone=$this->generate_timezone_list();
                foreach($timezone as $key=>$value){
                      if($setting->timezone==$key){
                              $date_zone=$value;
                      }
                }
            date_default_timezone_set($date_zone);   
            return date('H:i');                    
     }
     
        public function getsitedateall(){
            $setting=Setting::find(1);
            $date_zone=array();
            $timezone=$this->generate_timezone_list();
                foreach($timezone as $key=>$value){
                      if($setting->timezone==$key){
                              $date_zone=$value;
                      }
                }
            date_default_timezone_set($date_zone);   
            return date('Y-m-d h:i:s');                    
     }
     
     public function getsitecurrenttimenew(){
          $setting=Setting::find(1);
            $date_zone=array();
            $timezone=$this->generate_timezone_list();
                foreach($timezone as $key=>$value){
                      if($setting->timezone==$key){
                              $date_zone=$value;
                      }
                }
            date_default_timezone_set($date_zone);   
            return date('H:i A');   
     }
       
    public function create_session(){
        $application_id = 7223;
        $auth_key = "9dEWjERBVa5umN6";
        $authSecret = "T5Kx3CYsGKyMjLC";
        $nonce = rand();
        $timestamp = time();
        $stringForSignature = "application_id=".$application_id."&auth_key=".$auth_key."&nonce=".$nonce."&timestamp=".$timestamp;
        $signature = hash_hmac( 'sha1', $stringForSignature , $authSecret);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.connectycube.com/session');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{"application_id": "'.$application_id.'", "auth_key": "'.$auth_key.'", "nonce": "'.$nonce.'", "timestamp": "'.$timestamp.'",  "signature": "'.$signature.'"}');

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        $response = json_decode($result,true);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        // echo "<pre>";
        // print_r($response);
        // exit();
        return $response['session']['token'];
    }
   
   public function retrieveall(){
        $token = $this->create_session();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.connectycube.com/users');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, "per_page=1000");
        
        $headers = array();
        $headers[] = 'Cb-Token: '.$token;
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $response = json_decode($result,true);
        return $response;
   }
   
   public function signupconnectycude($name,$password,$email,$phone,$name_login){
        $token = $this->create_session();
        $ch = curl_init();
       // $name_login = $phone.rand()."#".$type;//trim($name).rand();
        curl_setopt($ch, CURLOPT_URL, 'https://api.connectycube.com/users');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{"user": {"login": "'.$name_login.'", "password": "'.$password.'", "email": "'.$email.'", "facebook_id": "", "twitter_id": "", "full_name": "'.$name.'", "phone": "'.$phone.'"}}');
        
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Cb-Token: '.$token;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $result = curl_exec($ch);
        $response = json_decode($result,true);
        // echo "<pre>";
        // print_r($response);
        // exit();
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        if(isset($response['user'])){
            return $response['user']['id'];
        }else if(!empty($response['errors']['password'][0])){
            return  '0-'.$response['errors']['password'][0];
        }else if(!empty($response['errors']['phone'][0])){
            return  '0-'.$response['errors']['phone'][0];
        }else{
           return  '0-'.$response['errors']['base'][0];
        }
       
   }
}
