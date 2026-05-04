<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ApiControllernew;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::any("searchdoctor",[ApiControllernew::class,"showsearchdoctor"]);
Route::any("nearbydoctor",[ApiControllernew::class,"nearbydoctor"]);
Route::any("register",[ApiControllernew::class,"postregisterpatient"]);
Route::any("registernew",[ApiControllernew::class,"postregisterpatient"]);
Route::any("user_reject_appointment",[ApiControllernew::class,"user_reject_appointment"]);

Route::any("savetoken",[ApiControllernew::class,"storetoken"]);
Route::any("login",[ApiControllernew::class,"showlogin"]);
Route::any("doctorregister",[ApiControllernew::class,"doctorregister"]);
Route::any("doctorlogin",[ApiControllernew::class,"doctorlogin"]);
Route::any("getspeciality",[ApiControllernew::class,"getspeciality"]);
Route::any("bookappointment",[ApiControllernew::class,"bookappointment"]);
Route::any("viewdoctor",[ApiControllernew::class,"viewdoctor"]);
Route::any("addreview",[ApiControllernew::class,"addreview"]);
Route::any("getslot",[ApiControllernew::class,"getslotdata"]);
Route::any("getlistofdoctorbyspecialty",[ApiControllernew::class,"getlistofdoctorbyspecialty"]);
Route::any("usersuappointment",[ApiControllernew::class,"usersupcomingappointment"]);
Route::any("userspastappointment",[ApiControllernew::class,"userspastappointment"]);
Route::any("doctoruappointment",[ApiControllernew::class,"doctoruappointment"]);
Route::any("doctorpastappointment",[ApiControllernew::class,"doctorpastappointment"]);
Route::any("reviewlistbydoctor",[ApiControllernew::class,"reviewlistbydoctor"]);
Route::any("doctordetail",[ApiControllernew::class,"doctordetail"]);
Route::any("appointmentdetail",[ApiControllernew::class,"appointmentdetail"]);
Route::any("doctoreditprofile",[ApiControllernew::class,"doctoreditprofile"]);
Route::any("usereditprofile",[ApiControllernew::class,"usereditprofile"]);
Route::any("getdoctorschedule",[ApiControllernew::class,"getdoctorschedule"]);
Route::any("Reportspam",[ApiControllernew::class,"saveReportspam"]);
Route::any("appointmentstatuschange",[ApiControllernew::class,"appointmentstatuschange"]);
Route::any("forgotpassword",[ApiControllernew::class,"forgotpassword"]);
Route::get("getalldoctors",[ApiControllernew::class,"getalldoctors"]);

Route::get("getholiday",[ApiControllernew::class,"getholiday"]);
Route::any("saveholiday",[ApiControllernew::class,"saveholiday"]);
Route::get("deleteholiday",[ApiControllernew::class,"deleteholiday"]);
Route::get("checkholiday",[ApiControllernew::class,"checkholiday"]);
























