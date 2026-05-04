<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\carbon;
use App\Models\Doctors;
use App\Models\Patient;
use App\Models\Subscriber;
use App\Models\BookAppointment;
use App\Models\Subscription;

class ReportController extends Controller
{

    public function doctor_report(Request $request):Response
    {
        $query = Doctors::query();
        $data=$request->data_filter;
        $start_date=$request->start_date;
        $end_date=$request->end_date;

        switch($data){
            case '1':
                $query->whereDate('created_at','>=',$start_date)->whereDate('created_at','<=',$end_date)->get();
                break;
            case 'today':
                $query->whereDate('created_at',carbon::today());
                break;
            case 'last_week':
                $query->whereBetween('created_at',[carbon::now()->subweek(),carbon::now()]);
                break;
            case 'this_month':
                $query->whereMonth('created_at',carbon::now()->month);
                break;
            case 'last_month':
                $query->whereMonth('created_at',carbon::now()->subMonth()->month);
                break;
            case 'this_year':
                $query->whereYear('created_at',carbon::now()->year);
                break;
            case 'last_year':
                $query->whereYear('created_at',carbon::now()->subYear()->year);
                break;
        }
        $doctors=$query->get();
        $total = $query->count();
        return response()->view('admin.report.doctor',compact('doctors','total'));
    }

    public function user_report(Request $request){
        $query = Patient::query();
        $data=$request->data_filter;
        $start_date=$request->start_date;
        $end_date=$request->end_date;


        switch($data){
            case '1':
                $query->whereDate('created_at','>=',$start_date)->whereDate('created_at','<=',$end_date)->get();
                break;
            case 'today':
                $query->whereDate('created_at',carbon::today());
                break;
            case 'last_week':
                $query->whereBetween('created_at',[carbon::now()->subweek(),carbon::now()]);
                break;
            case 'this_month':
                $query->whereMonth('created_at',carbon::now()->month);
                break;
            case 'last_month':
                $query->whereMonth('created_at',carbon::now()->subMonth()->month);
                break;
            case 'this_year':
                $query->whereYear('created_at',carbon::now()->year);
                break;
            case 'last_year':
                $query->whereYear('created_at',carbon::now()->subYear()->year);
                break;
        }
        $user=$query->get();
        $total = $query->count();
        return view('admin.report.user',compact('user','total'));
    }

    public function do_sub_report(Request $request){
        $query = Subscriber::query();
        $data=$request->data_filter;
        $start_date=$request->start_date;
        $end_date=$request->end_date;


        switch($data){
            case '1':
                $query->whereDate('created_at','>=',$start_date)->whereDate('created_at','<=',$end_date)->get();
                break;
            case 'today':
                $query->whereDate('created_at',carbon::today());
                break;
            case 'last_week':
                $query->whereBetween('created_at',[carbon::now()->subweek(),carbon::now()]);
                break;
            case 'this_month':
                $query->whereMonth('created_at',carbon::now()->month);
                break;
            case 'last_month':
                $query->whereMonth('created_at',carbon::now()->subMonth()->month);
                break;
            case 'this_year':
                $query->whereYear('created_at',carbon::now()->year);
                break;
            case 'last_year':
                $query->whereYear('created_at',carbon::now()->subYear()->year);
                break;
        }
        $doctorsubscription=$query->get();
        $total = $query->count();
        return view('admin.report.doctorsubscription',compact('doctorsubscription','total'));
    }

    public function app_book_report(Request $request){
        $query = BookAppointment::query();
        $data=$request->data_filter;
        $start_date=$request->start_date;
        $end_date=$request->end_date;


        switch($data){
            case '1':
                $query->whereDate('created_at','>=',$start_date)->whereDate('created_at','<=',$end_date)->get();
                break;
            case 'today':
                $query->whereDate('created_at',carbon::today());
                break;
            case 'last_week':
                $query->whereBetween('created_at',[carbon::now()->subweek(),carbon::now()]);
                break;
            case 'this_month':
                $query->whereMonth('created_at',carbon::now()->month);
                break;
            case 'last_month':
                $query->whereMonth('created_at',carbon::now()->subMonth()->month);
                break;
            case 'this_year':
                $query->whereYear('created_at',carbon::now()->year);
                break;
            case 'last_year':
                $query->whereYear('created_at',carbon::now()->subYear()->year);
                break;
        }
        $appointmentbook=$query->get();
        $total = $query->count();
        return view('admin.report.appointmentbook',compact('appointmentbook','total'));
    }
}
