@extends('user.layout')
@section('title')
{{__('message.Appointment Detail')}}
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
            <h1>{{__('message.Appointment Detail')}}</h1>
         </div>
      </div>
   </div>
   <div class="lower-content">
      <ul class="bread-crumb clearfix">
         <li><a href="{{url('/')}}">{{__("message.Home")}}</a></li>
         <li>{{__('message.Appointment Detail')}}</li>
      </ul>
   </div>
</section>
<section class="patient-dashboard bg-color-3">
   <div class="left-panel">
      <div class="profile-box patient-profile">
         <div class="upper-box">
            <figure class="profile-image">
               @if($userdata->profile_pic!="")
               <img src="{{asset('public/upload/profile').'/'.$userdata->profile_pic}}" alt="">
               @else
               <img src="{{asset('public/upload/profile/profile.png')}}" alt="">
               @endif
            </figure>
            <div class="title-box centred">
               <div class="inner">
                  <h3>{{$userdata->name}}</h3>
                  <p><i class="fas fa-envelope"></i>{{$userdata->email}}</p>
               </div>
            </div>
         </div>
         <div class="profile-info">
            <ul class="list clearfix">
               <li><a href="{{url('userdashboard')}}" ><i class="fas fa-columns"></i>{{__('message.Dashboard')}}</a></li>
               <li><a href="{{url('favouriteuser')}}"><i class="fas fa-heart"></i>{{__('message.Favourite Doctors')}}</a></li>
               <li><a href="{{url('viewschedule')}}"><i class="fas fa-clock"></i>{{__('message.Schedule Timing')}}</a></li>
               <li><a href="{{url('userreview')}}" ><i class="fas fa-comments"></i>{{__('message.Review')}}</a></li>
               <li><a href="{{url('usereditprofile')}}" ><i class="fas fa-user"></i>{{__('message.My Profile')}}</a></li>
               <li><a href="{{url('changepassword')}}" class="current"><i class="fas fa-unlock-alt"></i>{{__('message.Change Password')}}</a></li>
               <li><a href="{{url('logout')}}"><i class="fas fa-sign-out-alt"></i>{{__('message.Logout')}}</a></li>
            </ul>
         </div>
      </div>
   </div>
   <div class="right-panel">
      <div class="content-container">
         <div class="outer-container">
            <div class="add-listing change-password">
               <div class="single-box">
                  <div class="title-box">
                     <h3>{{__('message.Appointment Detail')}}</h3>
                  </div>
                  <div class="inner-box">
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
                     <div id="registererror"></div>
                     <div class="row">
                         <div class="col-md-3">
                             <img src="{{asset('public/upload/doctors').'/'.$viewappointment->doctorls->image}}">
                         </div>
                         <div class="col-md-6">
                              <p> <b>{{__("message.Appointment Time")}} : </b> {{$viewappointment->date}} {{$viewappointment->slot_name}}</p>
                     
                     <p> <b>{{__("message.description")}} : </b> {{$viewappointment->user_description}} 
                     
                    <p> <b> {{__("message.Phone no")}} : </b> {{$viewappointment->phone}}</p>
                     
                    <p> <b> {{__("message.Doctor Name")}} : </b> {{isset($viewappointment->doctorls)?$viewappointment->doctorls->name:''}}</p>
                    
                    <p> <b> {{__("message.Appointment Status")}} : </b>
                       <?php  if($viewappointment->status=='1'){
                             echo __("message.Received");
                        }else if($viewappointment->status=='2'){
                             echo __("message.Approved");
                        }else if($viewappointment->status=='3'){
                             echo __("message.In Process");
                        }
                        else if($viewappointment->status=='4'){
                             echo __("message.Completed");
                        }
                        else if($viewappointment->status=='5'){
                             echo __("message.Rejected");
                        }else{
                             echo __("message.Absent");
                        } ?>
                    </p>
                         </div>
                     </div>
                   
            </div>
         </div>
      </div>
   </div>
</section>
@stop
@section('footer')
@stop