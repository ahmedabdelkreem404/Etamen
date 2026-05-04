<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Session;
use validate;
use Sentinel;
use DB;
use App\Models\User;
use App\Models\Services;
use App\Models\Review;
use App\Models\BookAppointment;
use App\Models\Schedule;
use App\Models\Doctors;
use DataTables;
class ProductController extends Controller
{
    
     public function showservice(){
        return view("admin.service.default");
     }
    
     public function saveservices($id){
        $data=Services::find($id);
        return view("admin.service.saveservices")->with("data",$data)->with("id",$id);
     }

     public function updateservice(Request $request){
     	if($request->get("id")==0){
           $store=new Services(); 	
           $msg=__("message.Specialities Add Successfully");  
           $rel_url="";
     	}else{
     	   $store=Services::find($request->get("id"));
     	   $msg=__("message.Specialities Update Successfully");
     	   $rel_url=$store->icon;
           $img_url=$rel_url;
     	}
     	if ($request->hasFile('icon')) 
              {
                 $file = $request->file('icon');
                 $filename = $file->getClientOriginalName();
                 $extension = $file->getClientOriginalExtension() ?: 'png';
                 $folderName = '/upload/services/';
                 $picture = time() . '.' . $extension;
                 $destinationPath = public_path() . $folderName;
                 $request->file('icon')->move($destinationPath, $picture);
                 $img_url =$picture;                
                  $image_path = public_path() ."/upload/services/".$rel_url;
                    if(file_exists($image_path)&&$rel_url!="") {
                        try {
                             unlink($image_path);
                        }
                        catch(Exception $e) {
                          
                        }                        
                  }
             }
     	$store->name=$request->get("name");
     	$store->icon=$img_url;
     	$store->save();
     	Session::flash('message',$msg); 
        Session::flash('alert-class', 'alert-success');
        return redirect("admin/services");
     }

	 public function servicestable(){
	 	$services =Services::all();

        return DataTables::of($services)
            ->editColumn('id', function ($services) {
                return $services->id;
            })
            ->editColumn('icon', function ($services) {
                return asset("public/upload/services").'/'.$services->icon;
            })
            ->editColumn('name', function ($services) {
                return $services->name;
            })          
            ->editColumn('action', function ($services) {   
               $edit= url('admin/saveservices',array('id'=>$services->id));
               $delete= url('admin/deleteservices',array('id'=>$services->id));
               $return = '<a  rel="tooltip" title="" href="'.$edit.'" class="m-b-10 m-l-5" data-original-title="Remove"><i class="fa fa-edit f-s-25" style="margin-right: 10px;"></i></a><a onclick="delete_record(' . "'" . $delete . "'" . ')" rel="tooltip" title="" class="m-b-10 m-l-5" data-original-title="Remove"><i class="fa fa-trash f-s-25"></i></a>';
              return $return;               
            })
           
            ->make(true);
	 }


     public function showreviews(){
         return view("admin.product.review");
     }

     public function reviewtable(){
        $review =Review::all();

        return DataTables::of($review)
            ->editColumn('id', function ($review) {
                return $review->id;
            })
            ->editColumn('product_name', function ($review) {
                 $data=Products::find($review->product_id);
                return isset($data)?$data->name:"";
            })
            ->editColumn('username', function ($review) {
                return isset($review->name)?$review->name:"";
            }) 
             ->editColumn('ratting', function ($review) {
                return isset($review->rating)?$review->rating:"";
            })
             ->editColumn('email', function ($review) {
                return isset($review->email)?$review->email:"";
            })
             ->editColumn('comment', function ($review) {
                return isset($review->comment)?$review->comment:"";
            })            
            ->editColumn('action', function ($review) {   
               
               $return = '';
              return $return;               
            })
           
            ->make(true);
     }

     public function deleteservices($id){
        $service=Services::find($id);
        if($service){
            $data=Doctors::where("department_id",$id)->get();
            if(count($data)>0){
                foreach ($data as $doctor) {                    
                        if($doctor){
                            $deletsolt=Schedule::with('getslotls')->where("doctor_id",$doctor->id)->get();
                            foreach ($deletsolt as $de) {
                                foreach ($de->getslotls as $k) {
                                   $k->delete();
                                }
                                $de->delete();
                            }
                            $bookappointment=BookAppointment::where("doctor_id",$doctor->id)->delete();
                            $review=Review::where("doc_id",$doctor->id)->delete();
                            $image_path = public_path() ."/upload/doctors/".$doctor->image;
                                if(file_exists($image_path)) {
                                    try {
                                            unlink($image_path);
                                        }
                                    catch(Exception $e) {
                                    }                        
                                }
                            $doctor->delete();
                        }
                }
            }
            $image_path = public_path() ."/upload/services/".$service->icon;
            if(file_exists($image_path)) {
                try {
                        unlink($image_path);
                    }
                catch(Exception $e) {
                }                        
            }
            $service->delete();
        }
        Session::flash('message',__("message.Specialities Delete Successfully")); 
        Session::flash('alert-class', 'alert-success');
        return redirect()->back();
     }
    
}
