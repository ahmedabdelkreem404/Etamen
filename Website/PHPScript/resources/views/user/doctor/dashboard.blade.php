@extends('user.layout')
@section('title')
{{__('message.Doctor Dashboard')}}
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
            <h1>{{__('message.Doctor Dashboard')}}</h1>
         </div>
      </div>
   </div>
   <div class="lower-content">
      <ul class="bread-crumb clearfix">
         <li><a href="{{url('/')}}">{{__('message.Home')}}</a></li>
         <li>{{__('message.Doctor Dashboard')}}</li>
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
               <li><a href="{{url('doctordashboard')}}" class="current"><i class="fas fa-columns"></i>{{__('message.Dashboard')}}</a></li>
               <li><a href="{{url('doctorappointment')}}" ><i class="fas fa-calendar-alt"></i>{{__('message.Appointment')}}</a></li>
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
            <div class="feature-content">
               <div class="row clearfix">
                  <div class="col-xl-4 col-lg-12 col-md-12 feature-block">
                     <div class="feature-block-two">
                        <div class="inner-box">
                           <div class="pattern">
                              <div class="pattern-1" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-79.png')}}');"></div>
                              <div class="pattern-2" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-80.png')}}');"></div>
                           </div>
                           <div class="icon-box"><i class="icon-Dashboard-1"></i></div>
                           <h3>{{$totalappointment}}</h3>
                           <h5>{{__('message.Total Appointment')}}</h5>
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
                           <div class="icon-box"><i class="icon-Dashboard-5"></i></div>
                           <h3>{{$totalreview}}</h3>
                           <h5>{{__('message.Total Review')}}</h5>
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
                           <div class="icon-box"><i class="icon-Dashboard-3"></i></div>
                           <h3>{{$totalnewappointment}}</h3>
                           <h5>{{__('message.New Appointments')}}</h5>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="doctors-appointment">
               <div class="title-box">
                  <h3>{{__('message.Patients Appointments')}}</h3>
                  <div class="btn-box">
                         @if($type==2)
	                         <a href="{{url('doctordashboard?type=2')}}" class="theme-btn-one">{{__('message.past')}} <i class="icon-Arrow-Right"></i></a>
	                     @else
	                         <a href="{{url('doctordashboard?type=2')}}" class="theme-btn-two">{{__('message.past')}}</a>
	                     @endif
	                     @if(!isset($type))
	                         <a href="{{url('doctordashboard')}}" class="theme-btn-one">{{__('message.Today')}} <i class="icon-Arrow-Right"></i></a>
	                     @else
	                         <a href="{{url('doctordashboard')}}" class="theme-btn-two">{{__('message.Today')}}</a>
	                     @endif
	                     @if($type==3)
	                          <a href="{{url('doctordashboard?type=3')}}" class="theme-btn-one">{{__('message.Upcoming')}} <i class="icon-Arrow-Right"></i></a>
	                     @else
	                          <a href="{{url('doctordashboard?type=3')}}" class="theme-btn-two">{{__('message.Upcoming')}}</a>
	                     @endif
                  </div>
               </div>
               <div class="doctors-list">
                  <div class="table-outer">
                     <table class="doctors-table">
                        <thead class="table-header">
                           <tr>
                              <th>{{__("message.Patient Name")}}</th>
                              <th>{{__("message.Date")}}</th>
                              <th>{{__("message.Phone")}}</th>
                              <th>{{__("message.Status")}}</th>
                              
                           </tr>
                        </thead>
                        <tbody>
                          @if(count($bookdata)>0)
                            @foreach($bookdata as $bo)
		                           <tr>
		                              <td>
		                                 <div class="name-box">
		                                    <figure class="image">
		                                    	@if(isset($bo->patientls)&&$bo->patientls->profile_pic!="")
		                                    	<img src="{{asset('public/upload/profile/').'/'.$bo->patientls->profile_pic}}" alt="">
		                                    	@else
		                                    	  <img src="{{asset('public/upload/profile/profile.png')}}" alt="">
		                                    	@endif
		                                    	
		                                    </figure>
		                                    <h5>{{$bo->patientls->name}}</h5>
		                                   
		                                 </div>
		                              </td>
		                              <td>
		                                 <p>{{date("F d,Y",strtotime($bo->date))}}</p>
		                                 <span class="time">{{$bo->slot_name}}</span>
		                              </td>
		                              <td>
		                                 <p>{{$bo->phone}}</p>
		                              </td>
		                              <td>
		                                 <?php 
                                    if($bo->status=='1'){
                                         echo '<span class="status">'.__("message.Received").'</span>';
                                    }else if($bo->status=='2'){
                                         echo '<span class="status">'. __("message.Approved").'</span>';
                                    }else if($bo->status=='3'){
                                         echo '<span class="status">'. __("message.In Process").'</span>';
                                    }
                                    else if($bo->status=='4'){
                                         echo '<span class="status">'. __("message.Completed").'</span>';
                                    }
                                    else if($bo->status=='5'){
                                         echo '<span class="status">'. __("message.Rejected").'</span>';
                                    }else{
                                         echo '<span class="status">'. __("message.Absent").'</span>';
                                    }   
                                    ?>
		                              </td>                            
		                                                    
		                           </tr>
                            @endforeach
                           @else
                             <tr><td colspan="5" style="text-align: center;    padding: 18px;">{{__("message.No Data Found")}}</td></tr>
                           @endif
                        </tbody>
                     </table>
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