@extends('admin.layout')
@section('title')
{{__("message.Subscription List")}}
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
                  <h4 class="mb-0">{{__("message.Subscription List")}}</h4>
                  <div class="page-title-right">
                     <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item active">{{__("message.Subscription List")}}</li>
                     </ol>
                  </div>
               </div>
            </div>
         </div>
         <div class="row">
            <div class="col-12">
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
                     <h4 class="card-title">{{__("message.Subscription List")}} </h4>
                     
                     <table id="subscriber_doc" class="table table-bordered dt-responsive tablels">
                        <thead>
                           <tr>
                              <th>ID</th>
                              <th>{{__("message.Doctor Name")}}</th>
                              <th>{{__("message.Payment Type")}}</th>
                              <th>{{__("message.Amount")}}</th>
                              <!-- <th>TransactionID</th>
                              <th>Description</th>
                              <th>subscription Plan</th>-->
                              <th>{{__("message.Status")}}</th>
                              <!--<th>Date</th>-->
                              <th>{{__("message.Receipt File")}}</th>
                              <th>{{__("message.Action")}}</th>
                           </tr>
                        </thead>
                     </table>
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
      function active_order(record_id,status)
      {
        var x = confirm("Are you sure active this subscription");
        if(x){
             $.ajax({
              url: '{{route("active-order")}}',
              method: "POST",
              data: {
                "_token": "{{ csrf_token() }}",
                "record_id": record_id,
                "status":status
              },
              success: function (data) 
              {
                window.location.reload();
              }
          }); 
        }
      } 
         

  function disable_order(record_id,status)
  {
    var x = confirm("Are you sure disable this subscription");
    if(x){
      $.ajax({
        url: '{{route("disable-order")}}',
        method: "POST",
        data: {
          "_token": "{{ csrf_token() }}",
          "record_id": record_id,
          "status":status
        },
        success: function (data) 
        {
          window.location.reload();
        }
    }); 
    }  
  } 
</script>
@stop