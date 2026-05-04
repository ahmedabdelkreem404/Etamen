@extends('user.layout')
@section('title')
{{__('message.Contact Us')}}
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
            <h1>{{__('message.Contact Us')}}</h1>
         </div>
      </div>
   </div>
   <div class="lower-content">
      <div class="auto-container">
         <ul class="bread-crumb clearfix">
            <li><a href="{{url('/')}}">{{__('message.Home')}}</a></li>
            <li>{{__('message.Contact Us')}}</li>
         </ul>
      </div>
   </div>
</section>
<section class="information-section sec-pad centred bg-color-3">
   <div class="pattern-layer">
      <div class="pattern-1" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-88.png')}}');"></div>
      <div class="pattern-2" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-89.png')}}');"></div>
   </div>
   <div class="auto-container">
      <div class="sec-title centred">
         <p>{{__('message.Information')}}</p>
         <h2>{{__('message.Get In Touch')}}</h2>
      </div>
      <div class="row clearfix">
         <div class="col-lg-4 col-md-6 col-sm-12 information-column">
            <div class="single-information-block wow fadeInUp animated animated" data-wow-delay="00ms" data-wow-duration="1500ms">
               <div class="inner-box">
                  <div class="pattern" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-87.png')}}');"></div>
                  <figure class="icon-box"><img src="{{asset('public/front_pro/assets/images/icons/icon-20.png')}}" alt=""></figure>
                  <h3>{{__('message.Email')}}</h3>
                  <p>
                     <a href="mailto:{{$setting->email}}">{{$setting->email}}</a>
                  </p>
               </div>
            </div>
         </div>
         <div class="col-lg-4 col-md-6 col-sm-12 information-column">
            <div class="single-information-block wow fadeInUp animated animated" data-wow-delay="300ms" data-wow-duration="1500ms">
               <div class="inner-box">
                  <div class="pattern" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-87.png')}}');"></div>
                  <figure class="icon-box"><img src="{{asset('public/front_pro/assets/images/icons/icon-21.png')}}" alt=""></figure>
                  <h3>{{__('message.Phone Number')}}</h3>
                  <p>
                     <a href="tel:23055873407">{{$setting->phone}}</a><br />
                  </p>
               </div>
            </div>
         </div>
         <div class="col-lg-4 col-md-6 col-sm-12 information-column">
            <div class="single-information-block wow fadeInUp animated animated" data-wow-delay="600ms" data-wow-duration="1500ms">
               <div class="inner-box">
                  <div class="pattern" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-87.png')}}');"></div>
                  <figure class="icon-box"><img src="{{asset('public/front_pro/assets/images/icons/icon-22.png')}}" alt=""></figure>
                  <h3>{{__('message.Address')}}</h3>
                  <p>
                     {{$setting->address}}
                  </p>
               </div>
            </div>
         </div>
      </div>
   </div>
</section>
<section class="contact-section">
   <div class="auto-container">
      <div class="row clearfix">
         <div class="col-lg-6 col-md-12 col-sm-12 form-column">
            <div class="form-inner">
               <div class="sec-title">
                  <p>{{__('message.Contact Us')}}</p>
                  <h2>{{__('message.Contact Us')}}</h2>
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
               <form method="post" action="{{url('savecontact')}}" id="contact-form" class="default-form">
                  {{csrf_field()}}
                  <div class="row clearfix">
                     <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                        <input type="text" name="name" id="name" placeholder="{{__('message.Your name')}}" required="">
                     </div>
                     <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                        <input type="email" name="email" id="email" placeholder="{{__('message.Your email')}}" required="">
                     </div>
                     <div class="col-lg-6 col-md-12 col-sm-12 form-group">
                        <input type="text" name="phone" id="phone" required="" placeholder="{{__('message.Enter Your Phone number')}}">
                     </div>
                     <div class="col-lg-6 col-md-12 col-sm-12 form-group">
                        <input type="text" name="subject" id="subject" required="" placeholder="{{__('message.Enter Subject')}}">
                     </div>
                     <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                        <textarea name="message" id="message" placeholder="{{__('message.Enter Your Message')}}"></textarea>
                     </div>
                     <div class="col-lg-12 col-md-12 col-sm-12 form-group message-btn">
                        <button class="theme-btn-one" type="submit" name="submit-form">{{__('message.Send Message')}}<i class="icon-Arrow-Right"></i></button>
                     </div>
                  </div>
               </form>
            </div>
         </div>
         <div class="col-lg-6 col-md-12 col-sm-12 map-column">
            <div class="map-inner">
               <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d387201.09651094634!2d-74.6035408136134!3d40.69580898054919!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2sin!4v1686293784891!5m2!1sen!2sin" width="500" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
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