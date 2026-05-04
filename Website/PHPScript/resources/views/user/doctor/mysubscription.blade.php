@extends('user.layout')
@section('title')
{{__('message.My Subscription')}}
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
<style>
    .boxed label {
  display: inline-block;
  width: 200px;
  padding: 10px;
  border: solid 2px #ccc;
  transition: all 0.3s;
}

.boxed input[type="radio"] {
  display: none;
}

.boxed input[type="radio"]:checked + label {
  border: solid 2px green;
}
</style>
<section class="page-title-two">
   <div class="title-box centred bg-color-2">
      <div class="pattern-layer">
         <div class="pattern-1" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-70.png')}}');"></div>
         <div class="pattern-2" style="background-image: url('{{asset('public/front_pro/assets/images/shape/shape-71.png')}}');"></div>
      </div>
      <div class="auto-container">
         <div class="title">
            <h1>{{__('message.My Subscription')}}</h1>
         </div>
      </div>
   </div>
   <div class="lower-content">
      <ul class="bread-crumb clearfix">
         <li><a href="{{url('/')}}">{{__('message.Home')}}</a></li>
         <li>{{__('message.My Subscription')}}</li>
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
              <li><a href="{{url('doctordashboard')}}" ><i class="fas fa-columns"></i>{{__('message.Dashboard')}}</a></li>
               <li><a href="{{url('doctorappointment')}}" ><i class="fas fa-calendar-alt"></i>{{__('message.Appointment')}}</a></li>
               <li><a href="{{url('doctortiming')}}"><i class="fas fa-clock"></i>{{__('message.Schedule Timing')}}</a></li>
               <li><a href="{{url('doctorreview')}}" ><i class="fas fa-star"></i>{{__('message.Reviews')}}</a></li>
               <li><a href="{{url('doctor_hoilday')}}" ><i class="fas fa-star"></i>{{__('message.My Hoilday')}}</a></li>
               <li><a href="{{url('doctoreditprofile')}}"><i class="fas fa-user"></i>{{__('message.My Profile')}}</a></li>
{{--               <li><a href="{{url('paymenthistory')}}"><i class="fas fa-user"></i>{{__('message.Payment History')}}</a></li>--}}
{{--               <li><a href="{{url('mysubscription')}}" class="current"><i class="fas fa-rocket"></i>{{__('message.My Subscription')}}</a></li>--}}
               <li><a href="{{url('doctorchangepassword')}}" ><i class="fas fa-unlock-alt"></i>{{__('message.Change Password')}}</a></li>
               <li><a href="{{url('logout')}}"><i class="fas fa-sign-out-alt"></i>{{__("message.Logout")}}</a></li>
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
                     <h3>{{__('message.My Subscription')}}</h3>
                  </div>
                  <div class="inner-box">
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
                     <div id="registererror"></div>
                     <div class="row" style="float:right;">
                        <div class="btn-box">
                            @if($is_subscription == 0)

                                <button class="theme-btn-one subscription" type="button" onclick="remove_css()" style="margin-left: 220px;">{{__('message.Subscription')}}<i class="icon-Arrow-Right"></i></button>

                            @endif
                         </div>
                     </div>
                     <div class="row">
                        @if(isset($mysubscriptionlist))
                             <div style="display: inline-block;padding: 10px;border: solid 2px #ff9136;    transition: all 0.3s;">
                                 <span>
                                       <b>{{__('message.Amount')}} : </b>{{$currency.$mysubscriptionlist->amount}}</br>

                                     <?php
                                     $date = "";
                                          $datetime = new DateTime($mysubscriptionlist->date);
                                          if(isset($mysubscriptionlist->subscription_data)){
                                                $month = $mysubscriptionlist ->subscription_data->month;
                                                $datetime->modify('+'.$month.' month');
                                                $date = $datetime->format('Y-m-d H:i:s');
                                          }
                                     ?>
                                     </b>{{__('message.Expriy Duration')}} : </b><?=  $date ?>
                                     </br>
                                     </b>{{__('message.Payment Type')}}: </b>
                                        @if($mysubscriptionlist->payment_type==1)
                                           {{__('message.Online')}}
                                        @else
                                           {{__('message.Bank Deposit')}}
                                        @endif
                                     </br>
                                      @if($mysubscriptionlist->status=='1')
                                         <span style="padding: 5px;border-radius: 5px;" class="btn-warning">{{__('message.Not Active')}}</span>
                                     @elseif($mysubscriptionlist->status=='2')
                                         <span style="padding: 5px;border-radius: 5px;" class="btn btn-success">{{__('message.Active')}}</span>
                                     @else
                                         <span style="padding: 5px;border-radius: 5px;" class="btn btn-danger">{{__('message.Expired')}}</span>
                                     @endif
                                 </span>
                             </div>
                        @endif

                     </div>

                    </div>
               </div>


               <div class="single-box add_css" style="display:none; margin-top:20xp;">
                  <div class="title-box">
                     <h3>{{__('message.Subscription List')}}</h3>
                  </div>
                  <div class="inner-box">
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


                            <form action="{{url('updatedoctorpassword')}}" method="post" class="boxed">
                                {{csrf_field()}}

                                <div  class="row clearfix">
                                    @foreach($allsubscription as $as)
                                        <input type="radio" id="subscription{{$as->id}}" name="subscription_id" value="{{$as->id}}">
                                        <label for="subscription{{$as->id}}" style="margin-right: 15px;">{{$as->month}} {{__('message.Month')}} </br>{{$currency.$as->price}}</label>
                                    @endforeach
                                </div>

                                <div class="col-lg-7 col-md-7 col-sm-11 form-group" style="margin-left: -30px;">
                                   <div class="seldoctor">
                                    <select name="payment_method" id="payment_method" class="form-group" required onchange="changeform(this.value)">
                                        <option value="" disabled selected>{{__('message.Select Payment Type')}}</option>
                                        <option value="1">{{__('message.Braintree')}}</option>
                                        <option value="2">{{__('message.Bank Deposit')}}</option>
                                    </select>
                                 </div>
                                </div>

                               <!--<div class="btn-box">
                                   <button class="theme-btn-one" type="submit">{{__('message.Book')}}<i class="icon-Arrow-Right"></i></button>
                                   <a href="{{url('changepassword')}}" class="cancel-btn">{{__("message.Cancel")}}</a>
                               </div>-->
                            </form>

                            <div id="braintree_div" style="display:none;">
                               <form action="{{url('braintree_payment')}}" method="post" id="payment-form">
                                   {{csrf_field()}}

                                        <input type="hidden" name="payment_type" value="1">
                                        <input type="hidden" name="sub_plan" class="sub_plan">
                                        <div class="bt-drop-in-wrapper">
                                            <div id="bt-dropin"></div>
                                        </div>
                                         <input id="nonce" name="payment_method_nonce" type="hidden" />
                                             <div class="btn-box">
                                                @if(Session::has("user_id"))
                                                      <button class="theme-btn-one" type="submit">{{__('message.Book Subscription')}}<i class="icon-Arrow-Right"></i></button>
                                                @else
                                                      <button type="button" class="theme-btn-one" onclick="pleaselogin()">{{__('message.Book Subscription')}}<i class="icon-Arrow-Right"></i></button>
                                                @endif
                                             </div>
                                    </form>
                           </div>


                           <div id="stripe_div" style="display:none;">
                                <form action="{{url('deposit_payment')}}" method="post" id="stripe-form" enctype="multipart/form-data">
                                 {{csrf_field()}}
                                    <input type="hidden" name="payment_type" value="2">
                                    <input type="hidden" name="sub_plan" class="sub_plan">

                                     <div class="col-lg-7 col-md-7 col-sm-11 form-group" style="margin-left: -30px;">
                                         <div class="seldoctor">
                                            <input type="file" name="file" style="margin-top:20px;" required>
                                         </div>
                                    </div>

                                    <div class="col-lg-7 col-md-7 col-sm-11 form-group" style="margin-left: -30px;">
                                       <div class="seldoctor">
                                        <textarea name="description" placeholder="Description" required></textarea>
                                     </div>
                                    </div>

                                    <div class="btn-box">
                                        @if(Session::has("user_id"))
                                              <button class="theme-btn-one" type="submit">{{__('message.Book Subscription')}}<i class="icon-Arrow-Right"></i></button>
                                        @else
                                              <button type="button" class="theme-btn-one" onclick="pleaselogin()">{{__('message.Book Subscription')}}<i class="icon-Arrow-Right"></i></button>
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


@stop
@section('footer')
<script src="https://js.braintreegateway.com/web/dropin/1.23.0/js/dropin.min.js"></script>
<script>
    console.log('{{$token}}');
    function remove_css(){
        $(".add_css").css("display", "");
        // $(".add_css").css("margin-top", "10px");
    }

     function changeform(val){
        //  alert("Test");

        var radioValue = $("input[type=radio][name='subscription_id']:checked").val();

            if(radioValue)
            {
                $(".sub_plan").val(radioValue);
                if($("#payment_method").val()==1){
                    $("#braintree_div").css("display","block");
                    $("#stripe_div").css("display","none");
                }

                if($("#payment_method").val()==2){
                     $("#braintree_div").css("display","none");
                     $("#stripe_div").css("display","block");
                }
            }
            else
            {
                alert("Select Subscription Plan");
            }
    }

     var form = document.querySelector('#payment-form');
   var client_token = "{{$token}}";
   if(client_token!=""){
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
   }


</script>
@stop



