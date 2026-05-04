@extends('user.layout')
@section('title')
{{__('message.Edit Profile')}}
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
            <h1>{{__('message.Edit Profile')}}</h1>
         </div>
      </div>
   </div>
   <div class="lower-content">
      <ul class="bread-crumb clearfix">
         <li><a href="{{url('/')}}">{{__('message.Home')}}</a></li>
         <li>{{__('message.Edit Profile')}}</li>
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
                <li><a href="{{url('userdashboard')}}"><i class="fas fa-columns"></i>{{__('message.Dashboard')}}</a></li>
               <li><a href="{{url('favouriteuser')}}"><i class="fas fa-heart"></i>{{__('message.Favourite Doctors')}}</a></li>
               <li><a href="{{url('viewschedule')}}"><i class="fas fa-clock"></i>{{__('message.Schedule Timing')}}</a></li>
               <li><a href="{{url('userreview')}}" ><i class="fas fa-comments"></i>{{__('message.Review')}}</a></li>
               <li><a href="{{url('usereditprofile')}}" class="current"><i class="fas fa-user"></i>{{__('message.My Profile')}}</a></li>
               <li><a href="{{url('changepassword')}}"><i class="fas fa-unlock-alt"></i>{{__('message.Change Password')}}</a></li>
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
                     <h3>{{__('message.Edit Profile')}}</h3>
                  </div>
                  <div class="inner-box">
                     <div class="profile-title">
                        <div class="upload-photo">
                        </div>
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
                     </div>
                     <form action="{{url('updateuserprofile')}}" method="post" enctype="multipart/form-data">
                        {{csrf_field()}}
                        <div class="row clearfix">
                           <div class="col-lg-12 col-md-6 col-sm-12 form-group">
                              <figure class="image-box" style="    margin-bottom: 15px;">
                                 @if(isset($userdata)&&$userdata->profile_pic!="")
                                 <img src="{{asset('public/upload/profile/').'/'.$userdata->profile_pic}}" alt="">
                                 @else
                                 <img src="{{asset('public/upload/profile/profile.png')}}" alt="">
                                 @endif
                              </figure>
                              <input type="file" name="image">
                           </div>
                           <div class="col-lg-12 col-md-6 col-sm-12 form-group">
                              <label>{{__('message.Name')}}</label>
                              <input type="text" name="name" placeholder="Enter first name" required="" value="{{isset($userdata->name)?$userdata->name:''}}">
                           </div>
                           <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                              <label>{{__('message.Phone no')}}</label>
                              <input type="text" name="phone" placeholder="Enter Phone" value="{{isset($userdata->phone)?$userdata->phone:''}}" required="">
                           </div>
                           <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                              <label>{{__('message.Email')}}</label>
                              <input type="email" name="email" placeholder="{{__('message.Enter Email Address')}}" required="" value="{{isset($userdata->email)?$userdata->email:''}}">
                           </div>
                        </div>
                  </div>
               </div>
               <div class="btn-box">
               <button class="theme-btn-one" type="submit">{{__('message.Save Change')}}<i class="icon-Arrow-Right"></i></button>
               <a href="{{url('changepassword')}}" class="cancel-btn">{{__('message.Cancel')}}</a>
               </div>
               </form>
            </div>
         </div>
      </div>
   </div>
</section>
@stop
@section('footer')
@stop