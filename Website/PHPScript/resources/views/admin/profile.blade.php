@extends('admin.layout')
@section('title')
{{__("message.Edit Profile")}} | {{__("message.admin")}}  
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
                  <h4 class="mb-0">{{__("message.Edit Profile")}}</h4>
                  <div class="page-title-right">
                     <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item active">{{__("message.Edit Profile")}}</li>
                     </ol>
                  </div>
               </div>
            </div>
         </div>
         <div class="row" style="display: flex;justify-content: center;">
            <div class="col-6">
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
                     <form action="{{url('admin/updateaccount')}}" method="post" enctype="multipart/form-data">
                        {{csrf_field()}}  
                        <div class="form-group">
                           <label for="formrow-firstname-input">{{__("message.First Name")}}</label>
                           <input type="text" class="form-control" id="first_name" name="first_name" placeholder='{{__("message.Enter Your First Name")}}' value="{{$userdata->first_name}}" required>
                        </div>
                        <div class="form-group">
                           <label for="formrow-firstname-input">{{__("message.Last Name")}}</label>
                           <input type="text" class="form-control" id="last_name" name="last_name" placeholder='{{__("message.Enter Last Name")}}' value="{{$userdata->last_name}}" required>
                        </div>
                        <div class="form-group">
                           <label for="formrow-firstname-input">{{__("message.Email")}}</label>
                           <input type="text" class="form-control" id="email" name="email" placeholder='{{__("message.Enter Email Address")}}' value="{{$userdata->email}}" readonly>
                        </div>
                        <div class="form-group">
                           <label for="formrow-firstname-input">{{__("message.Phone")}}</label>
                           <input type="text" class="form-control" id="phone" name="phone" placeholder='{{__("message.Enter Phone")}}' value="{{$userdata->phone}}" required>
                        </div>
                        <div class="form-group">
                           <label for="formrow-firstname-input">{{__("message.Image")}}</label>
                           <img src="{{asset('public/upload/profile/').'/'.$userdata->profile_pic}}" style="width: 150px;height: 150px" />
                           <input type="file" class="form-control" id="profile_pic" name="profile_pic" >
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
@stop
@section('footer')
@stop