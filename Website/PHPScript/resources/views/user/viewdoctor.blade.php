@extends('user.layout')
@section('title')
{{__('message.Doctor Details')}}
@stop
@section('meta-data')
<meta property="og:type" content="website"/>
<meta property="og:url" content="{{$data->name}}"/>
<meta property="og:title" content="{{$data->name}}"/>
<meta property="og:image" content="{{asset('public/upload/doctors').'/'.$data->image}}"/>
<meta property="og:image:width" content="250px"/>
<meta property="og:image:height" content="250px"/>
<meta property="og:site_name" content="{{$data->name}}"/>
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
            <h1>{{__('message.Doctor Details')}}</h1>
         </div>
      </div>
   </div>
   <div class="lower-content">
      <div class="auto-container">
         <ul class="bread-crumb clearfix">
            <li><a href="{{url('/')}}">{{__('message.Home')}}</a></li>
            <li>{{__('message.Doctor Details')}}</li>
         </ul>
      </div>
   </div>
</section>
@if(empty($data))
{{__('message.Result Not Found')}}
@else
<section class="doctor-details bg-color-3">
   <div class="auto-container">
      <div class="row clearfix">
         <div class="col-lg-8 col-md-12 col-sm-12 content-side">
            <div class="clinic-details-content doctor-details-content">
               <div class="clinic-block-one">
                  <div class="inner-box">
                     <figure class="image-box">
                        <?php 
                           if($data->image==""){
                               $path=asset('public/upload/doctors/default.png');
                           }else{
                               $path=asset('public/upload/doctors').'/'.$data->image;
                           }
                           
                           ?>
                        <div class="doctor-detail-page-main-box" style="background-image:url('{{$path}}'); background-size: 311px 220px;" ></div>
                     </figure>
                     <div class="content-box">
                        @if($data->is_fav=='0')
                        @if(empty(Session::has("user_id")))
                        <a href="{{url('patientlogin')}}" id="favdoc{{$dl->id}}">
                        @else
                        <a href="javascript:userfavorite('{{$data->id}}')" id="favdoc{{$data->id}}">
                        @endif
                        @else
                        <a href="javascript:userfavorite('{{$data->id}}')" class="activefav" id="favdoc{{$data->id}}">
                        @endif
                        </a>
                        <div class="like-box">
                           <a href="#">
                           <i class="far fa-heart"></i>
                           </a>
                        </div>
                        <div class="middle body">
                           <div class="sm-container">
                              <i class="show-btn fas fa-share-alt"></i>
                              <div class="sm-menu">
                                 <a href="https://www.facebook.com/sharer/sharer.php?u={{url('viewdoctor').'/'.$data->id}}"><i class="fab fa-facebook-f"></i></a>
                                 <a href="https://web.whatsapp.com/send?url={{url('viewdoctor').'/'.$data->id}}"><i class="fab fa-whatsapp"></i></a>
                                 <a href="https://twitter.com/intent/tweet?text={{$data->name}}&url={{url('viewdoctor').'/'.$data->id}}"><i class="fab fa-twitter"></i></a>
                              </div>
                           </div>
                        </div>
                        <ul class="name-box clearfix">
                           <li class="name">
                              <h2>{{$data->name}}</h2>
                           </li>
                        </ul>
                        <span class="designation">{{isset($data->departmentls->name)?$data->departmentls->name:''}}</span>
                        <div class="rating-box clearfix">
                           <ul class="rating clearfix">
                              <?php
                                 $arr = $data->avgratting;
                                 if (!empty($arr)) {
                                   $i = 0;
                                   if (isset($arr)) {
                                       for ($i = 0; $i < $arr; $i++) {
                                           echo '<li><i class="icon-Star"></i></li>';
                                       }
                                   }
                                   
                                       $remaing = 5 - $i;
                                       for ($j = 0; $j < $remaing; $j++) {
                                           echo '<li class="light" style="color:gray !important"><i class="icon-Star"></i></li>';
                                       }
                                  
                                 }else{
                                 
                                    for ($j = 0; $j <5; $j++) {
                                           echo '<li class="light" style="color:gray !important"><i class="icon-Star"></i></li>';
                                       }
                                 }?>
                              <li><a href="#">({{$data->totalreview}})</a></li>
                           </ul>
                        </div>
                        <div class="text">
                           <p>{{substr($data->aboutus,0,75)}}</p>
                        </div>
                        <div class="lower-box clearfix">
                           <ul class="info clearfix">
                              <li><i class="fas fa-map-marker-alt"></i>{{substr($data->address,0,40)}}</li>
                              <li><i class="fas fa-phone"></i><a href="{{$data->phoneno}}">{{$data->phoneno}}</a></li>
                           </ul>
                           <div class="view-map"><a href="https://maps.google.com/?q={{$data->lat}},{{$data->lon}}" target="_blank">{{__('message.View Map')}}</a></div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="tabs-box">
                  <div class="tab-btn-box centred">
                     <ul class="tab-btns tab-buttons clearfix">
                        <li class="tab-btn active-btn" data-tab="#tab-1">{{__('message.About Us')}}</li>
                        <li class="tab-btn" data-tab="#tab-2">{{__('message.Services')}}</li>
                        <li class="tab-btn" data-tab="#tab-3">{{__('message.Health Care')}}</li>
                        <li class="tab-btn" data-tab="#tab-4">{{__('message.Review')}}</li>
                     </ul>
                  </div>
                  <div class="tabs-content">
                     <div class="tab active-tab" id="tab-1">
                        <div class="inner-box">
                           <div class="text">
                              <h3>{{__('message.About')}} {{$data->name}}:</h3>
                              <p>{{$data->aboutus}}</p>
                           </div>
                        </div>
                     </div>
                     <div class="tab" id="tab-2">
                        <div class="experience-box">
                           <div class="text">
                              <h3>{{__('message.Services')}}</h3>
                              <p>{{$data->services}}</p>
                           </div>
                        </div>
                     </div>
                     <div class="tab" id="tab-3">
                        <div class="location-box">
                           <h3>{{__('message.Health Care')}}</h3>
                           {{$data->healthcare}}
                        </div>
                     </div>
                     <div class="tab" id="tab-4">
                        <div class="review-box">
                           <h3>{{$data->name}} {{__('message.Review')}}</h3>
                           <div class="rating-inner">
                              <div class="rating-box">
                                 <h2>{{isset($data->avgratting)?number_format($data->avgratting,2):0}}</h2>
                                 <ul class="clearfix">
                                      
                                      
                                    <?php
                                       $arr = $data->avgratting;
                                       if (!empty($arr)) {
                                         $i = 0;
                                         if (isset($arr)) {
                                             for ($i = 0; $i < $arr; $i++) {
                                                 echo '<li><i class="icon-Star"></i></li>';
                                             }
                                         }
                                         
                                             $remaing = 5 - $i;
                                             for ($j = 0; $j < $remaing; $j++) {
                                                 echo '<li class="light" style="color:gray !important"><i class="icon-Star"></i></li>';
                                             }
                                        
                                       }else{
                                          for ($j = 0; $j <5; $j++) {
                                                 echo '<li class="light" style="color:gray !important"><i class="icon-Star"></i></li>';
                                             }
                                       }?>
                                 </ul>
                                 <span>{{__('message.Based on 5 review')}}</span>
                              </div>
                              <div class="rating-pregress">
                                 <div class="single-progress">
                                    <?php $star5=  isset($data->startrattinglines['start5'])?$data->startrattinglines['start5']:"0";
                                       $star4=  isset($data->startrattinglines['start4'])?$data->startrattinglines['start4']:"0";
                                       $star3=  isset($data->startrattinglines['start3'])?$data->startrattinglines['start3']:"0";
                                       $star2=  isset($data->startrattinglines['start2'])?$data->startrattinglines['start2']:"0";
                                       $star1=  isset($data->startrattinglines['start1'])?$data->startrattinglines['start1']:"0";
                                       ?>
                                    <style type="text/css">
                                       .doctor-details-content .tabs-box .tabs-content .review-box .rating-inner .  rating-pregress .single-progress:first-child .porgress-bar:before {
                                       width: {{$star5}}%;
                                       }
                                       .doctor-details-content .tabs-box .tabs-content .review-box .rating-inner .rating-pregress .single-progress:nth-child(2) .porgress-bar:before {
                                       width: {{$star4}}%;
                                       }
                                       .doctor-details-content .tabs-box .tabs-content .review-box .rating-inner .rating-pregress .single-progress:nth-child(3) .porgress-bar:before {
                                       width: {{$star3}}%;
                                       }
                                       .doctor-details-content .tabs-box .tabs-content .review-box .rating-inner .rating-pregress .single-progress:nth-child(4) .porgress-bar:before {
                                       width: {{$star2}}%;
                                       }
                                       .doctor-details-content .tabs-box .tabs-content .review-box .rating-inner .rating-pregress .single-progress:nth-child(5) .porgress-bar:before {
                                       width: {{$star1}}%;
                                       }
                                    </style>
                                    <span class="porgress-bar"></span>
                                    <div class="text">
                                       <p><i class="icon-Star"></i> {{__('message.5 Stars')}}</p>
                                    </div>
                                 </div>
                                 <div class="single-progress">
                                    <span class="porgress-bar"></span>
                                    <div class="text">
                                       <p><i class="icon-Star"></i>{{__('message.4 Stars')}}</p>
                                    </div>
                                 </div>
                                 <div class="single-progress">
                                    <span class="porgress-bar"></span>
                                    <div class="text">
                                       <p><i class="icon-Star"></i>{{__('message.3 Stars')}}</p>
                                    </div>
                                 </div>
                                 <div class="single-progress">
                                    <span class="porgress-bar"></span>
                                    <div class="text">
                                       <p><i class="icon-Star"></i>{{__('message.2 Stars')}}</p>
                                    </div>
                                 </div>
                                 <div class="single-progress">
                                    <span class="porgress-bar"></span>
                                    <div class="text">
                                       <p><i class="icon-Star"></i>{{__('message.1 Stars')}}</p>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="review-inner">
                              @foreach($data->reviewslist as $dr)
                              <div class="single-review-box">
                                 <figure class="image-box"><img src="{{asset('public/upload/profile/profile.png')}}" alt=""></figure>
                                 <ul class="rating clearfix">
                                    <?php
                                       $arr = $dr->rating;
                                       if (!empty($arr)) {
                                         $i = 0;
                                         if (isset($arr)) {
                                             for ($i = 0; $i < $arr; $i++) {
                                                 echo '<li><i class="icon-Star"></i></li>';
                                             }
                                         }
                                         
                                             $remaing = 5 - $i;
                                             for ($j = 0; $j < $remaing; $j++) {
                                                 echo '<li class="light"><i class="icon-Star"></i></li>';
                                             }
                                        
                                       }else{
                                          for ($j = 0; $j <5; $j++) {
                                                 echo '<li class="light"><i class="icon-Star"></i></li>';
                                             }
                                       }?>                       
                                 </ul>
                                 <h6>{{$dr->patientls->name}}<span>- 
                                    <?php 
                                       ?>{{date("F d, Y",strtotime($dr->created_at))}}</span>
                                 </h6>
                                 <p>{{$dr->description}}</p>
                              </div>
                              @endforeach
                           </div>
                           <div class="btn-box">
                              <a href="#" class="theme-btn-one">{{__('message.Submit Review')}}<i class="icon-Arrow-Right"></i></a>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-lg-4 col-md-12 col-sm-12 sidebar-side">
            <div class="doctors-sidebar">
               <div class="form-widget">
                  <div class="form-title">
                     <h3>{{__('message.Book Appointment')}}</h3>
                     <p>{{__('message.Monday to Sunday')}}: {{$data->working_time}}</p>
                  </div>
                
                     <div class="form-inner">
                        <div class="appointment-time">
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
                           @if ($errors->any())
                           <div class="alert alert-danger">
                              <ul>
                                 @foreach ($errors->all() as $error)
                                 <li>{{ $error }}</li>
                                 @endforeach
                              </ul>
                           </div>
                           @endif
                           <?php $currency = explode("-", $setting->currency);?>
                           <p>{{__('message.consultation_fees')}} :  {{$currency[1]}}<span id="consultation_fees">{{$data->consultation_fees}}</span></p>
                           <input type="hidden" name="doctor_id" id="doctor_id" value="{{$data->id}}">
                           <input type="hidden" name="consultation_fees" value="{{$data->consultation_fees}}">
                           <div class="form-group">
                              <input type="text" name="date"  id="date" value="{{date('m/d/Y')}}" id="datepicker" onchange="slotdivchange(this.value)">
                              <i class="fas fa-calendar-alt"></i>
                           </div>
                           @if(!empty($schedule))
                           <div class="custom-dropdown" id="timerange" style="    width: 100%;margin-bottom: 10px;" >
                              <select class="" name="slottime" id="slottime" required="" onchange="slotchange(this.value)">
                                 @foreach($schedule as $s)
                                 <option value="{{$s['id']}}" data-display-text="Fruits">{{$s['title']}}</option>
                                 @endforeach
                              </select>
                           </div>
                           @endif
                           <div class="custom-slot-design-box">
                              <ul id="slotdiv">
                                 @if(empty($getdoctorhoilday))
                                    @if(!empty($schedule))
                                    @foreach($schedule as $s)
                                    @foreach($s['slottime'] as $ns)
                                    <li>
                                       @if($ns['is_book']==0)
                                       <input type='radio' value='{{$ns["id"]."#".$ns["name"]}}' name='slot' id='{{$ns["id"]}}'/>
                                       <label for='{{$ns["id"]}}'>{{$ns["name"]}}</label>
                                       @else
                                       <input type='radio' value='{{$ns["id"]."#".$ns["name"]}}' name='slot' id='{{$ns["id"]}}' disabled/>
                                       <label class="custom-radio-disabled" for='radio4'>{{$ns["name"]}}</label>
                                       @endif
                                    </li>
                                    @endforeach
                                    @break;
                                    @endforeach
                                    @endif
                                 @else
                                    <p style="color: red;margin-left: 10px;">{{__("message.Doctor isnot Avilable")}}</p>
                                 @endif
                              </ul>
                           </div>
                        </div>
                        <div class="choose-service">
                           <h4>{{__('message.Enter Information')}}</h4>
                           <div class="form-group">
                              <label>{{__('message.Phone no')}}</label>
                              <input type="text" name="phone_no" id="phone_no" placeholder="{{__('message.Enter Your Phone number')}}" required="">
                           </div>
                           <div class="form-group">
                              <label>{{__('message.Message')}}</label>
                              <textarea id="message" rows="15"  name="message" placeholder="{{__('message.Enter Your Message')}}"></textarea>
                           </div>
                            <div class="row">
                                <div class="col-md-6">
                                   <div class="custom-check-box">
                                      <div class="custom-controls-stacked">
                                         <label class="custom-control material-checkbox">
                                         <input type="radio" class="material-control-input" name="payment_type" id="payment_type_cod" value="3" onchange="changeform(this.value)">
                                         <span class="material-control-indicator"></span>
                                         <span class="description">COD</span>
                                         </label>
                                      </div>
                                   </div>
                                </div>
                                <div class="col-md-6">   
                                    <div class="custom-check-box">
                                        <div class="custom-controls-stacked">
                                             <label class="custom-control material-checkbox">
                                             <input type="radio" class="material-control-input"  name="payment_type" id="payment_type_stripe" value="2" onchange="changeform(this.value)">
                                             <span class="material-control-indicator"></span>
                                             <span class="description">Stripe</span>
                                             </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="custom-check-box">
                                      <div class="custom-controls-stacked">
                                         <label class="custom-control material-checkbox">
                                         <input type="radio" class="material-control-input"  name="payment_type" id="payment_type_rave" value="4" onchange="changeform(this.value)">
                                         <span class="material-control-indicator"></span>
                                         <span class="description">Rave</span>
                                         </label>
                                      </div>
                                    </div>
                                </div>
                                <div class="col-md-6">   
                                    <div class="custom-check-box">
                                      <div class="custom-controls-stacked">
                                         <label class="custom-control material-checkbox">
                                         <input type="radio" class="material-control-input"  name="payment_type" id="payment_type_paytm" value="5" onchange="changeform(this.value)">
                                         <span class="material-control-indicator"></span>
                                         <span class="description">Paytm</span>
                                         </label>
                                      </div>
                                   </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="custom-check-box">
                                        <div class="custom-controls-stacked">
                                             <label class="custom-control material-checkbox">
                                             <input type="radio" class="material-control-input" name="payment_type" id="payment_type_braintree" value="1" onchange="changeform(this.value)">
                                             <span class="material-control-indicator"></span>
                                             <span class="description">Braintree</span>
                                             </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">   
                                    <div class="custom-check-box">
                                        <div class="custom-controls-stacked">
                                             <label class="custom-control material-checkbox">
                                             <input type="radio" class="material-control-input"  name="payment_type" id="payment_type_razorpay" value="6" onchange="changeform(this.value)">
                                             <span class="material-control-indicator"></span>
                                             <span class="description">Razorpay</span>
                                             </label>
                                        </div>
                                   </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="custom-check-box">
                                        <div class="custom-controls-stacked">
                                             <label class="custom-control material-checkbox">
                                             <input type="radio" class="material-control-input" name="payment_type" id="payment_type_paystack" value="7" onchange="changeform(this.value)">
                                             <span class="material-control-indicator"></span>
                                             <span class="description">Paystack</span>
                                             </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                
                            
                            <div class="text-center">
                            @if(Session::has("user_id"))
                                  <button class="theme-btn-one centred" type="button" id="show_book" onclick="bookshow()" >{{__('message.Book Appointment')}}<i class="icon-Arrow-Right"></i></button>
                            @else
                                  <button type="button" class="theme-btn-one" onclick="pleaselogin()"  id="show_book">{{__('message.Book Appointment')}}<i class="icon-Arrow-Right"></i></button>
                            @endif
                            </div>
                            <div id="braintree_div" style="display:none;">
                                <form action="{{url('makeappointment')}}" method="post" id="payment-form">
                                   {{csrf_field()}}
                                    <input type="hidden" name="doctor_id" id="doctor_id" value="{{$data->id}}">
                                    <input type="hidden" name="consultation_fees" value="{{$data->consultation_fees}}">
                                    <input type="hidden" name="phone_no" id="phone_no_1">
                                    <input type="hidden" name="date" id="date_1">
                                    <input type="hidden" name="slot" id="slot_1">
                                    <input type="hidden" name="message" id="message_1">
                                    <input type="hidden" name="payment_type" value="braintree">
                                    <div class="bt-drop-in-wrapper">
                                        <div id="bt-dropin"></div>
                                    </div>
                                    <input id="nonce" name="payment_method_nonce" type="hidden" />
                                    <div class="btn-box" id="btnappointment">
                                        @if(Session::has("user_id"))
                                              <button class="theme-btn-one" type="submit">{{__('message.Book Appointment')}}<i class="icon-Arrow-Right"></i></button>
                                        @else
                                              <button type="button" class="theme-btn-one" onclick="pleaselogin()">{{__('message.Book Appointment')}}<i class="icon-Arrow-Right"></i></button>
                                        @endif
                                    </div>
                                </form> 
                            </div>

                            <div id="stripe_div" style="display:none;">
                                <form action="{{url('makeappointment')}}" method="post" id="stripe-form">
                                    {{csrf_field()}}
                                    <input type="hidden" name="phone_no" id="phone_no_2">
                                    <input type="hidden" name="date" id="date_2">
                                    <input type="hidden" name="slot" id="slot_2">
                                    <input type="hidden" name="message" id="message_2">
                                    <input type="hidden" name="payment_type" value="stripe">
                                    <input type="hidden" name="doctor_id" id="doctor_id" value="{{$data->id}}">
                                    <input type="hidden" name="consultation_fees" value="{{$data->consultation_fees}}">
                                    <script
                                      src="https://checkout.stripe.com/checkout.js" class="stripe-button"
                                      data-key="{{env('STRIPE_KEY')}}"
                                      data-amount=""
                                      data-id="stripid"
                                      data-name="{{__('message.System Name')}}"
                                      data-label="{{__('message.Book Appointment')}}"
                                      data-description=""
                                      data-image="{{asset('public/image_web/').'/'.$setting->logo}}"
                                      data-locale="auto">
                                    </script>
                                </form> 
                            </div>  
                            
                            <div id="cod_div" style="display:none;">
                                <form action="{{url('makeappointment')}}" method="post" id="payment-form">
                                   {{csrf_field()}}
                                    <input type="hidden" name="doctor_id" id="doctor_id" value="{{$data->id}}">
                                    <input type="hidden" name="consultation_fees" value="{{$data->consultation_fees}}">
                                    <input type="hidden" name="phone_no" id="phone_no_3">
                                    <input type="hidden" name="date" id="date_3">
                                    <input type="hidden" name="slot" id="slot_3">
                                    <input type="hidden" name="message" id="message_3">
                                    <input type="hidden" name="payment_type" value="cod">
                                    <div class="bt-drop-in-wrapper">
                                        <div id="bt-dropin"></div>
                                    </div>
                                    <input id="nonce" name="payment_method_nonce" type="hidden" />
                                    <div class="btn-box" id="btnappointment">
                                        @if(Session::has("user_id"))
                                              <button class="theme-btn-one" type="submit">{{__('message.Book Appointment')}}<i class="icon-Arrow-Right"></i></button>
                                        @else
                                              <button type="button" class="theme-btn-one" onclick="pleaselogin()">{{__('message.Book Appointment')}}<i class="icon-Arrow-Right"></i></button>
                                        @endif
                                    </div>
                                </form> 
                            </div>
                            
                            <div id="rave_div" style="display:none;">
                                <form action="{{url('makeappointment')}}" method="post" id="payment-form">
                                   {{csrf_field()}}
                                    <input type="hidden" name="doctor_id" id="doctor_id" value="{{$data->id}}">
                                    <input type="hidden" name="consultation_fees" value="{{$data->consultation_fees}}">
                                    <input type="hidden" name="phone_no" id="phone_no_4">
                                    <input type="hidden" name="date" id="date_4">
                                    <input type="hidden" name="slot" id="slot_4">
                                    <input type="hidden" name="message" id="message_4">
                                    <input type="hidden" name="payment_type" value="Flutterwave">
                                    <div class="bt-drop-in-wrapper">
                                        <div id="bt-dropin"></div>
                                    </div>
                                    <input id="nonce" name="payment_method_nonce" type="hidden" />
                                    <div class="btn-box" id="btnappointment">
                                        @if(Session::has("user_id"))
                                              <button class="theme-btn-one" type="submit">{{__('message.Book Appointment')}}<i class="icon-Arrow-Right"></i></button>
                                        @else
                                              <button type="button" class="theme-btn-one" onclick="pleaselogin()">{{__('message.Book Appointment')}}<i class="icon-Arrow-Right"></i></button>
                                        @endif
                                    </div>
                                </form> 
                            </div>
                            
                            <div id="paytm_div" style="display:none;">
                                <form action="{{url('makeappointment')}}" method="post" id="payment-form">
                                   {{csrf_field()}}
                                    <input type="hidden" name="doctor_id" id="doctor_id" value="{{$data->id}}">
                                    <input type="hidden" name="consultation_fees" value="{{$data->consultation_fees}}">
                                    <input type="hidden" name="phone_no" id="phone_no_5">
                                    <input type="hidden" name="date" id="date_5">
                                    <input type="hidden" name="slot" id="slot_5">
                                    <input type="hidden" name="message" id="message_5">
                                    <input type="hidden" name="payment_type" value="Paytm">
                                    <div class="bt-drop-in-wrapper">
                                        <div id="bt-dropin"></div>
                                    </div>
                                    <input id="nonce" name="payment_method_nonce" type="hidden" />
                                    <div class="btn-box" id="btnappointment">
                                        @if(Session::has("user_id"))
                                              <button class="theme-btn-one" type="submit">{{__('message.Book Appointment')}}<i class="icon-Arrow-Right"></i></button>
                                        @else
                                              <button type="button" class="theme-btn-one" onclick="pleaselogin()">{{__('message.Book Appointment')}}<i class="icon-Arrow-Right"></i></button>
                                        @endif
                                    </div>
                                </form> 
                            </div>
                            
                            <div id="razorpay_div" style="display:none;">
                                <form action="{{url('makeappointment')}}" method="post" id="payment-form">
                                   {{csrf_field()}}
                                    <input type="hidden" name="doctor_id" id="doctor_id" value="{{$data->id}}">
                                    <input type="hidden" name="consultation_fees" value="{{$data->consultation_fees}}">
                                    <input type="hidden" name="phone_no" id="phone_no_6">
                                    <input type="hidden" name="date" id="date_6">
                                    <input type="hidden" name="slot" id="slot_6">
                                    <input type="hidden" name="message" id="message_6">
                                    <input type="hidden" name="type"  value="1">
                                    
                                    <input type="hidden" name="payment_type" value="Razorpay">
                                    <div class="bt-drop-in-wrapper">
                                        <div id="bt-dropin"></div>
                                    </div>
                                    <input id="nonce" name="payment_method_nonce" type="hidden" />
                                    @if(Session::has("user_id"))
                                        <script src="https://checkout.razorpay.com/v1/checkout.js"
                                            data-key="{{$paymentdetail['razorpay_razorpay_key']}}"
                                            data-amount="{{(int)$data->consultation_fees*100}}"
                                            data-buttontext='{{__("message.Pay")}}'
                                            data-name="{{env('APP_NAME')}}"
                                            data-description="Payment"
                                            data-image="{{asset('public/image_web/896814.png')}}"
                                            data-prefill.name="name"
                                            data-prefill.email="email"
                                            data-theme.color="#d18217">
                                        </script>
                                    @else
                                          <button type="button" class="theme-btn-one" onclick="pleaselogin()">{{__('message.Book Appointment')}}<i class="icon-Arrow-Right"></i></button>
                                    @endif
                                </form> 
                            </div>
                            
                            <div id="paystack_div" style="display:none;">
                                <form action="{{url('makeappointment')}}" method="post" id="payment-form">
                                   {{csrf_field()}}
                                    <input type="hidden" name="doctor_id" id="doctor_id" value="{{$data->id}}">
                                    <input type="hidden" name="consultation_fees" value="{{$data->consultation_fees}}">
                                    <input type="hidden" name="phone_no" id="phone_no_7">
                                    <input type="hidden" name="date" id="date_7">
                                    <input type="hidden" name="slot" id="slot_7">
                                    <input type="hidden" name="message" id="message_7">
                                    <input type="hidden" name="payment_type" value="Paystack">
                                    <div class="bt-drop-in-wrapper">
                                        <div id="bt-dropin"></div>
                                    </div>
                                    <input id="nonce" name="payment_method_nonce" type="hidden" />
                                    <div class="btn-box" id="btnappointment">
                                        @if(Session::has("user_id"))
                                              <button class="theme-btn-one" type="submit">{{__('message.Book Appointment')}}<i class="icon-Arrow-Right"></i></button>
                                        @else
                                              <button type="button" class="theme-btn-one" onclick="pleaselogin()">{{__('message.Book Appointment')}}<i class="icon-Arrow-Right"></i></button>
                                        @endif
                                    </div>
                                </form> 
                            </div>
                            
                        </div>
                     </div>               
               </div>
            </div>
         </div>
      </div>
   </div>
