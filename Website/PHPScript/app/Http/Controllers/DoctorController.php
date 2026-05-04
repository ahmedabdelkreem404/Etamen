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
use App\Models\Review;
use App\Models\Setting;
use App\Models\Patient;
use App\Models\BookAppointment;
use App\Models\Doctor_Hoilday;
use App\Models\Complement_settlement;
use DataTables;
use App\Models\TokenData;
use App\Models\User;
use App\Models\PaymentGatewayDetail;
use Mail;
class DoctorController extends Controller
{
    
     public function showdoctors(){
        return view("admin.doctor.default");
     }

     public function show_post_my_hoilday(Request $request){
        $store = new Doctor_Hoilday();
        $store->start_date = $request->get("start_date");
        $store->end_date = $request->get("end_date");
        $store->description = $request->get("description");
        $store->doctor_id = Session::get("user_id");
        $store->save();
        Session::flash('message',__("message.My Hoilday Add Successfully")); 
        Session::flash('alert-class', 'alert-success');
        return redirect()->back();
     }
     
     public function deletedoctorhoilday($id){
         Doctor_Hoilday::where("id",$id)->delete();
          Session::flash('message',__("message.Hoilday Delete Successfully")); 
        Session::flash('alert-class', 'alert-success');
        return redirect()->back();
     }

     public function show_complete_doctor_appointment(Request $request)
     {
        $getapp=BookAppointment::with('doctorls','patientls')->find($request->get("id"));
                 if($getapp){
                    $getapp->status=4;
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
                    $msg=__('order.complete1').' '.$getapp->doctorls->name." ".__('order.is completed');
                    $user=User::find(1);                            
                    $android=$this->send_notification_android($user->android_key,$msg,$getapp->user_id,"user_id");
                    $ios=$this->send_notification_IOS($user->ios_key,$msg,$getapp->user_id,"user_id");
                     try {
                            $user=Patient::find($getapp->user_id); 
                            $user->msg=$msg;                                     
                            $result=Mail::send('email.Ordermsg', ['user' => $user], function($message) use ($user){
                                $message->to($user->email,$user->name)->subject(__('message.System Name'));
                                         
                            });                                  
                    } catch (\Exception $e) {
                    }
                    Session::flash('message',$msg); 
                    Session::flash('alert-class', 'alert-success');
                    return redirect()->back();
                            
                }else{                        
                        Session::flash('message',__('message.Appointment Not Found')); 
                        Session::flash('alert-class', 'alert-danger');
                        return redirect()->back();
                } 
           
     }

