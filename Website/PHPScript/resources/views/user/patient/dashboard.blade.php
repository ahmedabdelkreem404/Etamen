@extends('user.layout')
@section('title')
{{__('message.Patient Dashboard')}}
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
            <h1>{{__('message.Patient Dashboard')}}</h1>
         </div>
      </div>
   </div>
   <div class="lower-content">
      <ul class="bread-crumb clearfix">
         <li><a href="{{url('/')}}">{{__('message.Home')}}</a></li>
         <li>{{__('message.Patient Dashboard')}}</li>
      </ul>
   </div>
</section>
<section class="patient-dashboard bg-color-3">
   <div class="left-panel">
      <div class="profile-box patient-profile">
         <div class="upper-box">
            <figure class="profile-image">
               @if(isset($userdata)&&$userdata->profile_pic!="")
               <img src="{{asset('public/upload/profile').'/'.$userdata->profile_pic}}" alt="">
               @else
               <img src="{{asset('public/upload/profile/profile.png')}}" alt="">
               @endif
            </figure>
            <div class="title-box centred">
               <div class="inner">
                  <h3>{{isset($userdata->name)?$userdata->name:""}}</h3>
                  <p><i class="fas fa-envelope"></i>{{isset($userdata->email)?$userdata->email:""}}</p>
               </div>
            </div>
         </div>
         <div class="profile-info">
            <ul class="list clearfix">
               <li><a href="{{url('userdashboard')}}" class="current"><i class="fas fa-columns"></i>{{__('message.Dashboard')}}</a></li>
               <li><a href="{{url('favouriteuser')}}"><i class="fas fa-heart"></i>{{__('message.Favourite Doctors')}}</a></li>
               <li><a href="{{url('viewschedule')}}"><i class="fas fa-clock"></i>{{__('message.Schedule Timing')}}</a></li>
               <li><a href="{{url('userreview')}}" ><i class="fas fa-comments"></i>{{__('message.Review')}}</a></li>
               <li><a href="{{url('usereditprofile')}}" ><i class="fas fa-user"></i>{{__('message.My Profile')}}</a></li>
               <li><a href="{{url('changepassword')}}"><i class="fas fa-unlock-alt"></i>{{__('message.Change Password')}}</a></li>
               <li><a href="{{url('logout')}}"><i class="fas fa-sign-out-alt"></i>{{__('message.Logout')}}</a></li>
            </ul>
         </div>
      </div>
   </div>
   <div class="right-panel">
      <div class="content-container">
         <div class="outer-container">
            <div class="feature-content">
               <div class="row clearfix">
                  <div class="col-xl-4 col-lg-12 col-md-12 feature-block">
                     <div class="feature-block-two">
                        <div class="inner-box">
                           <div class="pattern">
                              <div class="pattern-1" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-79.png')}}');"></div>
                              <div class="pattern-2" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-80.png')}}');"></div>
                           </div>
                           <div class="icon-box"><i class="icon-Dashboard-3"></i></div>
                           <h3>{{$totalappointment}}</h3>
                           <h5>{{__('message.Total')}}</h5>
                           <h5>{{__("message.Appointment")}}</h5>
                        </div>
                     </div>
                  </div>
                  <div class="col-xl-4 col-lg-12 col-md-12 feature-block">
                     <div class="feature-block-two">
                        <div class="inner-box">
                           <div class="pattern">
                              <div class="pattern-1" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-81.png')}}');"></div>
                              <div class="pattern-2" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-82.png')}}');"></div>
                           </div>
                           <div class="icon-box"><i class="icon-Dashboard-email-4"></i></div>
                           <h3>{{$completeappointment}}</h3>
                           <h5>{{__('message.Completed')}}</h5>
                           <h5>{{__("message.Appointment")}}</h5>
                        </div>
                     </div>
                  </div>
                  <div class="col-xl-4 col-lg-12 col-md-12 feature-block">
                     <div class="feature-block-two">
                        <div class="inner-box">
                           <div class="pattern">
                              <div class="pattern-1" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-83.png')}}');"></div>
                              <div class="pattern-2" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-84.png')}}');"></div>
                           </div>
                           <div class="icon-box"><i class="icon-Dashboard-5"></i></div>
                           <h3>{{$pendingappointment}}</h3>
                           <h5>{{__("message.Pending")}}</h5>
                           <h5>{{__("message.Appointment")}}</h5>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="doctors-appointment">
               <div class="title-box">
                  <h3>{{__("message.Doctors Appointment")}}</h3>
                  <div class="btn-box">
                     @if($type==2)
                     <a href="{{url('userdashboard?type=2')}}" class="theme-btn-one">{{__('message.past')}} <i class="icon-Arrow-Right"></i></a>
                     @else
                     <a href="{{url('userdashboard?type=2')}}" class="theme-btn-two">{{__('message.past')}}</a>
                     @endif
                     @if(!isset($type))
                     <a href="{{url('userdashboard')}}" class="theme-btn-one">{{__('message.Today')}} <i class="icon-Arrow-Right"></i></a>
                     @else
                     <a href="{{url('userdashboard')}}" class="theme-btn-two">{{__('message.Today')}}</a>
                     @endif
                     @if($type==3)
                     <a href="{{url('userdashboard?type=3')}}" class="theme-btn-one">{{__('message.Upcoming')}} <i class="icon-Arrow-Right"></i></a>
                     @else
                     <a href="{{url('userdashboard?type=3')}}" class="theme-btn-two">{{__('message.Upcoming')}}</a>
                     @endif
                  </div>
               </div>
               <div class="doctors-list">
                  <div class="table-outer">
                     <table class="doctors-table">
                        <thead class="table-header">
                           <tr>
                              <th>{{__('message.Doctor Name')}}</th>
                              <th>{{__('message.Phone')}}</th>
                              <th>{{__('message.Date')}}</th>
                              <th>{{__('message.Status')}}</th>
                              <th>{{__('message.Action')}}</th>
                              
                           </tr>
                        </thead>
                        <tbody>
                           @foreach($bookdata as $bdata)
                           <tr>
                              <td>
                                 <div class="name-box">
                                    <figure class="image">
                                       @if(isset($bdata->doctorls))
                                       <img src="{{asset('public/upload/doctors').'/'.$bdata->doctorls->image}}" alt="">
                                       @else
                                       <img src="{{asset('public/upload/profile/profile.png')}}" alt="">
                                       @endif
                                    </figure>
                                    <h5>{{isset($bdata->doctorls)?$bdata->doctorls->name:""}}</h5>
                                    <span class="designation">{{$bdata->department_name}}</span>
                                 </div>
                              </td>
                              <td>
                                 <p>{{$bdata->phone}}</p>
                              </td>
                              <td>
                                 <p>{{date("F d,Y",strtotime($bdata->date))}}</p>
                                 <span class="time">{{$bdata->slot_name}}</span>
                              </td>
                              <td>
                                 <?php 
                                    if($bdata->status=='1'){
                                         echo '<span class="status">'.__("message.Received").'</span>';
                                    }else if($bdata->status=='2'){
                                         echo '<span class="status">'. __("message.Approved").'</span>';
                                    }else if($bdata->status=='3'){
                                         echo '<span class="status">'. __("message.In Process").'</span>';
                                    }
                                    else if($bdata->status=='4'){
                                         echo '<span class="status">'. __("message.Completed").'</span>';
                                    }
                                    else if($bdata->status=='5'){
                                         echo '<span class="status">'. __("message.Rejected").'</span>';
                                    }else{
                                         echo '<span class="status">'. __("message.Absent").'</span>';
                                    }   
                                    ?>
                                     @if($bdata->prescription_file!="")
                                             <li><a href="{{asset('public/upload/prescription').'/'.$bdata->prescription_file}}" target="_blank" class="btn btn-success" style="color:white">{{__("message.View Prescription")}}</a></li>
                                             @endif
                              </td>
                              <td>
                                  @if($bdata->status=='2'||$bdata->status=='3'||$bdata->status=='1')
                                      <button type="button" class="btn btn-danger" onclick="reject_record('{{$bdata->id}}')">Reject</button>
                                  @endif
                              </td>
                             
                           </tr>
                           @endforeach
                        </tbody>
                     </table>
                     {{ $bookdata->links() }}
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