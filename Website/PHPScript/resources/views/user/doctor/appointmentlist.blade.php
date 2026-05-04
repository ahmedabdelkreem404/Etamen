@extends('user.layout')
@section('title')
{{__('message.Appointment List')}}
@stop
@section('meta-data')
<meta property="og:type" content="website"/>
<meta property="og:url" content="{{__('message.System Name')}}"/>
<meta property="og:title" content="{{__('message.System Name')}}"/>
<meta property="og:image" content="{{asset('public/image_web/').'/'.$setting->favicon}}"/>
<meta property="og:image:width" content="250px"/>
<meta property="og:image:height" content="250px"/>
<meta property="og:site_name" content="{{__('message.System Name')}}"/>
<meta property="og:description" content="{{__('message.meta_description')}}"/>
<meta property="og:keyword" content="{{__('message.Meta Keyword')}}"/>
<link rel="shortcut icon" href="{{asset('public/image_web/').'/'.$setting->favicon}}">
<meta name="viewport" content="width=device-width, initial-scale=1">
@stop
@section('content')
<section class="page-title-two">
   <div class="title-box centred bg-color-2">
      <div class="pattern-layer">
         <div class="pattern-1" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-70.png')}}');"></div>
         <div class="pattern-2" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-71.png')}}');"></div>
      </div>
      <div class="auto-container">
         <div class="title">
            <h1>{{__('message.Appointment List')}}</h1>
         </div>
      </div>
   </div>
   <div class="lower-content">
      <ul class="bread-crumb clearfix">
         <li><a href="{{url('/')}}">{{__('message.Home')}}</a></li>
         <li>{{__('message.Appointment List')}}</li>
      </ul>
   </div>
