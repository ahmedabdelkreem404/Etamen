@extends('user.layout')
@section('title')
{{__('message.My Hoilday')}}
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
            <h1>{{__('message.My Hoilday')}}</h1>
         </div>
      </div>
   </div>
   <div class="lower-content">
      <ul class="bread-crumb clearfix">
         <li><a href="{{url('/')}}">{{__('message.Home')}}</a></li>
         <li>{{__('message.My Hoilday')}}</li>
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
               <li><a href="{{url('doctor_hoilday')}}" class="current"><i class="fas fa-star"></i>{{__('message.My Hoilday')}}</a></li>
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
            
            <div class="doctors-appointment">

               <div class="title-box">
                  <h3>{{__('message.My Hoilday List')}}</h3> 
                   <div class="btn-box col-md-6 tdr"><a href="javascript::void(0)" class="theme-btn-one" data-toggle="modal" data-target="#addaddress"><i class="icon-image" ></i>{{__("message.Add Hoilday")}}</a></div>                 
               </div>
               <div class="doctors-list">
                  <div class="table-outer">
                     <table class="doctors-table">
                        <thead class="table-header">
                           <tr>
                              <th style="text-align: center;">{{__("message.Start Date")}}</th>
                              <th style="text-align: center;">{{__("message.End Date")}}</th>
                              <th style="text-align: center;">{{__("message.description")}}</th>
                              <th style="text-align: center;">{{__("message.action")}}</th>
                           </tr>
                        </thead>
                        <tbody>
                           @if(count($doctorhoilday)>0)
                              @foreach($doctorhoilday as $dh)
                                    <tr>
                                       <td style="text-align: center;">{{$dh->start_date}}</td>
                                       <td style="text-align: center;">{{$dh->end_date}}</td>
                                       <td style="text-align: center;">{{$dh->description}}</td>
                                       <?php $delete = url('deletedoctorhoilday',array('id'=>$dh->id));?>
                                        <td style="text-align: center;"><a onclick="delete_record('{{$delete}}')" rel="tooltip" title="" class="m-b-10 m-l-5" data-original-title="Remove"><i class="fa fa-trash f-s-25"></i></a></td>
                                    </tr>
                              @endforeach
                           @else
                           <tr>
                                       <td style="text-align: center;"></td>
                                       <td style="text-align: center;"> <p>{{__("message.No Hoilday Data")}}</p></td>
                                       <td style="text-align: center;"></td>
                                       <td style="text-align: center;"></td>
                                    </tr>
                             
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
<div class="modal" id="addaddress">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <!-- Modal Header -->
         <div class="modal-header">
            <h4 class="modal-title">{{__("message.Add My Hoilday")}}</h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
         </div>
         <!-- Modal body -->
         <div class="modal-body">
            <form action="{{route('post-my-hoilday')}}" method="post" id="user_address" class="registration-form">
               {{csrf_field()}}
               <div class="row clearfix">
                 
                  <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                     <label>{{__("message.Start Date")}}</label>
                     <input type="text" name="start_date" class="dateclass" id="start_date" required="">
                  </div>
                  <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                     <label>{{__("message.End Date")}}</label>
                     <input type="text" name="end_date" id="end_date" class="dateclass" required="">
                  </div>
                  <div class="col-lg-12 col-md-6 col-sm-12 form-group">
                     <label>{{__("message.description")}}</label>
                     <textarea name="description" id="description" required></textarea>                     
                  </div>
               </div>
         </div>
         <!-- Modal footer -->
         <div class="modal-footer">
         <button type="submit" id="address_submit_button" class="btn btn-success">{{__("message.Add Hoilday")}}</button>
         <button type="button" class="btn btn-danger" data-dismiss="modal" >{{__("message.Close")}}</button>
         </div>
         </form>
      </div>
   </div>
</div>
@stop
@section('footer')
@stop