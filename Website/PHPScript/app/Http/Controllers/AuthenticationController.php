<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Session;
use validate;
use Sentinel;
use DB;
use DataTables;
use App\Models\User;
use App\Models\Services;
use App\Models\Review;
use App\Models\Patient;
use App\Models\Doctors;
use App\Models\Reportspam;
use App\Models\BookAppointment;
use App\Models\Resetpassword;

use App\Models\Setting;
use DateTimeZone;
use DateTime;
use Hash;
use Mail;
class AuthenticationController extends Controller
{
    
    public function showlogin(){
       $setting=Setting::find(1);
       Session::put("is_demo",$setting->is_demo);
       Session::put("favicon",asset('public/image_web').'/'.$setting->favicon);
       Session::put("logo",asset('public/image_web').'/'.$setting->logo);
       return view("admin.login");
    }
    
   

    public function postlogin(Request $request){
    	       $user=Sentinel::authenticate($request->all());
               if($user){
                      
		               	if($request->get("remember_me")==1){
		                    setcookie('email', $request->get("email"), time() + (86400 * 30), "/");
		                    setcookie('password',$request->get("password"), time() + (86400 * 30), "/");
		                    setcookie('remember_me',1, time() + (86400 * 30), "/");
		                } 
                   return  redirect("admin/dashboard");    
               } 
               else{
                   Session::flash('message', __("message.Login Credentials Are Wrong")); 
                   Session::flash('alert', 'danger');
                   return redirect()->back();
               } 
    }

    public  function checkcurrentpassword($pwd){
          $user=Sentinel::getUser();
           if (Hash::check($pwd, $user->password))
           {
              $data=1;
           }
          else{
              $data=0; 
           }
         return $data;
     }

     public function reset_password($code){
            $setting = Setting::find(1);
            $data=Resetpassword::where("code",$code)->first();
            if($data){
              return view('admin.reset_pwd')->with("id",$data->user_id)->with("code",$code)->with("type",$data->type)->with("setting",$setting);
            }else{
              return view('admin.reset_pwd')->with("msg",__('message.Code Expired'))->with("setting",$setting);
            }
      }

      public function reset_new_pwd(Request $request){
        
           $setting = Setting::find(1);
            if($request->get('id')==""){
                return view('admin.reset_pwd')->with("msg",__('message.pwd_reset'))->with("setting",$setting);
            }else{
                if($request->get("type")==1){
                     $user=Patient::find($request->get('id'));
                }else{
                    $user=Doctors::find($request->get('id'));
                }
                $user->password=$request->get('npwd');

                $user->save();

                $codedel=Resetpassword::where('user_id',$request->get("id"))->delete();
               return view('admin.reset_pwd')->with("msg",__('message.pwd_reset'))->with("setting",$setting);
            }
      }

    public function showdashboard(){
      $totaldoctor=count(Doctors::all());
      $totalappointment=count(BookAppointment::all());
      $totalreview=count(Review::all());
      $totalpatient=count(Patient::all());
    	return view("admin.dashboard")->with("totaldoctor",$totaldoctor)->with("totalappointment",$totalappointment)->with("totalreview",$totalreview)->with("totalpatient",$totalpatient);
    }

    public  function check_password_same($pwd){
    $user=Sentinel::getUser();
     if (Hash::check($pwd, $user->password))
     {
        $data=1;
     }
    else{
        $data=0; 
     }
   return $data;
   }

   public function updatepassword(Request $request){
     $user=Sentinel::getUser();
       if (Hash::check($request->get('currentpwd'), $user->password))
        {
            Sentinel::update($user, array('password' => $request->get('newpwd')));
            Session::flash('message',__("message.Password Update Successfully")); 
            Session::flash('alert-class', 'alert-success');
            return redirect()->back();
        }
       else{
          Session::flash('message',__("message.Current Password Is Incorrect")); 
          Session::flash('alert-class', 'alert-danger');
          return redirect()->back();
       }
       
   }

