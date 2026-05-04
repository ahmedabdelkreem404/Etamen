@extends('user.layout')
@section('title')
{{__('message.System Name')}}
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
<section class="banner-section style-two bg-color-1">
   <div class="bg-layer" style="background-image: url('{{asset('public/image_web/').'/'.$setting->main_banner}}');"></div>
   <div class="pattern">
      <div class="pattern-1" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-32.png')}}');"></div>
      <div class="pattern-2" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-33.png')}})';"></div>
      <div class="pattern-3" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-34.png')}})';"></div>
      <div class="pattern-4" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-35.png')}}');"></div>
   </div>
   <div class="auto-container">
      <div class="row clearfix">
         <div class="col-lg-6 col-md-12 col-sm-12 content-column">
            <div class="content-box">
               <h1>{{__('message.Find A Doctor')}}</h1>
               <p>{{__('message.Amet consectetur adipisicing elit sed do eiusmod')}}</p>
               <div class="form-inner">
                  <form action="{{url('searchdoctor')}}" method="get">
                     <div class="form-group">
                        <input type="text" name="term" placeholder="{{__('message.Ex. Name')}}" required="">
                        <button type="submit"><i class="icon-Arrow-Right"></i></button>
                     </div>
                  </form>
               </div>
            </div>
         </div>
      </div>
   </div>
</section>
<section class="category-section bg-color-3 centred">
   <div class="pattern-layer">
      <div class="pattern-1" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-47.png')}}');"></div>
      <div class="pattern-2" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-48.png')}}');"></div>
   </div>
   <div class="auto-container">
      <div class="sec-title centred">
         <p>{{__('message.Category')}}</p>
         <h2>{{__('message.Browse by specialist')}}</h2>
      </div>
      <div class="row clearfix">
         @foreach($department as $d)
         <div class="col-lg-3 col-md-6 col-sm-6 col-6 category-block">
            <div class="category-block-one wow fadeInUp animated animated animated" data-wow-delay="00ms" data-wow-duration="1500ms" style="visibility: visible; animation-duration: 1500ms; animation-delay: 0ms; animation-name: fadeInUp;">
               <div class="inner-box">
                  <div class="pattern">
                     <div class="pattern-1" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-45.png')}}');"></div>
                     <div class="pattern-2" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-46.png')}}');"></div>
                  </div>
                  <figure class="icon-box"><img src="{{asset('public/upload/services').'/'.$d->icon}}"  style="height: 62px;width: 62px;" alt=""></figure>
                  <h3><a href="{{url('searchdoctor?type=').$d->id}}">{{$d->name}}</a></h3>
                  <div class="link"><a href="{{url('searchdoctor?type=').$d->id}}">
                        @if($setting->is_rtl=='1')
                          <i class="icon-Arrow-Left"></i>
                        @else
                           <i class="icon-Arrow-Right"></i>
                        @endif
                      
                      </a></div>
                  <div class="btn-box"><a href="{{url('searchdoctor?type=').$d->id}}" class="theme-btn-one">{{__('message.View List')}}<i class="icon-Arrow-Right"></i></a></div>
               </div>
            </div>
         </div>
         @endforeach                    
      </div>
      <div class="more-btn"><a href="{{url('viewspecialist')}}" class="theme-btn-one">{{__('message.All Category')}}<i class="icon-Arrow-Right"></i></a></div>
   </div>
</section>
<section class="team-style-two">
   <div class="auto-container">
      <div class="sec-title centred">
         <p>{{__('message.Meet Our Professionals')}}</p>
         <h2>{{__('message.Top Rated Specialists')}}</h2>
      </div>
      <div id="favmsg"></div>
      <div class="row clearfix">
         @foreach($doctorlist as $dl)
         <div class="col-lg-3 col-md-6 col-sm-12 team-block">
            <div class="team-block-two wow fadeInUp animated animated" data-wow-delay="00ms" data-wow-duration="1500ms">
               <div class="inner-box">
                  <div class="pattern" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-43.png')}}');"></div>
                  <figure class="image-box">
                     <img src="{{asset('public/upload/doctors').'/'.$dl->image}}" alt="" style="height: 185px;">
                     @if($dl->is_fav=='0')
                     @if(empty(Session::has("user_id")))
                     <a href="{{url('patientlogin')}}" id="favdoc{{$dl->id}}">
                     @else
                     <a href="javascript:userfavorite('{{$dl->id}}')" id="favdoc{{$dl->id}}">
                     @endif
                     @else
                     <a href="javascript:userfavorite('{{$dl->id}}')" class="activefav" id="favdoc{{$dl->id}}">
                     @endif
                     <i class="far fa-heart" ></i></a>
                  </figure>
                  <div class="lower-content">
                     <h3><a href="{{url('viewdoctor').'/'.$dl->id}}"> {{ \Illuminate\Support\Str::limit($dl->name,17, $end='..') }}</a></h3>
                     
                     <span class="designation">{{isset($dl->departmentls)?$dl->departmentls->name:""}}
                     
                   
                     </span>
                     <ul class="rating clearfix">
                        <?php
                           $arr = $dl->avgratting;
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
                        <li><a href="{{url('viewdoctor').'/'.$dl->id}}">{{$dl->totalreview}} {{__('message.reviews')}}</a></li>
                     </ul>
                     <div class="location-box">
                        <p style="height: 40px;width: 210px;"><i class="fas fa-map-marker-alt"></i>{{substr($dl->address,0,25)}}</p>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         @endforeach
      </div>
      <div class="more-btn centred"><a href="{{url('searchdoctor')}}" class="theme-btn-one">{{__('message.All Specialist')}}<i class="icon-Arrow-Right"></i></a></div>
   </div>
