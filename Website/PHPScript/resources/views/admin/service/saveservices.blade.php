@extends('admin.layout')
@section('title')
{{__("message.Save Specialities")}} | {{__("message.Admin")}}
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
                  <h4 class="mb-0">{{__("message.Save Specialities")}}</h4>
                  <div class="page-title-right">
                     <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{url('admin/services')}}">{{__("message.specialities")}}</a></li>
                        <li class="breadcrumb-item active">{{__("message.Save Specialities")}}</li>
                     </ol>
                  </div>
               </div>
            </div>
         </div>
         <div class="row" style="display: flex;justify-content: center;">
            <div class="col-6">
               <div class="card">
                  <div class="card-body">
                     <form action="{{url('admin/updateservice')}}" method="post" enctype="multipart/form-data">
                        {{csrf_field()}}  
                        <input type="hidden" name="id" value="{{$id}}"> 
                        <div class="form-group">
                           <label for="formrow-firstname-input">{{__("message.Name")}}</label>
                           <input type="text" class="form-control" id="name" name="name" placeholder='{{__("message.Enter Specialities Name")}}' value="{{isset($data)?$data->name:''}}">
                        </div>
                        <div class="form-group">
                           <label for="formrow-firstname-input">{{__("message.Icon")}}</label>
                           @if($data)
                           <img src="{{asset('public/upload/services').'/'.$data->icon}}" style="width: 150px;height: 150px" />
                           <input type="file" class="form-control" id="icon" name="icon" >
                           @else
                           <input type="file" class="form-control" required="" id="icon" name="icon" >
                           @endif
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