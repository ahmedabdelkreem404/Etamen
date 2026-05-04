<?php

namespace App\Http\Controllers;
use App\Models\Setting;
use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Package;
use App\Models\PackageBenefit;
use App\Models\Speciality;
use App\Models\Document;
use App\Models\Schedule;
use App\Models\Family;
use App\Models\Subscription;
use App\Models\Subscriber;
use App\Models\Slot;
use App\Models\Review;
use App\Models\Appointment;
use App\Models\About;
use App\Models\Notification;
use App\Models\Prescription;
use App\Models\ReportSpam;
use App\Models\Medicines;
use App\Models\AppointmentMedicines;
use App\Models\Token;
use App\Models\ServiceOrder;

use Auth;
use Session;
use validate;
use DB;
use DataTables;
use DateTimeZone;
use DateTime;
use Hash;
use Mail;
use Illuminate\Http\Request;

class MedicinesController extends Controller
{
    
    public function showmedicines(){
        $data = Medicines::get();
        return view("admin.medicines.default",compact('data'));
    }
    
    
    
    public function index()
    {
    	$data = Medicines::with('speciality')->get();
    	return view("admin.medicines.default",compact('data'));
    }

    public function medicinesadd1()
    {
    	return view("admin.medicines.add-medicines");
    }

    public function addmedicinessave(Request $request)
    {
        foreach($request->input('type') as $new){
            $types[]=$new;
         }
         $alltype = implode(',',$types);

	   	$data = new Medicines();
	   	// $data->image = $img_url;
	   	$data->name = $request->get("name");
	   	$data->description = $request->get("description");
	   	$data->dosage = $request->get("dosage");
        $data->medicine_type = $alltype;
	   	$data->save();

	   	$msg = __("Successfull Medicine Added");
	   	Session::flash('message',$msg);
        Session::flash('alert-class', 'alert-success');
        return redirect("admin/medicines");
        // return $request;
        
    // 	return view("admin.medicines.default");
    }
    
    
     public function deletemedicines($id)
    {
    	$data = Medicines::find($id);
        $data->delete();
        $msg = __("Successfull Medicine Delete");
	   	Session::flash('message',$msg);
        Session::flash('alert-class', 'alert-success');
      return redirect("admin/medicines");
    }
    
     public function medicine(){
         $data = Medicines::get();
             
         return DataTables::of($data)
            ->make(true);
     }
    

    public function editmedicines($id)
    {
    	$data = Medicines::find($id);
     	return view("admin.medicines.edit-medicines",compact('data'));
    }

    public function editmedicinessave(Request $request)
    {
        // return $request;
    	foreach($request->input('type') as $new){
            $types[]=$new;
         }
         $alltype = implode(',',$types);

         $data=Medicines::find($request->get("id"));
	   	$data->name = $request->get("name");
	   	$data->dosage = $request->get("dosage");
	   	$data->description = $request->get("description");
           $data->medicine_type = $alltype;
	   	$data->save();

	   	$msg = __("Successfull Medicine Update");
	   	Session::flash('message',$msg);
        Session::flash('alert-class', 'alert-success');
       return redirect("admin/medicines");
    }

   
}