</section>
<section class="doctors-dashboard bg-color-3">
   <div class="left-panel">
      <div class="profile-box">
         <div class="upper-box">
            <figure class="profile-image">
               @if($doctordata->image!="")
               <img src="{{asset('public/upload/doctors').'/'.$doctordata->image}}" alt="">
               @else
               <img src="{{asset('public/front_pro/assets/images/resource/profile-2.png')}}" alt="">
               @endif
            </figure>
            <div class="title-box centred">
               <div class="inner">
                  <h3>{{$doctordata->name}}</h3>
                  <p>{{isset($doctordata->departmentls)?$doctordata->departmentls->name:""}}</p>
               </div>
            </div>
         </div>
         <div class="profile-info">
            <ul class="list clearfix">
               <li><a href="{{url('doctordashboard')}}" ><i class="fas fa-columns"></i>{{__('message.Dashboard')}}</a></li>
               <li><a href="{{url('doctorappointment')}}" class="current"><i class="fas fa-calendar-alt"></i>{{__('message.Appointment')}}</a></li>
               <li><a href="{{url('doctortiming')}}"><i class="fas fa-clock"></i>{{__('message.Schedule Timing')}}</a></li>
               <li><a href="{{url('doctorreview')}}" ><i class="fas fa-star"></i>{{__('message.Reviews')}}</a></li>
               <li><a href="{{url('doctor_hoilday')}}" ><i class="fas fa-star"></i>{{__('message.My Hoilday')}}</a></li>
               <li><a href="{{url('doctoreditprofile')}}"><i class="fas fa-user"></i>{{__('message.My Profile')}}</a></li>
               <li><a href="{{url('paymenthistory')}}"><i class="fas fa-user"></i>{{__('message.Payment History')}}</a></li>
               <li><a href="{{url('doctorchangepassword')}}"><i class="fas fa-unlock-alt"></i>{{__('message.Change Password')}}</a></li>
               <li><a href="{{url('logout')}}"><i class="fas fa-sign-out-alt"></i>{{__("message.Logout")}}</a></li>
            </ul>
         </div>
      </div>
   </div>
   <div class="right-panel">
      <div class="content-container">
        <div class="outer-container">
                        <div class="appointment-list">
                            @if(Session::has('message'))
                                <div class="col-sm-12">
                                   <div class="alert  {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                      {{ Session::get('message') }}
                                      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                      <span aria-hidden="true">&times;</span>
                                      </button>
                                   </div>
                                </div>
                            @endif
                            <div class="upper-box clearfix">
                                <div class="text pull-left">
                                    <h3>{{__('message.Appointment List')}}</h3>
                                </div>
                                <div class="select-box pull-right">
                                    <select class="custom-dropdown" style="width: 100%;border: 1px solid #ada3a3;padding: 15px 30px;border-radius: 15px;">
                                       <option value="">{{__("message.Any Status")}}</option>
                                       <option value="1">{{__("message.Received")}}</option>
                                       <option value="3">{{__("message.In Process")}}</option>
                                       <option value="4">{{__("message.Completed")}}</option>
                                       <option value="5">{{__("message.Absent")}}</option>
                                       <option value="0">{{__("message.Cancelled")}}</option>
                                    </select>
                                </div>
                            </div>
                           @if(count($appointmentdata)>0)
                              @foreach($appointmentdata as $am)
                                  <div class="single-item">
                                      <figure class="image-box">
                                        @if($am->patientls->profile_pic!="")
                                            <img src="{{asset('public/upload/profile').'/'.$am->patientls->profile_pic}}" alt="">
                                        @else
                                             <img src="{{asset('public/upload/profile/profile.png')}}" alt="">
                                        @endif
                                      </figure>
                                      <div class="inner">
                                          <h4>{{$am->patientls->name}}</h4>
                                          <ul class="info-list clearfix">
                                              <li><i class="fas fa-clock"></i>{{date("F d,Y",strtotime($am->date))}}, {{$am->slot_name}}</li>
                                             
                                             
                                              <li><i class="fas fa-envelope"></i><a href="mailto:{{$am->patientls->email}}">{{$am->patientls->email}}</a></li>
                                              <li><i class="fas fa-phone"></i><a href="tel:2265458856">{{$am->phone}}</a></li>
                                              <li><i class="fas fa-sticky-note"></i>
                                                 {{$am->user_description}}
                                              </li>
                                               @if($am->prescription_file!="")
                                             <li><a href="{{asset('public/upload/prescription').'/'.$am->prescription_file}}" target="_blank" class="btn btn-success" style="color:white">{{__("message.View Prescription")}}</a></li>
                                             @endif
                                               <li style="float: left;background: #453f85;color: white;padding: 7px 23px;border-radius: 15px;">
                                               <?php 
                                                      if($am->status=='1'){
                                                           echo __("message.Received");
                                                      }else if($am->status=='2'){
                                                           echo __("message.Approved");
                                                      }else if($am->status=='3'){
                                                           echo __("message.In Process");
                                                      }
                                                      else if($am->status=='4'){
                                                           echo __("message.Completed");
                                                      }
                                                      else if($am->status=='5'){
                                                           echo __("message.Rejected");
                                                      }else{
                                                           echo __("message.Absent");
                                                      }
                                               ?>
                                             </li>

                                          </ul>
                                          
                                         
                                         
                                          <ul class="confirm-list clearfix">
                                             @if($am->status=='1')
                                                <li><a href="{{url('changeappointment').'/3/'.$am->id}}"><i class="fas fa-check"></i>{{__("message.Accept")}}</a></li>
                                                <li><a href="{{url('changeappointment').'/5/'.$am->id}}"><i class="fas fa-times"></i>{{__("message.Cancel")}}</a></li>
                                             @elseif($am->status=='3')
                                             <!-- href="{{url('changeappointment').'/4/'.$am->id}}" -->
                                                <li><a href="javascript:void(0)" onclick="completeappointment('{{$am->id}}')" data-toggle="modal" data-target="#myModal"><i class="fas fa-check"></i>{{__("message.Complete")}}</a></li>
                                                <li><a href="{{url('changeappointment').'/0/'.$am->id}}"><i class="fas fa-times"></i>{{__("message.Absent")}}</a></li>
                                             @endif
                                          </ul>
                                      </div>
                                  </div>
                              @endforeach
                           @endif
                        </div>
                        {{$appointmentdata->links()}}
            </div>
      </div>
   </div>
</section>
<div class="modal" id="myModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">{{__("message.Add Prescription")}}</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
<form action="{{route('complete-doctor-appointment')}}" method="post" enctype="multipart/form-data" class="registration-form">
                                 {{csrf_field()}}
      <input type="hidden" name="id" id="appointment_id" />
      <div class="modal-body">
           <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                        <label class="fr">{{__('message.Upload Prescription')}}</label>
                                        <input type="file" name="prescription" id="prescription" required>
                                    </div>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
         <button type="submit" class="btn btn-success">{{__('message.Submit')}}</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>
</form>
    </div>
  </div>
</div>
@stop
@section('footer')
@stop