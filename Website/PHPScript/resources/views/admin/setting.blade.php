@extends('admin.layout')
@section('title')
{{__("message.Setting")}} | {{__("message.Admin")}}  
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
<div class="page-content">
   <div class="container-fluid">
      <div class="row">
         <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
               <h4 class="mb-0">{{__("message.Setting")}}</h4>
               <div class="page-title-right">
                  <ol class="breadcrumb m-0">
                     <li class="breadcrumb-item active">{{__("message.Setting")}}</li>
                  </ol>
               </div>
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-lg-12">
            <div class="card">
               <div class="card-body">
                  @if(Session::has('message'))
                  <div class="col-sm-12">
                     <div class="alert  {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">{{ Session::get('message') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                     </div>
                  </div>
                  @endif
                  <h4 class="card-title mb-4">{{__("message.Setting")}}</h4>
                  <div id="vertical-nav-wizard" class="twitter-bs-wizard verti-nav-wizard">
                     <div class="row">
                        <div class="col-xl-3 col-sm-4">
                           <ul class="twitter-bs-wizard-nav nav nav-pills flex-column">
                              <li class="nav-item">
                                 <a href="#verti-nav-admin-details" class="nav-link" data-toggle="tab">
                                 <span class="step-number mr-2">01</span>
                                  Admin {{__("message.Basic Details")}}
                                 </a>
                              </li>
                               <?php if(env('IS_FORNT')=="1")
                              {
                                 ?>
                                 <li class="nav-item">
                                    <a href="#verti-nav-seller-details" class="nav-link active" data-toggle="tab">
                                    <span class="step-number mr-2">02</span>
                                    Fornt {{__("message.Basic Details")}}
                                    </a>
                                 </li>
                                 <li class="nav-item">
                                    <a href="#verti-nav-company-document" class="nav-link" data-toggle="tab">
                                    <span class="step-number mr-2">03</span>
                                    <span>{{__("message.Upload Section")}}</span>
                                    </a>
                                 </li>
                              <?php
                           }?>
                              <!--<li class="nav-item">
                                 <a href="#verti-nav-payment-keys" class="nav-link" data-toggle="tab">
                                 <span class="step-number mr-2">03</span>
                                 <span>{{__("message.Payments Keys")}}</span>
                                 </a>
                              </li>-->
                              
                           </ul>
                        </div>
                        <div class="col-xl-9 col-sm-8">
                           <div class="tab-content twitter-bs-wizard-tab-content px-sm-3 pt-sm-0">
                              <div class="tab-pane active" id="verti-nav-seller-details">
                                 <form action="{{url('admin/updatesettingone')}}" method="post" enctype="multipart/form-data">
                                    {{csrf_field()}}  
                                    <div class="row">
                                       <div class="col-lg-6">
                                          <div class="form-group">
                                             <label for="verti-nav-phoneno-input">{{__("message.Phone")}}</label>
                                             <input type="text" required name="phone" value="{{isset($data->phone)?$data->phone:''}}" class="form-control" id="verti-nav-phoneno-input">
                                          </div>
                                       </div>
                                       <div class="col-lg-6">
                                          <div class="form-group">
                                             <label for="verti-nav-email-input">Email</label>
                                             <input type="email" required="" name="email" value="{{isset($data->email)?$data->email:''}}" class="form-control" id="verti-nav-email-input">
                                          </div>
                                       </div>
                                    </div>
                                    <div class="row">
                                       <div class="col-lg-12">
                                          <div class="form-group">
                                             <label for="verti-nav-address-input">{{__("message.Address")}}</label>
                                             <textarea id="verti-nav-address-input" required name="address" id="address"  class="form-control" rows="2"> {{isset($data->address)?$data->address:''}}</textarea>
                                          </div>
                                       </div>
                                    </div>
                                   
                                    <div class="form-group">
                                       <label for="verti-nav-phoneno-input">{{__("message.App Store URL")}}</label>
                                       <input type="text" required name="app_url" value="{{isset($data->app_url)?$data->app_url:''}}" class="form-control" id="verti-nav-phoneno-input">
                                    </div>
                                    <div class="form-group">
                                       <label for="verti-nav-phoneno-input">{{__("message.Play Store URL")}}</label>
                                       <input type="text" required name="playstore_url" value="{{isset($data->playstore_url)?$data->playstore_url:''}}" class="form-control" id="verti-nav-phoneno-input">
                                    </div>
                                    
                                    <div class="mt-4">
                                       <button type="submit" class="btn btn-primary w-md">{{__("message.Submit")}}</button>
                                    </div>
                                 </form>
                              </div>
                              <div class="tab-pane" id="verti-nav-company-document">
                                 <div>
                                    <form action="{{url('admin/updatesettingtwo')}}" method="post" enctype="multipart/form-data">
                                       {{csrf_field()}}  
                                       <div class="form-group">
                                          <label for="verti-nav-pancard-input">{{__("message.Main Banner")}}</label>
                                          @if(isset($data->main_banner))
                                          <img src="{{asset('public/image_web').'/'.$data->main_banner}}" style="width: 150px;height: 150px">
                                          <input type="file" class="form-control" id="verti-nav-pancard-input"  name="main_banner">
                                          @else
                                          <input type="file" class="form-control" name="main_banner" id="verti-nav-pancard-input" required="">
                                          @endif    
                                       </div>
                                       <div class="form-group">
                                          <label for="verti-nav-vatno-input">{{__("message.Favicon")}}</label>
                                          @if(isset($data->favicon))
                                          <img src="{{asset('public/image_web').'/'.$data->favicon}}">
                                          <input type="file" class="form-control" id="verti-nav-pancard-input"  name="favicon">
                                          @else
                                          <input type="file" class="form-control" name="favicon" id="verti-nav-pancard-input" required="">
                                          @endif   
                                       </div>
                                       <div class="form-group">
                                          <label for="verti-nav-cstno-input">{{__("message.LOGO")}}</label>
                                          @if(isset($data->logo))
                                          <img src="{{asset('public/image_web').'/'.$data->logo}}" style="width: 250px;">
                                          <input type="file" class="form-control" id="verti-nav-pancard-input"  name="logo">
                                          @else
                                          <input type="file" class="form-control" name="logo" id="verti-nav-pancard-input" required="">
                                          @endif   
                                       </div>
                                       <div class="form-group">
                                          <label for="verti-nav-servicetax-input">{{__("message.App Banner")}}</label>
                                          @if(isset($data->app_banner))
                                          <img src="{{asset('public/image_web').'/'.$data->app_banner}}" style="width: 250px;">
                                          <input type="file" class="form-control" id="verti-nav-pancard-input"  name="app_banner">
                                          @else
                                          <input type="file" class="form-control" name="app_banner" id="verti-nav-pancard-input" required="">
                                          @endif   
                                       </div>
                                       <div class="form-group">
                                          <label for="verti-nav-companyuin-input">{{__("message.Appointment Process Icon 1")}}</label>
                                          @if(isset($data->icon1))
                                          <img src="{{asset('public/image_web').'/'.$data->icon1}}" style="width: 250px;">
                                          <input type="file" class="form-control" id="verti-nav-pancard-input"  name="icon1">
                                          @else
                                          <input type="file" class="form-control" name="icon1" id="verti-nav-pancard-input" required="">
                                          @endif
                                       </div>
                                       <div class="form-group">
                                          <label for="verti-nav-declaration-input">{{__("message.Appointment Process Icon 2")}}</label>
                                          @if(isset($data->icon2))
                                          <img src="{{asset('public/image_web').'/'.$data->icon2}}" style="width: 250px;">
                                          <input type="file" class="form-control" id="verti-nav-pancard-input"  name="icon2">
                                          @else
                                          <input type="file" class="form-control" name="icon2" id="verti-nav-pancard-input" required="">
                                          @endif
                                       </div>
                                       <div class="form-group">
                                          <label for="verti-nav-declaration-input">{{__("message.Appointment Process Icon 3")}}</label>
                                          @if(isset($data->icon3))
                                          <img src="{{asset('public/image_web').'/'.$data->icon3}}" style="width: 250px;">
                                          <input type="file" class="form-control" id="verti-nav-pancard-input"  name="icon3">
                                          @else
                                          <input type="file" class="form-control" name="icon3" id="verti-nav-pancard-input" required="">
                                          @endif
                                       </div>
                                       <div class="mt-4">
                                          @if(Session::get("is_demo")=='0')
                                          <button type="button" onclick="disablebtn()" class="btn btn-primary">{{__('message.Submit')}}</button>
                                          @else
                                          <button  class="btn btn-primary" type="submit" value="Submit">{{__("message.Submit")}}</button>
                                          @endif 
                                    </form>
                                    </div>
                                 </div>
                              </div>
                              <div class="tab-pane" id="verti-nav-payment-keys">
                                 <div>
                                    <form action="{{url('admin/updatesettingtwo')}}" method="post" enctype="multipart/form-data">
                                       {{csrf_field()}}  
                                       @if(Session::has('message'))
                                       <div class="col-sm-12">
                                          <div class="alert  {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">{{ Session::get('message') }}
                                             <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                             <span aria-hidden="true">&times;</span>
                                             </button>
                                          </div>
                                       </div>
                                       @endif
                                       <div id="pay-invoice">
                                          <div class="card-body">
                                    <form action="{{route('store_keys')}}" method="post">
                                    {{csrf_field()}}
                                    <div class="cmr1"> 
                                          dfds
                                    </div>
                                    </form>
                                    </div>
                                    </div>
                                    <div class="mt-4">
                                       @if(Session::get("is_demo")=='0')
                                       <button type="button" onclick="disablebtn()" class="btn btn-primary">{{__('message.Submit')}}</button>
                                       @else
                                       <button  class="btn btn-primary" type="submit" value="Submit">{{__("message.Submit")}}</button>
                                       @endif 
                                       </form>
                                    </div>
                                 </div>
                              </div>
                              <div class="tab-pane active" id="verti-nav-admin-details">
                                 <form action="{{url('admin/updatesettingfour')}}" method="post" enctype="multipart/form-data">
                                    {{csrf_field()}}  
                                    
                                    <div class="form-group">
                                       <label for="verti-nav-phoneno-input">{{__("message.Commission")}}</label>
                                       <input type="number" required name="commission" value="{{isset($data->commission)?$data->commission:''}}" class="form-control" id="verti-nav-phoneno-input" min="1" step="0.01" max="100">
                                    </div>
                                    
                                    <div class="form-group">
                                       <label for="name" class=" form-control-label">
                                       {{__('message.timezone')}}
                                       <span class="reqfield">*</span>
                                       </label>
                                       <select class="form-control" name="timezone" id="timezone" required="">
                                          <option value="">{{__('messages.select_timezone')}}</option>
                                          @foreach($timezone as $tz=>$value)
                                          <option value="{{$tz}}" <?=$data->timezone ==$tz ? ' selected="selected"' : '';?>>{{$value}}</option>
                                          @endforeach
                                       </select>
                                    </div>
                                    <div class="form-group">
                                       <label for="name" class=" form-control-label">
                                       {{__('message.currency')}}
                                       <span class="reqfield">*</span>
                                       </label>
                                       <select class="form-control" name="currency" id="currency" required="">
                                          <option value="{{$data->currency}}" selected>{{$data->currency}}</option>
                                          @include('admin.currency')
                                       </select>
                                    </div>
                                    <div class="form-group">
                                       <div class="custom-control custom-checkbox">
                                          <input type="checkbox" class="custom-control-input" id="formrow-customCheck" name="doctor_approved" value="1" <?=isset($data->doctor_approved)&&$data->doctor_approved=='1'?'checked="checked"':""?> >
                                          <label class="custom-control-label" for="formrow-customCheck">{{__("message.You Need To Approve Doctors Profile")}}</label>
                                       </div>
                                    </div>
                                    <div class="form-group">
                                       <div class="custom-control custom-checkbox">
                                          <input type="checkbox" class="custom-control-input" id="formrow-customCheck11" <?=isset($data->is_rtl)&&$data->is_rtl=='1'?'checked="checked"':""?> name="is_rtl" value="2">
                                          <label class="custom-control-label" for="formrow-customCheck11">{{__("message.Is RTL")}}</label>
                                       </div>
                                    </div>
                                    <div class="mt-4">
                                       @if(Session::get("is_demo")=='0')
                                          <button type="button" onclick="disablebtn()" class="btn btn-primary">{{__('message.Submit')}}</button>
                                       @else
                                           <button  class="btn btn-primary" type="submit" value="Submit">{{__("message.Submit")}}</button>
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
      </div>
   </div>
</div>
@stop
@section('footer')
@stop