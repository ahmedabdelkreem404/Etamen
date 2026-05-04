@extends('user.layout')
@section('title')
{{__('message.Specialist')}}
@stop
@section('meta-data')
<meta property="og:type" content="website"/>
<meta property="og:url" content="{{__('message.Specialist')}}"/>
<meta property="og:title" content="{{__('message.Specialist')}}"/>
<meta property="og:image" content="{{asset('public/image_web/').'/'.$setting->favicon}}"/>
<meta property="og:image:width" content="250px"/>
<meta property="og:image:height" content="250px"/>
<meta property="og:site_name" content="{{__('message.Specialist')}}"/>
<meta property="og:description" content="{{__('message.meta_description')}}"/>
<meta property="og:keyword" content="{{__('message.Meta Keyword')}}"/>
<link rel="shortcut icon" href="{{asset('public/image_web/').'/'.$setting->favicon}}">
<meta name="viewport" content="width=device-width, initial-scale=1">
@stop
@section('content')
<section class="page-title centred bg-color-1">
   <div class="pattern-layer">
      <div class="pattern-1" style="background-image: url(assets/images/shape/shape-70.png);"></div>
      <div class="pattern-2" style="background-image: url(assets/images/shape/shape-71.png);"></div>
   </div>
   <div class="auto-container">
      <div class="content-box">
         <div class="title">
            <h1>{{__('message.Specialist')}}</h1>
         </div>
         <ul class="bread-crumb clearfix">
            <li><a href="{{url('/')}}">{{__('message.Home')}}</a></li>
            <li>{{__('message.Specialist')}}</li>
         </ul>
      </div>
   </div>
</section>

<section class="category-viewspecialistp-section category-section bg-color-3 centred">
   <div class="pattern-layer">
      <div class="pattern-1" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-47.png')}}');"></div>
      <div class="pattern-2" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-48.png')}}');"></div>
   </div>
   <div class="auto-container">
      <div class="sec-title centred">
         <h2></h2>
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
                  <div class="link"><a href="{{url('searchdoctor?type=').$d->id}}"><i class="icon-Arrow-Right"></i></a></div>
                  <div class="btn-box"><a href="{{url('searchdoctor?type=').$d->id}}" class="theme-btn-one">{{__('message.View List')}}<i class="icon-Arrow-Right"></i></a></div>
               </div>
            </div>
         </div>
         @endforeach                    
      </div>
   </div>
</section>

@stop
@section('footer')



   <script type="text/javascript">
      document.querySelector('.show-btn').addEventListener('click', function() {
        document.querySelector('.sm-menu').classList.toggle('active');
      });
   </script>



@stop