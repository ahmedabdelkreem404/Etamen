@extends('admin.layout')
@section('title')
{{__("message.Edit Notification Key")}} | {{__("message.admin")}} 
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
                  <h4 class="mb-0">{{__("message.Edit Notification Key")}} </h4>
                  <div class="page-title-right">
                     <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item active">{{__("message.Edit Notification Key")}} </li>
                     </ol>
                  </div>
               </div>
            </div>
         </div>
         <div class="row" style="display: flex;justify-content: center;">
            <div class="col-9">
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
                     <form action="{{url('admin/updatenotificationkey')}}" method="post">
                        {{csrf_field()}}  
                        <div class="form-group">
                           <label for="formrow-firstname-input">{{__("message.Android Key")}}</label>
                           <textarea class="form-control" row="5" required name="android_key" id="android_key" placeholder='{{__("message.Enter Android Notification Key")}}' >{{$user->android_key}}
                           </textarea>
                        </div>
                        <div class="form-group">
                           <label for="formrow-firstname-input">{{__("message.Ios Key")}}</label>
                           <textarea class="form-control" row="5" required name="ios_key" id="ios_key" placeholder="Enter Ios Notification Key">{{$user->ios_key}}
                           </textarea>
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