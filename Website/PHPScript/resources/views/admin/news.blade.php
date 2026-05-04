@extends('admin.layout')
@section('title')
{{__("message.News")}} | {{__("message.Admin")}} 
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
                  <h4 class="mb-0">{{__("message.News")}}</h4>
                  <div class="page-title-right">
                     <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{__("message.Dashboard")}}</a></li>
                        <li class="breadcrumb-item active">{{__("message.News")}}</li>
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
                     <form action="{{url('admin/sendnews')}}" method="post" >
                        {{csrf_field()}}  
                        <div class="form-group">
                           <label for="formrow-firstname-input">{{__("message.News Content")}}</label>
                           <textarea id="content" name="content" required class="form-control" ></textarea>
                        </div>
                        <p> <span style="color:red">*</span> You can send email to news users who have subscribed news on website</p>
                        
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
<script>
    CKEDITOR.replace('content');
</script>
@stop