     public function doctorstable(){
         $doctors =Doctors::get();
             
         return DataTables::of($doctors)
            ->editColumn('id', function ($doctors) {
                return $doctors->id;
            })
            ->editColumn('image', function ($doctors) {
                if($doctors->image!=""){
                    return asset("public/upload/doctors").'/'.$doctors->image;
                }else{
                    return asset("public/upload/doctors/doctor_default.png");
                }
                
            })
            ->editColumn('name', function ($doctors) {
                return $doctors->name;
            })  
            ->editColumn('email', function ($doctors) {
                return $doctors->email;
            })  
            ->editColumn('phone', function ($doctors) {
                return $doctors->phoneno;
            })  
            ->editColumn('service', function ($doctors) {

                return isset($doctors->departmentls)?$doctors->departmentls->name:"";
            })  
            ->editColumn('action', function ($doctors) {
                 $edit= url('admin/savedoctor',array('id'=>$doctors->id));
                 $timeing= url('admin/doctortiming',array('id'=>$doctors->id));
                 $delete= url('admin/deletedoctor',array('id'=>$doctors->id));
                 $doctorapprove=url('admin/approvedoctor',array('id'=>$doctors->id,"approve"=>'1'));
                 $txt="";
                  $setting=Setting::find(1);   
                 if($setting->doctor_approved=='1'){
                     if($doctors->is_approve=='0'){
                        $txt='<a  rel="tooltip" title="" href="'.$doctorapprove.'" class="m-b-10 m-l-5" data-original-title="Remove"><i class="fa fa-ban f-s-25" style="margin-left: 10px;color:red"></i></a>';
                     }else{
                        $txt='<i class="fa fa-ban f-s-25" style="margin-left: 10px;color:green"></i>';
                     }
                 }
                 else if($setting->doctor_approved=='0'&&$setting->is_demo=='1'){
                    if($doctors->is_approve=='0'){
                        $txt='<a  rel="tooltip" title="" href="'.$doctorapprove.'" class="m-b-10 m-l-5" data-original-title="Remove"><i class="fa fa-ban f-s-25" style="margin-left: 10px;color:red"></i></a>';
                     }else{
                        $txt='<i class="fa fa-ban f-s-25" style="margin-left: 10px;color:green"></i>';
                     }
                 }else{

                 }
                 $return = '<a  rel="tooltip" title="" href="'.$edit.'" class="m-b-10 m-l-5" data-original-title="Remove"><i class="fa fa-edit f-s-25" style="margin-right: 10px;"></i></a><a  rel="tooltip" title="" href="'.$timeing.'" class="m-b-10 m-l-5" data-original-title="Remove"><i class="fa fa-clock f-s-25" style="margin-right: 10px;"></i></a><a onclick="delete_record(' . "'" . $delete . "'" . ')" rel="tooltip" title="" class="m-b-10 m-l-5" data-original-title="Remove"><i class="fa fa-trash f-s-25"></i></a>'.$txt;
                 return $return;
            })
            ->make(true);
     }

     public function postapprovedoctor($id,$status){
         if(Session::get("is_demo")=='0'){
                Session::flash('message',"This Action Disable In Demo"); 
                Session::flash('alert-class', 'alert-danger');
                return redirect()->back();
         }else{
                $store=Doctors::find($id);
                $store->is_approve=$status;
                $store->save();
                if($status=='1'){
                    $msg=__('message.Profile Enable Successfully');
                }else{
                    $msg=__('message.Profile Disable Successfully');
                }
                Session::flash('message',$msg); 
                Session::flash('alert-class', 'alert-success');
                return redirect()->back();
         }
     }

     public function savedoctor($id){
        $data=Doctors::find($id);
        $department=Services::all();
        return view("admin.doctor.savedoctor")->with("id",$id)->with("data",$data)->with("department",$department);
     }

