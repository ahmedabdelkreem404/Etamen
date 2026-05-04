@extends('user.layout')
@section('title')
{{__('message.Schedule Timing')}}
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
            <h1>{{__('message.Schedule Timing')}}</h1>
         </div>
      </div>
   </div>
   <div class="lower-content">
      <ul class="bread-crumb clearfix">
         <li><a href="{{url('/')}}">{{__('message.Home')}}</a></li>
         <li>{{__('message.Schedule Timing')}}</li>
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
               <li><a href="{{url('doctortiming')}}" class="current"><i class="fas fa-clock"></i>{{__('message.Schedule Timing')}}</a></li>
               <li><a href="{{url('doctorreview')}}" ><i class="fas fa-star"></i>{{__('message.Reviews')}}</a></li>
               <li><a href="{{url('doctor_hoilday')}}" ><i class="fas fa-star"></i>{{__('message.My Hoilday')}}</a></li>
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
             <div class="appointment-list">
               <div class="upper-box clearfix">
                  <div class="text pull-left">
                     <h3>{{__('message.Schedule Timing')}}</h3>
                  </div>
               </div>
            </div> 
            <div class="custom-all-timing-main-box">
               <form action="{{url('updatedoctortiming')}}" method="post">
                   {{csrf_field()}}  
              <input type="hidden" name="doctor_id" value="{{Session::get('user_id')}}"/>
               <ul class="accordion-box">
                  <?php $arr=array(0=>__("message.Monday"),1=>__("message.Tuesday"),2=>__("message.Wednesday"),3=>__("message.Thursday"),4=>__("message.Friday"),5=>__("message.Saturday"),6=>__("message.Sunday"));?>
                        @for($i=0;$i<7;$i++)
                            <li class="accordion block ">
                               <div class="acc-btn">
                                   <div class="icon-outer"></div>
                                   <h4>{{$arr[$i]}}</h4>
                               </div>
                               <div class="acc-content">
                                 <input type="hidden" name="arr[]" id="day_id_{{$i}}" value="{{$i}}">
                                   <div class="btn-box">
                                    <button type="button" onclick="addnewslot('{{$i}}')" class="theme-btn-one">
                                      {{__("message.Add Time")}}
                                       <i class="far fa-plus"></i>
                                    </button>
                                 </div>
                                 <div id="day_{{$i}}">
                                    <?php $j=0;$temp=0;?>
                                     @foreach($data as $d)
                                       @if(isset($d)&&$d->day_id==$i)
                                          <div class="timing-slot-main-box" id="slotdiv{{$i}}{{$j}}">
                                             <div class="doctors-sidebar">
                                                <div class="form-widget">
                                                   <div class="form-inner">
                                                      <div class="appointment-time">

                                                     <!--   <div class="form-group">
                                                            <input type="text" name="time" placeholder="Start Time" autocomplete="off">
                                                            <i class="far fa-clock"></i>
                                                        </div>

                                                        <div class="form-group">
                                                            <input type="text" name="time" placeholder="End Time" autocomplete="off">
                                                            <i class="far fa-clock"></i>
                                                        </div>-->


                                                         <div class="form-group">
                                                            <input type="time" name="arr[{{$i}}][start_time][]" id="start_time_{{$i}}_0" placeholder='{{__("message.Start Time")}}' value="{{isset($d->start_time)?$d->start_time:''}}" onchange="checkduration('{{$i}}','{{$j}}')" >
                                                         </div>
                                                         <div class="form-group">
                                                            <input type="time" name="arr[{{$i}}][end_time][]" id="end_time_{{$i}}_0" placeholder='{{__("message.End Time")}}'value="{{isset($d->end_time)?$d->end_time:''}}" onchange="checkduration('{{$i}}','{{$j}}')">
                                                         </div> 


                                                         <div class="custom-dropdown" id="timerange" >
                                                            <select class="" name="arr[{{$i}}][duration][]" required="" id="duration_{{$i}}_{{$j}}" onchange="getslot(this.value,'{{$i}}','{{$j}}')">
                                                               <?php echo html_entity_decode($d->options);?>
                                                            </select>
                                                         </div>
                                                         @if($j!=0)
                                                          <div class="custom-btn-box btn-box" style="margin-left: 15px;">
                                                            <a href="javascript:removescdehule('{{$i}}','{{$j}}')" class="theme-btn-one">
                                                               {{__("message.delete")}}
                                                            </a>
                                                         </div>
                                                         @endif
                                                         <div class="slot-doctor-profile-main-box">
                                                            <ul id="slot_{{$i}}_{{$j}}">
                                                               <?php for($k=0;$k<count($d->getslotls);$k++){ ?>
                                                               <li>
                                                                  <label>{{$d->getslotls[$k]->slot}}</label>
                                                               </li>
                                                               <?php } ?>
                                                         </div>
                                                   </div>  
                                                </div>
                                             </div> 
                                             </div>  
                                          </div>
                                          <?php $j++;$temp=1;?>
                                       @endif
                                     @endforeach
                                       @if($temp==0)
                                           <div class="timing-slot-main-box" id="slotdiv{{$i}}0">
                                             <div class="doctors-sidebar">
                                                <div class="form-widget">
                                                   <div class="form-inner">
                                                      <div class="appointment-time">
                                                         <div class="form-group">
                                                            <input type="time" id="start_time_{{$i}}_0"  placeholder='{{__("message.Start Time")}}' name="arr[{{$i}}][start_time][0]"  onchange="checkduration('{{$i}}','0')">
                                                           
                                                         </div>
                                                         <div class="form-group">
                                                            <input type="time" id="end_time_{{$i}}_0" name="arr[{{$i}}][end_time][0]" onchange="checkduration('{{$i}}','0')" placeholder='{{__("message.End Time")}}'>
                                                           
                                                         </div>  
                                                         <div class="custom-dropdown" id="timerange" >
                                                            <select class=""  name="arr[{{$i}}][duration][0]" id="duration_{{$i}}_0" onchange="getslot(this.value,'{{$i}}',0)" >
                                                               <option value="">{{__("message.Select Duration")}}</option>      
                                                            </select>
                                                         </div>

                                                         <div class="slot-doctor-profile-main-box">
                                                            <ul id="slot_{{$i}}_0">
                                                            
                                                            </ul>
                                                         </div>
                                                   </div>  
                                                </div>
                                             </div> 
                                             </div>  
                                          </div>
                                       @endif
                                 </div>
                               </div>
                               <input type="hidden" id="total_slot_day_{{$i}}" value="{{$j+1}}"> 
                           </li>
                        @endfor   

               </ul>
                <div class="btn-box" style="margin-top: 15px">
                   <button class="theme-btn-one" type="submit">{{__("message.Submit")}}<i class="icon-Arrow-Right"></i></button>
              
               </form>
            </div>
         </div>
      </div>
   </div>
</section>
@stop
@section('footer')
@stop