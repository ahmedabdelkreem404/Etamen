@extends('admin.layout')
@section('title')
{{__("message.Add Notification")}} | {{__("message.Admin")}} 
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
                  <h4 class="mb-0">{{__("message.Add Notification")}}</h4>
                  <div class="page-title-right">
                     <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{url('admin/sendnotification')}}">{{__("message.Notification")}}</a></li>
                        <li class="breadcrumb-item active">{{__("message.Add Notification")}}</li>
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
                     <form action="{{url('admin/sendnotificationtouser')}}" method="post">
                        {{csrf_field()}}  
                        <div class="form-group">
                           <label for="formrow-firstname-input">{{__("message.Messages")}}</label>
                           <textarea class="form-control" row="10" cols="15" required name="message" id="message" placeholder='{{__("message.Enter You Notification Messages")}}' >
                           </textarea>
                        </div>
                        <div class="mt-4">
                          
                               <button  class="btn btn-primary" type="submit" value="Submit">{{__("message.Submit")}}</button>
                         
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