</section>
<section class="cta-section bg-color-2">
   <div class="pattern-layer">
      <div class="pattern-1" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-17.png')}}');"></div>
      <div class="pattern-2" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-18.png')}}');"></div>
      <div class="pattern-3" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-19.png')}}');"></div>
   </div>
   <div class="auto-container">
      <div class="row clearfix">
         <div class="col-lg-6 col-md-12 col-sm-12 image-column">
            <div class="image-box wow fadeInUp animated animated" data-wow-delay="00ms" data-wow-duration="1500ms">
               <figure class="image"><img src="{{asset('public/image_web/').'/'.$setting->app_banner}}" alt=""></figure>
            </div>
         </div>
         <div class="col-lg-6 col-md-12 col-sm-12 content-column">
            <div class="content_block_2">
               <div class="content-box">
                  <div class="sec-title light">
                     <p>{{__('message.Download apps')}}</p>
                     <h2>{{__('message.For Better Test Download Mobile App')}}</h2>
                  </div>
                  <div class="text">
                     <p>{{__('message.appdescription')}}</p>
                  </div>
                  <div class="btn-box clearfix">
                     <a href="{{$setting->app_url}}" class="download-btn app-store" target="_blank">
                        <i class="fab fa-apple"></i>
                        <span>{{__('message.Download on')}}</span>
                        <h3>{{__('message.App Store')}}</h3>
                     </a>
                     <a href="{{$setting->playstore_url}}" class="download-btn play-store" target="_blank">
                        <i class="fab fa-google-play"></i>
                        <span>{{__('message.Download on')}}</span>
                        <h3>{{__('message.Google Play')}}</h3>
                     </a>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</section>
<section class="process-style-two bg-color-3 centred">
   <div class="pattern-layer">
      <div class="pattern-1" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-39.png')}}');"></div>
      <div class="pattern-2" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-40.png')}}');"></div>
      <div class="pattern-3" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-41.png')}}');"></div>
      <div class="pattern-4" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-42.png')}}');"></div>
   </div>
   <div class="auto-container">
      <div class="sec-title centred">
         <p>{{__('message.Process')}}</p>
         <h2>{{__('message.Appointment Process')}}</h2>
      </div>
      <div class="inner-content">
         <div class="arrow" style="background-image: url('{{asset('public/front_pro/assets/images/icons/arrow-1.png')}}');"></div>
         <div class="row clearfix">
            <div class="col-lg-4 col-md-6 col-sm-12 processing-block">
               <div class="processing-block-two">
                  <div class="inner-box">
                     <figure class="icon-box"><img src="{{asset('public/image_web/').'/'.$setting->icon1}}" alt=""></figure>
                     <h3>{{__('message.Search Best Online Doctors')}}</h3>
                  </div>
               </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 processing-block">
               <div class="processing-block-two">
                  <div class="inner-box">
                     <figure class="icon-box"><img src="{{asset('public/image_web/').'/'.$setting->icon2}}" alt=""></figure>
                     <h3>{{__('message.View Doctor Profile')}}</h3>
                  </div>
               </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 processing-block">
               <div class="processing-block-two">
                  <div class="inner-box">
                     <figure class="icon-box"><img src="{{asset('public/image_web/').'/'.$setting->icon3}}" alt=""></figure>
                     <h3>{{__('message.Get Instant Doctor Appoinment')}}</h3>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</section>
<section class="agent-section" style="background: aliceblue;">
   <div class="auto-container">
      <div class="inner-container bg-color-2">
         <div class="row clearfix">
            <div class="col-lg-6 col-md-12 col-sm-12 left-column">
               <div class="content_block_3">
                  <div class="content-box">
                     <h3>{{__('message.Emergency call')}}</h3>
                     <div class="support-box">
                        <div class="icon-box"><i class="fas fa-phone"></i></div>
                        <span>{{__('message.Telephone')}}</span>
                        <h3><a href="tel:{{$setting->phone}}">{{$setting->phone}}</a></h3>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col-lg-6 col-md-12 col-sm-12 right-column">
               <div class="content_block_4">
                  <div class="content-box">
                     <h3>{{__('message.Sign up for Newsletter today')}}</h3>
                     <form action="#" method="post" class="subscribe-form">
                        <div class="form-group">
                           <input type="email" name="email" id="emailnews" placeholder="{{__('message.Your email')}}" required="">
                           <button type="button" onclick="addnewsletter()" class="theme-btn-one">{{__('message.Submit now')}}<i class="icon-Arrow-Right"></i></button>
                        </div>
                     </form>
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