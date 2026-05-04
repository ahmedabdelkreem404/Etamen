<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Session;
use validate;
use Sentinel;
use DB;
use App\Models\Notification;
use App\Models\TokenData;
use DataTables;
class NotificationController extends Controller
{

    public function showsendnotification(){
        return view("admin.notification");
    }

    public function notificationkey(){
        $user=Sentinel::getUser();
        return view("admin.notificationkey")->with("user",$user);
    }

    public function updatenotificationkey(Request $request){
        $user=Sentinel::getUser();
        $user->android_key=$request->get("android_key");
        $user->ios_key=$request->get("ios_key");
        $user->save();
        Session::flash('message',__('message.Notification Key Update Successfully')); 
        Session::flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    public function notificationtable(Request $request){
        $notify =Notification::all();

        return DataTables::of($notify)
            ->editColumn('id', function ($notify) {
                return $notify->id;
            })
            ->editColumn('message', function ($notify) {
                return $notify->message;
            })
            ->make(true);
    }


    public function savenotification(){
         return view("admin.addnotification");
    }

    public function sendnotificationtouser(Request $request){
        if($request->get("message")==""){
             Session::flash('message',__('message.Message Is Required')); 
             Session::flash('alert-class', 'alert-danger');
             return redirect()->back();
        }
        $user=Sentinel::getUser();
        $msg=$request->get("message");
        $android=$this->send_notification_android($user->android_key,$msg);
        $ios=$this->send_notification_IOS($user->ios_key,$msg);
        if($android==1||$ios==1){
             $store=new Notification();
             $store->message=$request->get("message");
             $store->save();
             Session::flash('message',__('message.Notification Send Successfully')); 
             Session::flash('alert-class', 'alert-success');
             return redirect("admin/sendnotification");
        }
        else{
            Session::flash('message',__('message.Notification Not Send Successfully')); 
            Session::flash('alert-class', 'alert-danger');
            return redirect()->back();
        }
    }

     public function send_notification_android($key,$msg){
      if (env('EXTERNAL_SERVICES', false) == false) { return 0; }
        $getuser=TokenData::where("type",1)->get();
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
                            'title' =>  __('message.notification')
                        );
                       $fields = array(
                          'registration_ids'  => $registrationIds,
                          'data'              => $message,
                          'notification'      =>$message1
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
                    //  echo "<pre>";print_r($result);exit;
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
    public function send_notification_IOS($key,$msg){
      if (env('EXTERNAL_SERVICES', false) == false) { return 0; }
      $getuser=TokenData::where("type",2)->get();
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
                            'title' =>  __('message.notification')
                        );
                       $fields = array(
                          'registration_ids'  => $registrationIds,
                          'data'              => $message,
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