    public function logout(){
      $user=Sentinel::getUser();
     
        
          return redirect("admin");
       
    	
    }

    public function showsuser(){
      return view("admin.user.default");
    }

    public function showcontact(){
       return view("admin.contactus");
    }

    public function userstable(){
       $users =Patient::all();

        return DataTables::of($users)
            ->editColumn('id', function ($users) {
                return $users->id;
            })
            ->editColumn('name', function ($users) {
                return $users->name;
            })
            ->editColumn('email', function ($users) {
                return $users->email;
            }) 
             ->editColumn('phone', function ($users) {
                return $users->phone;
            })            
            ->editColumn('action', function ($users) {  
                $delete= url('admin/deleteuser',array('id'=>$users->id));
                $return = '<a onclick="delete_record(' . "'" . $delete . "'" . ')" rel="tooltip" title="" class="m-b-10 m-l-5" data-original-title="Remove"><i class="fa fa-trash f-s-25"></i></a>';
                return $return;               
            })
           
            ->make(true);
    }

    public function deleteuser($id){
        $data=Patient::find($id);
        if($data){
            $deleteAppointment=BookAppointment::where("user_id",$id)->delete();
            $deleteremove=Review::where("user_id",$id)->delete();
            $data->delete();
        }
        Session::flash('message',__("message.Patient Delete Successfully")); 
          Session::flash('alert-class', 'alert-success');
          return redirect()->back();
    }


    public function editprofile(){
        $userdata=Sentinel::getUser();
        return view("admin.profile")->with("userdata",$userdata);
    }

    public function changepassword(Request $request){
        return view("admin.changepassword");
    }

    public function shownewsletter(){
       return view("admin.newsletter");
    }

    public function updatenewsletter(Request $request){
        $msg=$request->get("description");
          $getall=Newsletter::all();
          foreach($getall as $g){
              $data=array();
              $data['email']=$g->email;
              $data['msg']=$msg;
              try {
                      $result=Mail::send('email.news', ['user' => $data], function($message) use ($data){
                         $message->to($data['email'],'customer')->subject("Freaktemplate");
                      });
            
              } catch (\Exception $e) {
              }
        
          }
       Session::flash('message',__("message.News Send Successfully"));
       Session::flash('alert-class', 'alert-success');
       return redirect()->back();
    }
   
       public function updateaccount(Request $request){
        $user=Sentinel::getUser();
        $user->first_name=$request->get("first_name");
        $user->last_name=$request->get("last_name");
        $user->save();
        $rel_url=$user->profile_pic;
        if ($request->hasFile('profile_pic')) 
           {
              $file = $request->file('profile_pic');
              $filename = $file->getClientOriginalName();
              $extension = $file->getClientOriginalExtension() ?: 'png';
              $folderName = '/upload/profile/';
              $picture = time() . '.' . $extension;
              $destinationPath = public_path() . $folderName;
              $request->file('profile_pic')->move($destinationPath, $picture);
              $user->profile_pic =$picture;  
              $user->save();                                
              $image_path = public_path() ."/upload/profile/".$rel_url;
              if(file_exists($image_path)&&$rel_url!="") {
                try {
                        unlink($image_path);
                    }
                 catch(Exception $e) {}                        
              }
            }
            Session::flash("successorder",__("message.Account Details Update Successfully"));
            return redirect()->back();
     }


     public function showcomplain(){
        $data=Reportspam::all();
        return view("admin.reportspam")->With("data",$data);
     }

