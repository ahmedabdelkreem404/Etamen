@extends('admin.layout')
@section('title')
{{__("message.Save Subscription")}} | {{__("message.Admin")}}
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
                  <h4 class="mb-0">{{__("message.Save Subscription")}}</h4>
                  <div class="page-title-right">
                     <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{url('admin/services')}}">{{__("message.Subscription")}}</a></li>
                        <li class="breadcrumb-item active">{{__("message.Save Subscription")}}</li>
                     </ol>
                  </div>
               </div>
            </div>
         </div>
         <div class="row" style="display: flex;justify-content: center;">
            <div class="col-6">
               <div class="card">
                  <div class="card-body">
                     <form action="{{url('admin/update_subscriptio_price')}}" method="post" enctype="multipart/form-data">
                        {{csrf_field()}}  
                        <input type="hidden" name="id" value="{{$id}}"> 
                        <div class="form-group">
                           <label for="formrow-firstname-input">{{__("message.Price")}}</label>
                           <input type="number" class="form-control" id="price" name="price" placeholder='' value="{{isset($data)?$data->price:''}}">
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