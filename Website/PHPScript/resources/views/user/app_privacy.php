@extends('user.layout')
@section('title')
Privecy | Admin Dashboard
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
              <h4>Privecy</h4>
                <div class="content mt-3">
                  <div class="animated">
                    <div class="col-sm-10">
                      <div class="modal-content">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Privecy</h5>
                          </div>
                          <div class="modal-body">
                            <form action="{{url('admin/edit_privecy')}}" method="post" enctype="multipart/form-data">
                              {{csrf_field()}} 
                              <div class="form-group">
                                <label>Privecy</label>
                                
                                <input type="hidden" class="form-control" id="id" name="id" required="" value="{{isset($data->id)?$data->id:0}}">

                                <textarea class="form-control" name="desctiption">{{isset($data->desctiption)?$data->desctiption:''}}</textarea>
                                
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
          CKEDITOR.replace('desctiption');
      });
  </script>
@stop
@section('footer')
@stop