     public function compaintable(){
         $compain =Reportspam::all();

        return DataTables::of($compain)
            ->editColumn('id', function ($compain) {
                return $compain->id;
            })
            ->editColumn('username', function ($compain) {
                $data=Patient::find($compain->user_id);
                return isset($data->name)?$data->name:"";
            })
            ->editColumn('title', function ($compain) {
                return $compain->title;
            })
            ->editColumn('description', function ($compain) {
                return $compain->description;
            })
            ->editColumn('action', function ($compain) {
              $data=Patient::find($compain->user_id);
               if($data){
                   return '<a  href="https://mail.google.com/mail/?view=cm&fs=1&to='.$data->email.'&su=Complain '.$compain->title.'&body='.$compain->description.'" rel="tooltip"  target="blank" class="m-b-10 m-l-5" data-original-title="Remove"><i class="fa fa-envelope f-s-25" style="margin-right: 10px;font-size: x-large;color:black"></i></a>';
               } 
               
            })
            ->make(true);
     }


     public function showsetting(){
        $data=Setting::find(1);
        $timezone=$this->generate_timezone_list();
        return view("admin.setting")->with("data",$data)->with("timezone",$timezone);
     }

     public function updatesettingone(Request $request){
         $store=Setting::find(1);
         $store->email=$request->get("email");
         $store->phone=$request->get("phone");
         $store->address=$request->get("address");
         $store->app_url=$request->get("app_url");
         //$store->commission = $request->get("commission");
         $store->playstore_url=$request->get("playstore_url");
        //$store->timezone = $request->get("timezone");
         //$store->currency = $request->get("currency");
         //$store->doctor_approved=$request->get("doctor_approved")?$request->get("doctor_approved"):"0";
         //$store->is_rtl=$request->get("is_rtl")?$request->get("is_rtl"):"0";
         $store->save();
         Session::flash('message',"Setting Update Successfully");
         Session::flash('alert-class', 'alert-success');
         return redirect()->back();
     }
     public function updatesettingfour(Request $request){
         $store=Setting::find(1);
         $store->commission = $request->get("commission");
         $store->timezone = $request->get("timezone");
         $store->currency = $request->get("currency");
         $store->doctor_approved=$request->get("doctor_approved")?$request->get("doctor_approved"):"0";
         $store->is_rtl=$request->get("is_rtl")?$request->get("is_rtl"):"0";
         $store->save();
         Session::flash('message',"Setting Update Successfully");
         Session::flash('alert-class', 'alert-success');
         return redirect()->back();
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
          $timezone_list[] = "({$pretty_offset}) $timezone";
        }
    
