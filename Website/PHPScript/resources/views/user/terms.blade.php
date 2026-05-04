@extends('user.layout')
@section('title')
{{__('message.Privecy')}}
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
  <section class="page-title centred bg-color-1">
            <div class="pattern-layer">
                <div class="pattern-1" style="background-image: url(assets/images/shape/shape-70.png);"></div>
                <div class="pattern-2" style="background-image: url(assets/images/shape/shape-71.png);"></div>
            </div>
            <div class="auto-container">
                <div class="content-box">
                    <div class="title">
                        <h1>{{__('message.Privecy')}}</h1>
                    </div>
                    <ul class="bread-crumb clearfix">
                        <li><a href="{{url('/')}}">{{__('message.Home')}}</a></li>
                        <li>{{__('message.Privecy')}}</li>
                    </ul>
                </div>
            </div>
        </section>
        <section class="about-style-two">
            <div class="auto-container">
                <div class="row align-items-center clearfix">
                    <div class="col-lg-12 col-md-12 col-sm-12 content-column">
                        <div class="content_block_1">
                            <div class="content-box mr-50">
                                <div class="sec-title">
                                    <p>{{__('message.Privecy')}}</p>
                                 </div>  
                                
                                
                                 {!! $data->trems !!}
                              
                            </div>
                        </div>
                    </div>
                   
                </div>
            </div>
        </section>
       
      
        <section class="faq-section pt-125">
            <div class="auto-container">
                <div class="row clearfix">
                    <div class="col-lg-6 col-md-12 col-sm-12 image-column">
                        <div class="image_block_4">
                            <div class="image-box">
                                <div class="pattern" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-54.png')}}');"></div>
                                <figure class="image"><img src="{{asset('public/front_pro/assets/images/resource/faq-1.png')}}" alt=""></figure>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12 col-sm-12 content-column">
                        <div class="content_block_5">
                            <div class="content-box">
                                <div class="sec-title">
                                    <p>Faq’s</p>
                                    <h2>Frequently Asked Questions.</h2>
                                </div>
                                <ul class="accordion-box">
                                    <li class="accordion block">
                                        <div class="acc-btn">
                                            <div class="icon-outer"></div>
                                            <h4>How do I contact customer service?</h4>
                                        </div>
                                        <div class="acc-content">
                                            <div class="text">
                                                <p>Lorem ipsum dolor sit amet consectur adipiscing elit eiusmod tempor incididunt labore dolore magna aliquaenim ad minim veniam quis nostrud exercitation ullamco laboris.</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="accordion block active-block">
                                        <div class="acc-btn active">
                                            <div class="icon-outer"></div>
                                            <h4>Do doctors pay for good reviews?</h4>
                                        </div>
                                        <div class="acc-content current">
                                            <div class="text">
                                                <p>Lorem ipsum dolor sit amet consectur adipiscing elit eiusmod tempor incididunt labore dolore magna aliquaenim ad minim veniam quis nostrud exercitation ullamco laboris.</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="accordion block">
                                         <div class="acc-btn">
                                            <div class="icon-outer"></div>
                                            <h4>Why didn't my review get posted?</h4>
                                        </div>
                                        <div class="acc-content">
                                            <div class="text">
                                                <p>Lorem ipsum dolor sit amet consectur adipiscing elit eiusmod tempor incididunt labore dolore magna aliquaenim ad minim veniam quis nostrud exercitation ullamco laboris.</p>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
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