@extends('user.layout')
@section('title')
{{__('message.Favourite')}}
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
            <h1>{{__('message.Favourite')}}</h1>
         </div>
      </div>
   </div>
   <div class="lower-content">
      <ul class="bread-crumb clearfix">
         <li><a href="{{url('/')}}">{{__('message.Home')}}</a></li>
         <li>{{__('message.Favourite')}}</li>
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
               <li><a href="{{url('favouriteuser')}}" class="current"><i class="fas fa-heart"></i>{{__('message.Favourite Doctors')}}</a></li>
               <li><a href="{{url('viewschedule')}}"><i class="fas fa-clock"></i>{{__('message.Schedule Timing')}}</a></li>
               <li><a href="{{url('userreview')}}" ><i class="fas fa-comments"></i>{{__('message.Review')}}</a></li>
               <li><a href="{{url('usereditprofile')}}"><i class="fas fa-user"></i>{{__('message.My Profile')}}</a></li>
               <li><a href="{{url('changepassword')}}"><i class="fas fa-unlock-alt"></i>{{__('message.Change Password')}}</a></li>
               <li><a href="{{url('logout')}}"><i class="fas fa-sign-out-alt"></i>{{__('message.Logout')}}</a></li>
            </ul>
         </div>
      </div>
   </div>
   <div class="right-panel">
      <div class="content-container">
         <div class="outer-container">
            <div class="favourite-doctors">
               <div class="doctors-list">
                  <div class="row clearfix">
                     @if(count($userfavorite)>0)
                     @foreach($userfavorite as $uf)
                     <div class="col-xl-4 col-lg-6 col-md-12 doctors-block">
                        <div class="team-block-three">
                           <div class="inner-box">
                              <figure class="image-box">
                                 <img src="{{asset('public/upload/doctors').'/'.$uf->doctorls->image}}" alt="">
                                 <a href="javascript:userfavoritedashboard('{{$uf->doctor_id}}')" class="activefav"><i class="far fa-heart"></i></a>
                              </figure>
                              <div class="lower-content">
                                 <ul class="name-box clearfix">
                                    <li class="name">
                                       <h3><a href="{{url('viewdoctor').'/'.$uf->doctor_id}}">{{substr($uf->doctorls->name,0,10)}}</a></h3>
                                    </li>
                                    <li><i class="icon-Trust-1"></i></li>
                                    <li><i class="icon-Trust-2"></i></li>
                                 </ul>
                                 <span class="designation">{{$uf->doctorls->department_name}}</span>
                                 <div class="rating-box clearfix">
                                    <ul class="rating clearfix">
                                       <?php
                                          $arr = $uf->doctorls->avgratting;
                                          if (!empty($arr)) {
                                            $i = 0;
                                            if (isset($arr)) {
                                                for ($i = 0; $i < $arr; $i++) {
                                                    echo '<li><i class="icon-Star"></i></li>';
                                                }
                                            }
                                            
                                                $remaing = 5 - $i;
                                                for ($j = 0; $j < $remaing; $j++) {
                                                    echo '<li><i class="icon-Star" style="color: gray;"></i></li>';
                                                }
                                           
                                          }else{
                                             for ($j = 0; $j <5; $j++) {
                                                    echo '<li><i class="icon-Star" style="color: gray;"></i></li>';
                                                }
                                          }?>
                                       <li><a href="">({{$uf->doctorls->totalreview}})</a></li>
                                    </ul>
                                 </div>
                                 <div class="location-box">
                                    <p><i class="fas fa-map-marker-alt"></i>{{substr($uf->doctorls->address,0,25)}}</p>
                                 </div>
                                 <div class="lower-box clearfix">
                                    <a href="{{url('viewdoctor').'/'.$uf->doctor_id}}">{{__('message.Book Now')}}</a>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                     @endforeach
                     @else
                     {{__('message.No any doctors In your Favorite list')}}
                     @endif
                  </div>
               </div>
               {{$userfavorite->links()}}
            </div>
         </div>
      </div>
   </div>
</section>
@stop
@section('footer')
@stop