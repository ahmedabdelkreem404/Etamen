<head>
           <link href="{{asset('public/front_pro/assets/css/style.css?v=fgfdgf')}}" rel="stylesheet">
           <link href="{{asset('public/front_pro/assets/css/bootstrap.css')}}" rel="stylesheet">
      <link href="{{asset('public/front_pro/assets/css/jquery.fancybox.min.css')}}" rel="stylesheet">
      <link href="{{asset('public/front_pro/assets/css/animate.css')}}" rel="stylesheet">
      <link href="{{asset('public/front_pro/assets/css/color.css')}}" rel="stylesheet">
</head>

<section class="registration-section bg-color-3">
            <div class="pattern">
                <div class="pattern-1" style="background-image: url('https://healthdrfinder.co.za/doctorfinder/public/front_pro/assets/images/shape/shape-85.png');"></div>
                <div class="pattern-2" style="background-image: url('https://healthdrfinder.co.za/doctorfinder/public/front_pro/assets/images/shape/shape-86.png');"></div>
            </div>
            <div class="auto-container">
                <div class="inner-box">
                    <div class="content-box">
                        <div class="title-box">
                            <h3>Reset Password</h3>
                           
                        </div>
                        <div class="inner">
                           <form action="{{url('resetnewpwd')}}" method="post" class="registration-form">
                                 {{csrf_field()}}
                                 <input type="hidden" name="code" value="{{$code ?? ''}}" />
                                 <input type="hidden" name="id" value="{{$id ?? ''}}" />
                                 <input type="hidden" name="type" value="{{$type ?? ''}}" />
                                <div class="row clearfix">
                                    
                                    <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                        <label>Enter New Password</label>
                                        <input type="password" name="npwd" id="npwd" placeholder="Enter New Password" required="">
                                    </div>
                                     <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                        <label>Enter Re Enter New Password</label>
                                        <input type="password" name="rpwd" id="rpwd" placeholder="Enter Re Enter New Password" required="">
                                    </div>
                                   
                                    <div class="col-lg-12 col-md-12 col-sm-12 form-group message-btn">
                                        <button type="submit" class="theme-btn-one">Reset Password<i class="icon-Arrow-Right"></i></button>
                                    </div>
                                </div>
                            </form>
                                                        
                        </div>
                    </div>
                </div>
            </div>
        </section>