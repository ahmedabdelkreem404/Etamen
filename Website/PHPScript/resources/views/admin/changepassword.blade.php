@extends('admin.layout')
@section('title')
{{__("message.Change Password")}} | {{__("message.Admin")}} 
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
                  <h4 class="mb-0">{{__("message.Change Password")}}</h4>
                  <div class="page-title-right">
                     <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{__("message.Dashboard")}}</a></li>
                        <li class="breadcrumb-item active">{{__("message.Change Password")}}</li>
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
                     <form action="{{url('admin/updatepassword')}}" method="post" >
                        {{csrf_field()}}  
                        <div class="form-group">
                           <label for="formrow-firstname-input">{{__("message.Enter Your Current Password")}}</label>
                           <input type="password" class="form-control" id="currentpwd" name="currentpwd" placeholder='{{__("message.Enter Your Current Password")}}' required onchange="currentpwdnew(this.value)">
                        </div>
                        <div class="form-group">
                           <label for="formrow-firstname-input">{{__("message.Enter Your Current Password")}}</label>
                           <input type="password" class="form-control" id="newpwd" name="newpwd" placeholder='{{__("message.Enter Your Current Password")}}' value="" required>
                        </div>
                        <div class="form-group">
                           <label for="formrow-firstname-input">{{__("message.Re Enter New Password")}}</label>
                           <input type="password" class="form-control" id="repwd" name="repwd" placeholder='{{__("message.Re Enter New Password")}}' required onchange="checkmatchpassword(this.value)">
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
<script type="text/javascript">
  function checkmatchpassword(val){
    var npwd=$("#newpwd").val();
    if(npwd!=val){
        alert('{{__("message.Password And Confirm Password Must Be Same")}}');        
        $("#repwd").val("");
    }
}

function currentpwdnew(val){
   $.ajax({
                url: '{{url("admin/check_password_same")}}'+'/'+val,
                method:"get",
                success: function( data ) {
                    if(data==0){
                        alert('{{__("message.Current Password is Wrong")}}');
                        $("#currentpwd").val("");
                    }
                }
    });
}

</script>
@stop