</section>
@endif
@stop
@section('footer')

<script src="https://js.braintreegateway.com/web/dropin/1.23.0/js/dropin.min.js"></script>
<script type="text/javascript">
   document.querySelector('.show-btn').addEventListener('click', function() {
     document.querySelector('.sm-menu').classList.toggle('active');
   });
</script>
<script>

   function changeform(val){
     
      var phone_no = $("#phone_no").val();
      var date = $("#date").val();
      var message = $("#message").val();
      var slot = $('input[name="slot"]:checked').val();
      if(phone_no!=""&&date!=""&&message!=""&&slot!=""){
            if($("#payment_type_braintree").prop("checked")==true){
                 $("#braintree_div").css("display","block");
                 $("#cod_div").css("display","none");
                 $("#stripe_div").css("display","none");
                 $("#razorpay_div").css("display","none");
                 $("#paytm_div").css("display","none");
                 $("#rave_div").css("display","none");
                 $("#show_book").css("display","none");
                 $("#paystack_div").css("display","none");
                 $("#phone_no_1").val(phone_no);
                 $("#date_1").val(date);
                 $("#message_1").val(message);
                 $("#slot_1").val(slot);
            }
            if($("#payment_type_stripe").prop("checked")==true){
                 $("#stripe_div").css("display","block");
                 $("#braintree_div").css("display","none");
                 $("#cod_div").css("display","none");
                 $("#razorpay_div").css("display","none");
                 $("#paytm_div").css("display","none");
                 $("#rave_div").css("display","none");
                 $("#show_book").css("display","none");
                 $("#paystack_div").css("display","none");
                 $("#phone_no_2").val(phone_no);
                 $("#date_2").val(date);
                 $("#message_2").val(message);
                 $("#slot_2").val(slot);
            }
            if($("#payment_type_cod").prop("checked")==true){
                 $("#cod_div").css("display","block");
                 $("#braintree_div").css("display","none");
                 $("#stripe_div").css("display","none");
                 $("#razorpay_div").css("display","none");
                 $("#paytm_div").css("display","none");
                 $("#rave_div").css("display","none");
                 $("#show_book").css("display","none");
                 $("#paystack_div").css("display","none");
                 $("#phone_no_3").val(phone_no);
                 $("#date_3").val(date);
                 $("#message_3").val(message);
                 $("#slot_3").val(slot);
            }
            if($("#payment_type_rave").prop("checked")==true){
                 $("#rave_div").css("display","block");
                 $("#braintree_div").css("display","none");
                 $("#stripe_div").css("display","none");
                 $("#cod_div").css("display","none");
                 $("#razorpay_div").css("display","none");
                 $("#paytm_div").css("display","none");
                 $("#show_book").css("display","none");
                 $("#paystack_div").css("display","none");
                 $("#phone_no_4").val(phone_no);
                 $("#date_4").val(date);
                 $("#message_4").val(message);
                 $("#slot_4").val(slot);
            }
            if($("#payment_type_paytm").prop("checked")==true){
                 $("#paytm_div").css("display","block");
                 $("#braintree_div").css("display","none");
                 $("#stripe_div").css("display","none");
                 $("#cod_div").css("display","none");
                 $("#razorpay_div").css("display","none");
                 $("#rave_div").css("display","none");
                 $("#show_book").css("display","none");
                 $("#paystack_div").css("display","none");
                 $("#phone_no_5").val(phone_no);
                 $("#date_5").val(date);
                 $("#message_5").val(message);
                 $("#slot_5").val(slot);
            }
            if($("#payment_type_razorpay").prop("checked")==true){
                 $("#razorpay_div").css("display","block");
                 $("#braintree_div").css("display","none");
                 $("#stripe_div").css("display","none");
                 $("#cod_div").css("display","none");
                 $("#paytm_div").css("display","none");
                 $("#rave_div").css("display","none");
                 $("#show_book").css("display","none");
                 $("#paystack_div").css("display","none");
                 $("#phone_no_6").val(phone_no);
                 $("#date_6").val(date);
                 $("#message_6").val(message);
                 $("#slot_6").val(slot);
            }
            if($("#payment_type_paystack").prop("checked")==true){
                 $("#razorpay_div").css("display","none");
                 $("#braintree_div").css("display","none");
                 $("#stripe_div").css("display","none");
                 $("#cod_div").css("display","none");
                 $("#paytm_div").css("display","none");
                 $("#rave_div").css("display","none");
                 $("#show_book").css("display","none");
                 $("#paystack_div").css("display","block");
                 $("#phone_no_7").val(phone_no);
                 $("#date_7").val(date);
                 $("#message_7").val(message);
                 $("#slot_7").val(slot);
            }
            
            
      }else{
            alert("Please Fillup All Field");
      }
      

    }
   
    function bookshow(){
        var phone_no = $("#phone_no").val();
        var date = $("#date").val();
        var message = $("#message").val();
        var slot = $('input[name="slot"]:checked').val();
        if(phone_no!=""&&date!=""&&message!=""&&slot!=""){
            alert("please choose payment type");
        }
        else{
            alert("Please Fillup All Field");
        }
    }
    


   var form = document.querySelector('#payment-form');
   var client_token = "{{$token}}";
   
   braintree.dropin.create({
     authorization: client_token,
     selector: '#bt-dropin',
     paypal: {
       flow: 'vault'
     }
   }, function (createErr, instance) {
     if (createErr) {
       console.log('Create Error', createErr);
       return;
     }
     form.addEventListener('submit', function (event) {
       event.preventDefault();
   
       instance.requestPaymentMethod(function (err, payload) {
         if (err) {
           console.log('Request Payment Method Error', err);
           return;
         }
        
        
         document.querySelector('#nonce').value = payload.nonce;
         form.submit();
       });
     });
   });
</script>

@stop