<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banner;
use Auth;
use Session;
use validate;
use DataTables;

class BannerController extends Controller
{
    public function showbanner(){
        return view("admin.banner.default");
    }

    public function bannertable(){
         $banner =Banner::get();
             
         return DataTables::of($banner)
            ->editColumn('id', function ($banner) {
                return $banner->id;
            })
            ->editColumn('image', function ($banner) {
                if($banner->image!=""){
                    return asset("public/upload/banner").'/'.$banner->image;
                }else{
                    return asset("public/upload/doctors/doctor_default.png");
                }
                
            })
            ->editColumn('action', function ($banner) {
            	
                 $edit= $banner->id;
                 
                 $delete= url('admin/deletebanner',array('id'=>$banner->id));
                 
                 $return = '<a   data-toggle="modal" style="margin-right: 10px;" data-target="#Backdrop"><i class="fa fa-edit f-s-25" style="color: #5870e2;" onclick="edit_img('.$edit.')" style="margin-right: 10px;"></i></a><a onclick="delete_record(' . "'" . $delete . "'" . ')" rel="tooltip" title="" class="m-b-10 m-l-5" data-original-title="Remove"><i class="fa fa-trash f-s-25"></i></a>';
                 return $return;
            })
            
            ->make(true);
    }

    public function savebanner(Request $request){
    	$image=array();
            if($files=$request->file('image')){
                foreach($files as $file){
                    $name=$file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension() ?: 'png';
                    $picture = rand().time() . '.' . $extension;
                    $folderName = '/upload/banner/';
                    $destinationPath = public_path() . $folderName;;
                    $file->move($destinationPath,$picture);

                    $banner = new Banner();
                    $banner->image = $picture; 
                    $banner->save();
                }
            }
        else{
            $image='';
        }
        

        Session::flash('message',__("message.Banner Add Successfully")); 
        Session::flash('alert-class', 'alert-success');
        return redirect()->back();

    }

    public function deletebanner($id){
        $banner=Banner::find($id);
        if($banner){
            $banner->delete();
        }
        Session::flash('message',__("message.Banner Delete Successfully")); 
        Session::flash('alert-class', 'alert-success');
        return redirect()->back();
     }

     public function edit_banner($id){
     	 $banner=Banner::find($id);
     	 $data = $banner->image;
     	 return $data;
     }
     public function updatebanner(Request $request){
     	$id = $request->id;
     	$banner=Banner::find($id);
     	$rel_url=$banner->image;
     	if($request->hasFile('image')){
          $file = $request->file('image');
          $fileName = $file->getClientOriginalName();
          $extension = $file->getClientOriginalExtension() ?: 'png';
          $picture = rand().time() . '.' . $extension;
          $destinationPath = Public_path("upload/banner/");
          $request->file('image')->move($destinationPath,$picture);
          $img_url= $picture;

          $image_path = public_path() ."/upload/banner/".$rel_url;
                    if(file_exists($image_path)) {
                        try {
                             unlink($image_path);
                        }
                        catch(Exception $e) {
                          
                        }                        
                  }
        }else{
        	$img_url= $rel_url;
        }

        $banners=Banner::find($id);
        $banners->image =$img_url;
        $banners->save();

     	Session::flash('message',__("message.Banner Edit Successfully")); 
        Session::flash('alert-class', 'alert-success');
        return redirect()->back();
     }

}