        return $timezone_list;
        ob_end_flush();
    }


     public function updatesettingtwo(Request $request){
          //dd($request->all());exit;
          $store=Setting::find(1);
          if($request->hasFile('main_banner')) 
            {
                 $logo_img=$store->main_banner;
                 $file = $request->file('main_banner');
                 $filename = $file->getClientOriginalName();
                 $extension = $file->getClientOriginalExtension() ?: 'png';
                 $folderName = '/image_web';
                 $picture = mt_rand(100000, 999999). '.' . $extension;
                 $destinationPath = public_path() . $folderName;
                 $request->file('main_banner')->move($destinationPath, $picture);
                 $store->main_banner =$picture;
                 $image_path=public_path().'/image_web/'.$logo_img;
                  if(file_exists($image_path)) {
                            try{
                                 unlink($image_path);
                            }
                            catch(\Exception $e)
                            {
                                
                            }
                            
                        }
            }
             if ($request->hasFile('favicon')) 
            {
                 $logo_img=$store->favicon;
                 $file = $request->file('favicon');
                 $filename = $file->getClientOriginalName();
                 $extension = $file->getClientOriginalExtension() ?: 'png';
                 $folderName = '/image_web';
                 $picture = mt_rand(100000, 999999). '.' . $extension;
                 $destinationPath = public_path() . $folderName;
                 $request->file('favicon')->move($destinationPath, $picture);
                 $store->favicon =$picture;
                 $image_path=public_path().'/image_web/'.$logo_img;
                  if(file_exists($image_path)) {
                            try{
                                 unlink($image_path);
                            }
                            catch(\Exception $e)
                            {
                                
                            }
                            
                        }
            }
             if ($request->hasFile('logo')) 
            {
                 $logo_img=$store->logo;
                 $file = $request->file('logo');
                 $filename = $file->getClientOriginalName();
                 $extension = $file->getClientOriginalExtension() ?: 'png';
                 $folderName = '/image_web';
                 $picture = mt_rand(100000, 999999). '.' . $extension;
                 $destinationPath = public_path() . $folderName;
                 $request->file('logo')->move($destinationPath, $picture);
                 $store->logo =$picture;
                 $image_path=public_path().'/image_web/'.$logo_img;
                  if(file_exists($image_path)) {
                            try{
                                 unlink($image_path);
                            }
                            catch(\Exception $e)
                            {
                                
                            }
                            
                        }
            }
             if ($request->hasFile('app_banner')) 
            {
                 $logo_img=$store->app_banner;
                 $file = $request->file('app_banner');
                 $filename = $file->getClientOriginalName();
                 $extension = $file->getClientOriginalExtension() ?: 'png';
                 $folderName = '/image_web';
                 $picture = mt_rand(100000, 999999). '.' . $extension;
                 $destinationPath = public_path() . $folderName;
                 $request->file('app_banner')->move($destinationPath, $picture);
                 $store->app_banner =$picture;
                 $image_path=public_path().'/image_web/'.$logo_img;
                  if(file_exists($image_path)) {
                            try{
                                 unlink($image_path);
                            }
                            catch(\Exception $e)
                            {
                                
                            }
                            
                        }
            }
             if ($request->hasFile('icon1')) 
            {
                 $logo_img=$store->icon1;
                 $file = $request->file('icon1');
                 $filename = $file->getClientOriginalName();
                 $extension = $file->getClientOriginalExtension() ?: 'png';
                 $folderName = '/image_web';
                 $picture = mt_rand(100000, 999999). '.' . $extension;
                 $destinationPath = public_path() . $folderName;
                 $request->file('icon1')->move($destinationPath, $picture);
                 $store->icon1 =$picture;
                 $image_path=public_path().'/image_web/'.$logo_img;
                  if(file_exists($image_path)) {
                            try{
                                 unlink($image_path);
                            }
                            catch(\Exception $e)
                            {
                                
                            }
                            
                        }
            }
             if ($request->hasFile('icon2')) 
            {
                 $logo_img=$store->icon2;
                 $file = $request->file('icon2');
                 $filename = $file->getClientOriginalName();
                 $extension = $file->getClientOriginalExtension() ?: 'png';
                 $folderName = '/image_web';
                 $picture = mt_rand(100000, 999999). '.' . $extension;
                 $destinationPath = public_path() . $folderName;
                 $request->file('icon2')->move($destinationPath, $picture);
                 $store->icon2 =$picture;
                 $image_path=public_path().'/image_web/'.$logo_img;
                  if(file_exists($image_path)) {
                            try{
                                 unlink($image_path);
                            }
                            catch(\Exception $e)
                            {
                                
                            }
                            
                        }
            }
             if ($request->hasFile('icon3')) 
            {
                 $logo_img=$store->icon3;
                 $file = $request->file('icon3');
                 $filename = $file->getClientOriginalName();
                 $extension = $file->getClientOriginalExtension() ?: 'png';
                 $folderName = '/image_web';
                 $picture = mt_rand(100000, 999999). '.' . $extension;
                 $destinationPath = public_path() . $folderName;
                 $request->file('icon3')->move($destinationPath, $picture);
                 $store->icon3 =$picture;
                 $image_path=public_path().'/image_web/'.$logo_img;
                  if(file_exists($image_path)) {
                            try{
                                 unlink($image_path);
                            }
                            catch(\Exception $e)
                            {
                                
                            }
                            
                        }
            }
         $store->save();
         Session::flash('message',"Setting Update Successfully");
         Session::flash('alert-class', 'alert-success');
         return redirect()->back();
     }
}
