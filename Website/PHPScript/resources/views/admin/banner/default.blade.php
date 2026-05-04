@extends('admin.layout')
@section('title')
{{__("message.banner")}} | {{__("message.Admin")}} {{__("message.banner")}}
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
                  <h4 class="mb-0">{{__("message.banner")}}</h4>
                  <div class="page-title-right">
                     <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item active">{{__("message.banner")}}</li>
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
                     <h4 class="card-title">{{__("message.banner")}} {{__("message.List")}}</h4>
                      <p><a class="btn btn-primary" data-toggle="modal" data-target="#staticBackdrop">{{__("message.Add Banner")}}</a></p>
                     <table id="bannertable" class="table table-bordered dt-responsive tablels">
                        <thead>
                           <tr>
                              <th>{{__("message.Id")}}</th>
                              <th>{{__("message.Image")}}</th>
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
 <input type="hidden" id="site" value="{{url('/')}}" />
<!-- Modal -->
<div class="modal fade" id="staticBackdrop" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="staticBackdropLabel">Add Banner</h5>
      </div>

      <form action="{{url('admin/savebanner')}}" class="needs-validation" method="post" enctype="multipart/form-data">
        {{csrf_field()}} 
          <div class="modal-body">
            <main class="main_full">
              <div class="container">
                <div class="panel">
                  <div class="button_outer">
                    <div class="btn_upload">
                     <label>Upload Image</label>
                      <input type="file" id="upload_file" name="image[]" multiple required>
                     
                    </div>
                    <div class="processing_bar"></div>
                    <div class="success_box"></div>
                  </div>
                </div>
                <div class="error_msg"></div>
                
              </div>
            </main>
          </div>
          <center>
          <div style="padding: 0.75rem; border-top: 1px solid #f5f6f8;border-bottom-right-radius: calc(0.3rem - 1px);border-bottom-left-radius: calc(0.3rem - 1px);">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <!--<button type="submit" class="btn btn-primary">Submit</button>-->
           @if(Session::get("is_demo")=='0')
              <button type="button" onclick="disablebtn()" class="btn btn-primary">{{__('message.Submit')}}</button>
           @else
               <button  class="btn btn-primary" type="submit" value="Submit">{{__("message.Submit")}}</button>
           @endif 
          </div>
          </center>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="Backdrop" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="staticBackdropLabel">Edit Banner</h5>
      </div>

      <form action="{{url('admin/updatebanner')}}" class="needs-validation" method="post" enctype="multipart/form-data">
        {{csrf_field()}} 
          <div class="modal-body">
            <main class="main_full">
              <div class="container">
                <div class="panel">
                  <div class="button_outer">
                    <div class="btn_upload">
                     <label>Upload Image</label>
                     <img src="" width=50 id="banner_image" style="width:70%">
                      <input type="file" id="upload_file" name="image" multiple required>

                     <input type="hidden" name="id" id="img_id">

                    </div>
                    <div class="processing_bar"></div>
                    <div class="success_box"></div>
                  </div>
                </div>
                <div class="error_msg"></div>
                
              </div>
            </main>
          </div>
          <center>
          <div style="padding: 0.75rem; border-top: 1px solid #f5f6f8;border-bottom-right-radius: calc(0.3rem - 1px);border-bottom-left-radius: calc(0.3rem - 1px);">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
           @if(Session::get("is_demo")=='0')
              <button type="button" onclick="disablebtn()" class="btn btn-primary">{{__('message.Submit')}}</button>
           @else
               <button  class="btn btn-primary" type="submit" value="Submit">{{__("message.Submit")}}</button>
           @endif 
          </div>
          </center>
      </form>
    </div>
  </div>
</div>
@stop
@section('footer')
<script>
  
function edit_img(id){
    $("#img_id").val(id);
    $.ajax({
                
         url:$("#siteurl").val()+"/edit-img"+"/"+id,
         data: { },
         success: function(data)
         {
            var url=$("#site").val()+"/public/upload/banner"+"/"+data;
            
            $("#banner_image").attr("src",url);
            // $("#stone_name").val(data.name);
         }
        });

}

</script>
@stop