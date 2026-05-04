@extends('admin.layout')
@section('title')
{{__("message.Add Payment")}} | {{__("message.Admin")}} 
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
                  <h4 class="mb-0">{{__("message.Add Payment")}}</h4>
                  <div class="page-title-right">
                     <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{__("message.Dashboard")}}</a></li>
                        <li class="breadcrumb-item active">{{__("message.Add Payment")}}</li>
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
                     <form action="{{url('admin/updatepayment')}}" method="post" >
                         <input type="hidden" name="doctor_id" id="doctor_id" value="{{$book->doctor_id}}"/>
                         <input type="hidden" name="amount" id="amount" value="{{$book->amount}}"/>
                        {{csrf_field()}}  
                        <div class="form-group">
                           <label for="formrow-firstname-input">{{__("message.Doctor Name")}}</label> {{$book->name}}
                        </div>
                        
                        <div class="form-group">
                           <label for="formrow-firstname-input">{{__("message.Amount")}}</label> {{$book->amount}}
                        </div>
                        
                        <div class="form-group">
                           <label for="formrow-firstname-input">{{__("message.translation_id")}}</label>
                           <input type="text" class="form-control" id="translation_id" name="translation_id" placeholder='{{__("message.translation_id")}}' required>
                        </div>
                       
                        <div class="mt-4">
                            @if(Session::get("is_demo")=='0')
                              <button type="button" onclick="disablebtn()" class="btn btn-primary">{{__('message.Submit')}}</button>
                           @else
                               <button  class="btn btn-primary" type="submit" value="Submit">{{__("message.pay")}}</button>
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