     public function updatedoctor(Request $request){
          if($request->get("id")==0){
            $store=new Doctors();
            $data=Doctors::where("email",$request->get("email"))->first();
            if($data){
                 Session::flash('message',__("message.Email Already Existe")); 
                 Session::flash('alert-class', 'alert-danger');
                 return redirect()->back();
            }
            $msg=__("message.Doctor Add Successfully");
            $img_url="profile.png";
            $rel_url="";
          }else{
            $store=Doctors::find($request->get("id"));
            $msg=__("message.Doctor Update Successfully");
            $img_url=$store->image;
            $rel_url=$store->image;
          }
            if ($request->hasFile('upload_image')) 
              {
                 $file = $request->file('upload_image');
                 $filename = $file->getClientOriginalName();
                 $extension = $file->getClientOriginalExtension() ?: 'png';
                 $folderName = '/upload/doctors/';
                 $picture = time() . '.' . $extension;
                 $destinationPath = public_path() . $folderName;
                 $request->file('upload_image')->move($destinationPath, $picture);
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
          $store->consultation_fees = $request->get("consultation_fees");
          $store->email=$request->get("email");
          $store->working_time=$request->get("working_time");
          $store->image=$img_url;
          $store->is_approve='1';
          $user_id = "";
          $login_field = "";
           if($request->get("id")==0&&env('ConnectyCube')==true){
                                  $login_field = $request->get("phoneno").rand()."#1";
                                  $user_id = $this->signupconnectycude($request->get("name"),$request->get("password"),$request->get("email"),$request->get("phoneno"),$login_field);
             }
             $store->connectycube_user_id = $user_id;
          $store->login_id = $login_field;
          $store->save();
          
          Session::flash('message',$msg); 
          Session::flash('alert-class', 'alert-success');
          return redirect("admin/doctors");
     }

     public function doctortiming($id){
        $datats=Schedule::with('getslotls')->where("doctor_id",$id)->get();
        foreach ($datats as $k) {
           $k->options=$this->getdurationoption($k->start_time,$k->end_time,$k->duration);
        }
     
        return view("admin.doctor.schedule")->with("id",$id)->with("data",$datats);
     }



     public function findpossibletime(Request $request){
        $type="<option value=''>".__("message.Select Duration")."</option>";
        $start_time=$request->get("start_time");
        $end_time=$request->get("end_time");
        $duration=$request->get("duration");
        $datetime1 = strtotime($start_time);
        $datetime2 = strtotime($end_time);
        $interval  = abs($datetime2 - $datetime1);
        $minutes   = round($interval / 60);
        if($minutes%15==0){ // 15 mintue
            if($duration==15){
              $type=$type."<option value='15' selected='selected'>".__("message.15 Minutes")."</option>";
            }else{
              $type=$type."<option value='15'>".__("message.15 Minutes")."</option>";
            }
        }
        if($minutes%30==0){ //30 mintue
            if($duration==30){
                $type=$type."<option value='30' selected='selected'>".__("message.30 Minutes")."</option>";
            }else{
                $type=$type."<option value='30'>".__("message.30 Minutes")."</option>";
            }
        }
        if($minutes%45==0){ // 45 mintue
            if($duration==45){
                $type=$type."<option value='45' selected='selected'>".__("message.45 Minutes")."</option>";
            }else{
                $type=$type."<option value='45'>".__("message.45 Minutes")."</option>";
            }
        }
        if($minutes%60==0){ //60 mintue
            if($duration==60){
                $type=$type."<option value='60' selected='selected'>".__("message.1 Hour")."</option>";
            }else{
                $type=$type."<option value='60'>".__("message.1 Hour")."</option>"; 
            }
        }
        return $type;
     }

     public function getdurationoption($start_time,$end_time,$duration){
        $type="<option value=''>Select Duration</option>";       
        $datetime1 = strtotime($start_time);
        $datetime2 = strtotime($end_time);
        $interval  = abs($datetime2 - $datetime1);
        $minutes   = round($interval / 60);
        if($minutes%15==0){ // 15 mintue
            if($duration==15){
              $type=$type."<option value='15' selected='selected'>".__("message.15 Minutes")."</option>";
            }else{
              $type=$type."<option value='15'>".__("message.15 Minutes")."</option>";
            }
        }
        if($minutes%30==0){ //30 mintue
            if($duration==30){
                $type=$type."<option value='30' selected='selected'>".__("message.30 Minutes")."</option>";
            }else{
                $type=$type."<option value='30'>".__("message.30 Minutes")."</option>";
            }
        }
        if($minutes%45==0){ // 45 mintue
            if($duration==45){
                $type=$type."<option value='45' selected='selected'>".__("message.45 Minutes")."</option>";
            }else{
                $type=$type."<option value='45'>".__("message.45 Minutes")."</option>";
            }
        }
        if($minutes%60==0){ //60 mintue
            if($duration==60){
                $type=$type."<option value='60' selected='selected'>".__("message.1 Hour")."</option>";
            }else{
                $type=$type."<option value='60'>".__("message.1 Hour")."</option>"; 
            }
        }
        return $type;
     }
     public function generateslotfront(Request $request){
        $start_time=$request->get("start_time");
         $end_time=$request->get("end_time");
         $duration=$request->get("duration");
         $datetime1 = strtotime($start_time);
         $datetime2 = strtotime($end_time);
         $interval  = abs($datetime2 - $datetime1);
         $minutes   = round($interval / 60);         
         $noofslot=$minutes /$duration;
         $slot=array();
         if($noofslot>0){
            for ($i=0; $i <$noofslot; $i++) { 
                $a=$duration*$i;
                $slot[]=date("h :i A",strtotime("+".$a." minutes", strtotime($start_time)));
            }
         }
         $txt="";
         for($i=0;$i<count($slot);$i++){
            if(isset($slot[$i])){
                 $txt=$txt.'<li><label>'.$slot[$i].'</label></li>';
            }          
         }
         return $txt;
     }
     public function generateslot(Request $request){
         $start_time=$request->get("start_time");
         $end_time=$request->get("end_time");
         $duration=$request->get("duration");
         $datetime1 = strtotime($start_time);
         $datetime2 = strtotime($end_time);
         $interval  = abs($datetime2 - $datetime1);
         $minutes   = round($interval / 60);         
         $noofslot=$minutes /$duration;
         $slot=array();
         if($noofslot>0){
            for ($i=0; $i <$noofslot; $i++) { 
                $a=$duration*$i;
                $slot[]=date("h :i A",strtotime("+".$a." minutes", strtotime($start_time)));
            }
         }
         $txt="";
         for($i=0;$i<count($slot);$i++){
             $txt=$txt.'<div class="col-md-12 md25">';
            if(isset($slot[$i])){
                 $txt=$txt.'<span class="slotshow">'.$slot[$i].'</span>';
                 $i++;
            }
            if(isset($slot[$i])){
                 $txt=$txt.'<span class="slotshow">'.$slot[$i].'</span>';
                 $i++;
            }           
            if(isset($slot[$i])){
                 $txt=$txt.'<span class="slotshow">'.$slot[$i].'</span>';
                 $i++;
            }
             if(isset($slot[$i])){
                 $txt=$txt.'<span class="slotshow">'.$slot[$i].'</span>';
                 $i++;
            }
            if(isset($slot[$i])){
                 $txt=$txt.'<span class="slotshow">'.$slot[$i].'</span>';
            }
            $txt=$txt."</div>";
          
         }
         return $txt;
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

     public function savescheduledata(Request $request){
     
        $arr=$request->get("arr");
        if(!empty($arr)){
           $removedata=Schedule::where("doctor_id",$request->get("doc_id"))->get();
           if(count($removedata)>0){
              foreach ($removedata as $k) {
                  $findslot=SlotTiming::where("schedule_id",$k->id)->delete();
                  $k->delete();
              }
           }

           for($i=0;$i<count($arr);$i++){
               if($arr[$i]['start_time']){
                   for($j=0;$j<count($arr[$i]['start_time']);$j++){
                      if(isset($arr[$i]['start_time'][$j])&&$arr[$i]['start_time'][$j]!=""&&isset($arr[$i]['end_time'][$j])&&$arr[$i]['end_time'][$j]!=""&&isset($arr[$i]['duration'][$j])&&$arr[$i]['duration'][$j]!=""){
                            $getslot=$this->getslotvalue($arr[$i]['start_time'][$j],$arr[$i]['end_time'][$j],$arr[$i]['duration'][$j]);
                            $store=new Schedule();
                            $store->doctor_id=$request->get("doc_id");
                            $store->day_id=$i;
                            $store->start_time=$arr[$i]['start_time'][$j];
                            $store->end_time=$arr[$i]['end_time'][$j];
                            $store->duration=$arr[$i]['duration'][$j];
                            $store->save();
                            foreach ($getslot as $g) {
                                $aslot=new SlotTiming();
                                $aslot->schedule_id=$store->id;
                                $aslot->slot=$g;
                                $aslot->save();
                            }
                      }
                   }
               }              
           }

          Session::flash('message',__("message.Schedule Save Successfully")); 
          Session::flash('alert-class', 'alert-success');
          return redirect("admin/doctors");
        }
        return redirect("admin/doctors");
     }
    
     public function deletedoctor($id){
        $doctor=Doctors::find($id);
        if($doctor){
            $deletsolt=Schedule::with('getslotls')->where("doctor_id",$id)->get();
            foreach ($deletsolt as $de) {
                foreach ($de->getslotls as $k) {
                   $k->delete();
                }
                $de->delete();
            }
            $bookappointment=BookAppointment::where("doctor_id",$id)->delete();
            $review=Review::where("doc_id",$id)->delete();
            $image_path = public_path() ."/upload/doctors/".$doctor->image;
                if(file_exists($image_path)&&$doctor->image!="") {
                    try {
                            unlink($image_path);
                        }
                    catch(Exception $e) {
                    }                        
                }
            $doctor->delete();
        }
        Session::flash('message',__("message.Doctor Delete Successfully")); 
        Session::flash('alert-class', 'alert-success');
        return redirect()->back();
     }
    
     public function showreviews(){
            return view("admin.doctor.review");
     }

     public function reviewtable(){
          $review =Review::all();

        return DataTables::of($review)
            ->editColumn('id', function ($review) {
                return $review->id;
            })
            ->editColumn('doctor_name', function ($review) {
                $data=Doctors::find($review->doc_id);
                return isset($data)?$data->name:"";
            })
            ->editColumn('username', function ($review) {
                $data=Patient::find($review->user_id);
                return isset($data)?$data->name:"";
            }) 
             ->editColumn('ratting', function ($review) {
                return isset($review->rating)?$review->rating:"";
            })
             ->editColumn('comment', function ($review) {
                return isset($review->description)?$review->description:"";
            })            
            ->editColumn('action', function ($review) {   
                $delete= url('admin/deletereview',array('id'=>$review->id));
                $return = '<a onclick="delete_record(' . "'" . $delete . "'" . ')" rel="tooltip" title="" class="m-b-10 m-l-5" data-original-title="Remove"><i class="fa fa-trash f-s-25"></i></a>'; 
                return $return;            
            })
           
            ->make(true);
     }

     public function deletereview($id){
        $store=Review::find($id);
        $store->delete();
        Session::flash('message',__("message.Review Delete Successfully")); 
        Session::flash('alert-class', 'alert-success');
        return redirect()->back();
     }

     public function doctordashboard(Request $request){
         if(Session::get("user_id")!=""&&Session::get("role_id")=='2'){
            $setting=Setting::find(1);
            $totalappointment=count(BookAppointment::where("doctor_id",Session::get("user_id"))->get());
            $totalreview=count(Review::where("doc_id",Session::get("user_id"))->get());
            $totalnewappointment=count(BookAppointment::where("doctor_id",Session::get("user_id"))->where("notify",'1')->get());
            $getnewapp=BookAppointment::where("doctor_id",Session::get("user_id"))->where("notify",'1')->get();
            foreach ($getnewapp as $k) {
                $k->notify='0';
                $k->save();
            }
            $type=$request->get("type");
             if($type==2){ //past                
                $bookdata=BookAppointment::with("patientls")->where("doctor_id",Session::get("user_id"))->where("date","<",date('Y-m-d'))->paginate(10);
              }elseif($type==3){ //upcoming
                  $bookdata=BookAppointment::with("patientls")->where("doctor_id",Session::get("user_id"))->where("date",">",date('Y-m-d'))->paginate(10);
              }else{ //today
                  $bookdata=BookAppointment::with("patientls")->where("doctor_id",Session::get("user_id"))->where("date",date('Y-m-d'))->paginate(10);
              }
            $doctordata=Doctors::with('departmentls')->find(Session::get("user_id"));
            return view("user.doctor.dashboard")->with("setting",$setting)->with("doctordata",$doctordata)->with("totalappointment",$totalappointment)->with("totalreview",$totalreview)->with("totalnewappointment",$totalnewappointment)->with("type",$type)->with("bookdata",$bookdata);
         }else{
            return redirect("/");
         }         
     }

     public function postdoctorregister(Request $request){
        //dd($request->all());
        $getuser=Doctors::where("email",$request->get("email"))->first();
        if($getuser){
            Session::flash('message',__("message.Email Already Existe")); 
            Session::flash('alert-class', 'alert-danger');
            return redirect()->back();
        }else{
            $store=new Doctors();
            $store->name=$request->get("name");
            $store->email=$request->get("email");
            $store->password=$request->get("password");
            $store->phoneno=$request->get("phone");
            
            if(env('ConnectyCube')==true){
                  $login_field = $request->get("phone").rand()."#2";
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
                Session::put("role_id",'2');
                Session::flash('message',__("Successful Register")); 
                Session::flash('alert-class', 'alert-success');
                return redirect("doctordashboard");
            }
        }
     }

    public function postlogindoctor(Request $request){
        $getUser=Doctors::where("email",$request->get("email"))->where("password",$request->get("password"))->first();
        $setting=Setting::find(1);       
        if($getUser){
                if($setting->doctor_approved=='1'&&$getUser->is_approve=='1'){
                    if($request->get("rem_me")==1){
                        setcookie('email', $request->get("email"), time() + (86400 * 30), "/");
                        setcookie('password',$request->get("password"), time() + (86400 * 30), "/");
                        setcookie('rem_me',1, time() + (86400 * 30), "/");
                    } 
                    Session::put("user_id",$getUser->id);                
                    Session::put("role_id",'2');
                    return redirect("doctordashboard");
                }elseif($setting->doctor_approved=='0'&&$getUser->is_approve=='1'){
                    if($request->get("rem_me")==1){
                        setcookie('email', $request->get("email"), time() + (86400 * 30), "/");
                        setcookie('password',$request->get("password"), time() + (86400 * 30), "/");
                        setcookie('rem_me',1, time() + (86400 * 30), "/");
                    } 
                    Session::put("user_id",$getUser->id);                
                    Session::put("role_id",'2');
                    return redirect("doctordashboard");
                }elseif($setting->doctor_approved=='1'&&$setting->is_demo=='1'){
                    if($request->get("rem_me")==1){
                        setcookie('email', $request->get("email"), time() + (86400 * 30), "/");
                        setcookie('password',$request->get("password"), time() + (86400 * 30), "/");
                        setcookie('rem_me',1, time() + (86400 * 30), "/");
                    } 
                    Session::put("user_id",$getUser->id);                
                    Session::put("role_id",'2');
                    return redirect("doctordashboard");
                }else{
                    Session::flash('message',"Your profile is in under process please wait for some time"); 
                    Session::flash('alert-class', 'alert-danger');
                    return redirect("doctorlogin");
                }
               
        }else{
            Session::flash('message',__("message.Login Credentials Are Wrong")); 
            Session::flash('alert-class', 'alert-danger');
            return redirect("doctorlogin");
        }

    } 

    public function doctorchangepassword(){
         if(Session::get("user_id")!=""&&Session::get("role_id")=='2'){
            $setting=Setting::find(1);          
            $doctordata=Doctors::with('departmentls')->find(Session::get("user_id"));
            return view("user.doctor.changepassword")->with("setting",$setting)->with("doctordata",$doctordata);
         }else{
            return redirect("/");
         }         
    }

    public function show_doctor_hoilday(){
         if(Session::get("user_id")!=""&&Session::get("role_id")=='2'){
            $setting=Setting::find(1);          
            $doctorhoilday=Doctor_Hoilday::where("doctor_id",Session::get("user_id"))->get();
            $doctordata=Doctors::with('departmentls')->find(Session::get("user_id"));
            return view("user.doctor.doctor_hoilday")->with("setting",$setting)->with("doctordata",$doctordata)->with("doctorhoilday",$doctorhoilday);
         }else{
            return redirect("/");
         } 
    }

    public function checkdoctorpwd(Request $request){
        $data=Doctors::find(Session::get("user_id"));
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

    public function updatedoctorpassword(Request $request){
          $data=Doctors::find(Session::get("user_id"));
          $data->password=$request->get("npwd");
          $data->save();
          Session::flash('message',"Password Change Successfully"); 
          Session::flash('alert-class', 'alert-success');
          return redirect()->back();
    }

    public function doctoreditprofile(){
         if(Session::get("user_id")!=""&&Session::get("role_id")=='2'){
            $setting=Setting::find(1);    
            $department=Services::all();      
            $doctordata=Doctors::with('departmentls')->find(Session::get("user_id"));
            return view("user.doctor.editprofile")->with("setting",$setting)->with("doctordata",$doctordata)->with("department",$department);
         }else{
            return redirect("/");
         }  
    }

    public function updatedoctorsideprofile(Request $request){
        //dd($request->all());
          $doctoremail=Doctors::where("email",$request->get("email"))->where("id","!=",Session::get("user_id"))->first();
          if($doctoremail){
                 Session::flash('message',__("message.Email Already Existe")); 
                 Session::flash('alert-class', 'alert-danger');
                 return redirect()->back();
          }else{
            $store=Doctors::find(Session::get("user_id"));
            $msg=__("message.Doctor Update Successfully");
            $img_url=$store->image;
            $rel_url=$store->image;
          }
            if ($request->hasFile('upload_image')) 
              {
                 $file = $request->file('upload_image');
                 $filename = $file->getClientOriginalName();
                 $extension = $file->getClientOriginalExtension() ?: 'png';
                 $folderName = '/upload/doctors/';
                 $picture = time() . '.' . $extension;
                 $destinationPath = public_path() . $folderName;
                 $request->file('upload_image')->move($destinationPath, $picture);
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
          Session::flash('message',$msg); 
          Session::flash('alert-class', 'alert-success');
          return redirect()->back();
    }

    public function doctorreview(){        
        if(Session::get("user_id")!=""&&Session::get("role_id")=='2'){
            $setting=Setting::find(1);    
            $doctordata=Doctors::find(Session::get("user_id"));      
            $reviewdata=Review::with('patientls')->where("doc_id",Session::get("user_id"))->get();
            return view("user.doctor.review")->with("setting",$setting)->with("doctordata",$doctordata)->with("reviewdata",$reviewdata);
        }else{
            return redirect("/");
        } 
    }

    public function doctorappointment(Request $request){
         if(Session::get("user_id")!=""&&Session::get("role_id")=='2'){
            $setting=Setting::find(1);    
            $doctordata=Doctors::find(Session::get("user_id"));      
            $appointmentdata=BookAppointment::with('patientls')->where("doctor_id",Session::get("user_id"))->orderby("id","DESC")->paginate(15);
            return view("user.doctor.appointmentlist")->with("setting",$setting)->with("doctordata",$doctordata)->with("appointmentdata",$appointmentdata);
        }else{
            return redirect("/");
        } 
    }


    public function doctortimingfront(Request $request){
        if(Session::get("user_id")!=""&&Session::get("role_id")=='2'){
            $setting=Setting::find(1);    
            $doctordata=Doctors::find(Session::get("user_id")); 
            $datats=Schedule::with('getslotls')->where("doctor_id",Session::get("user_id"))->get();
            foreach ($datats as $k) {
               $k->options=$this->getdurationoption($k->start_time,$k->end_time,$k->duration);
            }
            return view("user.doctor.doctortiming")->with("setting",$setting)->with("doctordata",$doctordata)->with("data",$datats);
        }else{
            return redirect("/");
        } 
    }
    
    public function paymenthistory(){
         if(Session::get("user_id")!=""&&Session::get("role_id")=='2'){
            $setting=Setting::find(1);    
            $data = Complement_settlement::where("doctor_id",Session::get("user_id"))->get();
$doctordata=Doctors::with('departmentls')->find(Session::get("user_id"));
            return view("user.doctor.paymenthistory")->with("setting",$setting)->with("data",$data)->with("doctordata",$doctordata);
        }else{
            return redirect("/");
        } 
    }

     public function updatedoctortiming(Request $request){
      // dd($request->all());
       
       $arr=$request->get("arr");
        if(!empty($arr)){
           $removedata=Schedule::where("doctor_id",$request->get("doctor_id"))->get();
           if(count($removedata)>0){
              foreach ($removedata as $k) {
                  $findslot=SlotTiming::where("schedule_id",$k->id)->delete();
                  $k->delete();
              }
           }

           for($i=0;$i<count($arr);$i++){
               if($arr[$i]['start_time']){
                    $start_date = array_values($arr[$i]['start_time']);
                    $end_date = array_values($arr[$i]['end_time']);
                    $duration= array_values($arr[$i]['duration']);

                   for($j=0;$j<count($start_date);$j++){
                      if(isset($start_date[$j])&&$start_date[$j]!=""&&isset($end_date[$j])&&$end_date[$j]!=""&&isset($duration[$j])&&$duration[$j]!=""){
                            $getslot=$this->getslotvalue($start_date[$j],$end_date[$j],$duration[$j]);
                            
                            $store=new Schedule();
                            $store->doctor_id=$request->get("doctor_id");
                            $store->day_id=$i;
                            $store->start_time=$start_date[$j];
                            $store->end_time=$end_date[$j];
                            $store->duration=$duration[$j];
                            $store->save();
                            foreach ($getslot as $g) {
                                $aslot=new SlotTiming();
                                $aslot->schedule_id=$store->id;
                                $aslot->slot=$g;
                                $aslot->save();
                            }
                      }
                   }
               }              
           }
           

          Session::flash('message',__("message.Schedule Save Successfully")); 
          Session::flash('alert-class', 'alert-success');
          return redirect()->back();
        }
        return redirect()->back();
    }


    public function changeappointmentdoctor($status,$id){
        $getapp=BookAppointment::with('doctorls','patientls')->find($id);
                 if($getapp){
                            $getapp->status=$status;
                            $getapp->save();
                            if($status=='3'){ // in process
                                $msg=__('order.inprocess1').' '.$getapp->doctorls->name.' '.__('order.inprocess2').' '.$getapp->date.' '.$getapp->slot_name;
                            }
                            else if($status=='5'){ //reject
                                $msg=__('order.rejectorder').' '.$getapp->doctorls->name;
                                 Settlement::where("book_id",$id)->delete();
                            }else if($status=='4'){//complete
                                
                                $msg=__('order.complete1').' '.$getapp->doctorls->name." ".' '.__('order.is completed');
                            }else if($status=='0'){//absent
                                $msg="absent";
                            }else{
                                $msg="";
                            }
                            $user=User::find(1);                            
                            $android=$this->send_notification_android($user->android_key,$msg,$getapp->user_id,"user_id");
                            $ios=$this->send_notification_IOS($user->ios_key,$msg,$getapp->user_id,"user_id");                           
                             try {
                                      $user=Patient::find($getapp->user_id); 
                                      $user->msg=$msg;
                                     // $user->email="redixbit.jalpa@gmail.com";
                                      $result=Mail::send('email.Ordermsg', ['user' => $user], function($message) use ($user){
                                         $message->to($user->email,$user->name)->subject(__('message.System Name'));
                                         
                                      });
                                  
                              } catch (\Exception $e) {
                              }
                              Session::flash('message',$msg); 
                              Session::flash('alert-class', 'alert-success');
                             return redirect()->back();
                            
                 }else{                        
                        Session::flash('message',__('message.Appointment Not Found')); 
                        Session::flash('alert-class', 'alert-danger');
                        return redirect()->back();
                 } 
    }

    public function send_notification_android($key,$msg,$id,$field){
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
                            'title' =>  __('message.notification')
                        );
                        //echo "<pre>";print_r($message1);exit;
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
     public function send_notification_IOS($key,$msg,$id,$field){
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
