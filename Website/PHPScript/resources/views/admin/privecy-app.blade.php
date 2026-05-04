@extends('admin.layout')
@section('title')
Abouts | Admin Dashboard
@stop
@section('meta-data')
@stop
@section('content')
<style>
    td.dataTables_empty {
    font-size: medium;
    font-weight: 600;
}
</style>
<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">
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
              <h4>{{__("message.Privecy")}}</h4>
                <div class="content mt-3">
                  <div class="animated">
                    <div class="col-sm-12">
                      <div class="modal-content">
                        <div class="modal-content">
                          <div class="modal-header">
                             <h6 class="modal-title">{{url('Privacy-Policy')}}</h6>
                              <a href= "{{url('Privacy-Policy')}}" class="btn btn-md btn-primary" value="Visit" target="#"  style="float:right">Visit</a>
                            
                          </div>
                          <div class="modal-body">
                            <form action="{{url('admin/edit_app_privacy')}}" method="post" enctype="multipart/form-data">
                              {{csrf_field()}} 
                              <div class="form-group">
                                
                                <input type="hidden" class="form-control" id="id" name="id" required="" value="{{isset($data->id)?$data->id:0}}">

                                <textarea class="form-control" name="privacy">{{isset($data->privacy)?$data->privacy:''}}</textarea>
                                
                              </div>
                              <button name="update_about" type="submit" class="btn btn-md btn-success ">submit</button>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>          
            </div>
          </div>
        </div> 
      </div> 
    </div> 
  </div>
</div>

<script src="{{asset('public/js/vendor/jquery-2.1.4.min.js')}}"></script>
 <script src="{{asset('public/ckeditor/ckeditor.js')}}"></script>
  <script type="text/javascript">
    $(document).ready(function () 
      {
          CKEDITOR.replace('privacy');
      });
  </script>
@stop
@section('footer')
@stop