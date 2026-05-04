@extends('admin.layout')
@section('title')
{{__("messages.Packages")}} | {{__("messages.Admin Dashboard")}}
@stop
@section('meta-data')
@stop
@section('content')

<div class="main-content">
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Edit Medicine</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">{{__('messages.System Name')}} / </a></li>
                            <li class="active">Edit Medicine</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <a href="#addproduct-billinginfo-collapse" class="text-dark " data-bs-toggle="collapse" aria-expanded="true" aria-controls="addproduct-billinginfo-collapse">
                            @if(Session::has('message'))
                                <div class="col-sm-12">
                                   <div class="alert  {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                      {{ Session::get('message') }}
                                      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                      <span aria-hidden="true">&times;</span>
                                      </button>
                                   </div>
                                </div>
                            @endif

                        </a>
                        <form action="{{route('editmedicinessave')}}" method="post" enctype="multipart/form-data">
                        {{csrf_field()}}
                        @method('PATCH')
                            {{-- <div data-repeater-list="outer-group" class="outer"> --}}
                                {{-- <div data-repeater-item class="outer"> --}}
                                    <input type="hidden" name="id" value="{{$data->id}}">
                                    {{-- <div class="row"> --}}
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="productname">Medicine Name</label>
                                                <input id="medicinename" name="name" type="text" class="form-control" value="{{$data->name}}" placeholder='Enter your Medicine Name'required>
                                                @if ($errors->has('name'))
                                                    <span class="text-danger">{{ $errors->first('name') }}</span>
                                                @endif
                                            </div>
                                        </div>


                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="price">Dosage</label>
                                                <input id="dosage" name="dosage" type="text" class="form-control" value="{{$data->dosage}}" placeholder="Enter your Dosage" required>
                                                @if ($errors->has('Dosage'))
                                                    <span class="text-danger">{{ $errors->first('Dosage') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    {{-- </div> --}}

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="price">Description</label>
                                            <input id="description" name="description" type="text" class="form-control" value="{{$data->description}}" placeholder="Enter your description" required>

                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label for="">Type Medicine :</label>
                                            <div id="showmore">
                                                @php
                                                        $s = explode(',',$data->medicine_type);
                                                @endphp
                                                @foreach ($s as $o)
                                                    @if ($loop->first)
                                                    <div><input type="text" name="type[]" class="form-control" value="{{$o}}" placeholder="Enter your Type Medicine" required></div><br>
                                                    @else
                                                    <div class="row"><div class="col-10"><input type="text" name="type[]" class="form-control col-10" value="{{$o}}" placeholder="add options" required></div><a href="#" class="remove col-2 p-2 btn btn-icon btn-danger" id="remove">delete</a></div><br>
                                                    @endif

                                                @endforeach

                                            </div>
                                            <a class="btn btn-sm btn-success" id="addmore">Add Medicine type</a><br><br>
                                        </div>
                                    </div>

                                    {{-- <div class="inner-repeater mb-4 col-lg-6">
                                        <div data-repeater-list="inner-group" class="inner form-group">
                                            <label class="form-label">Type Medicine :</label>
                                            <div data-repeater-item class="inner mb-3 row">
                                                <div class="col-md-10 col-8">
                                                    <textarea type="text" class="inner form-control" name="type[]" placeholder="Enter your Type Medicine"/ required></textarea>
                                                    @if ($errors->has('type'))
                                                        <span class="text-danger">{{ $errors->first('type') }}</span>
                                                    @endif
                                                </div>
                                                <div class="col-md-1 col-4">
                                                    <div class="d-grid">
                                                        <input data-repeater-delete type="button" class="btn btn-danger btn-sm inner" value="Delete"/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <input data-repeater-create type="button" class="btn btn-sm btn-success inner" value="Add Medicine type"/>
                                    </div> --}}

                                    <button type="submit" class="btn btn-primary ">Submit</button>
                                {{-- </div> --}}
                            {{-- </div> --}}
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end row -->

    </div> <!-- container-fluid -->
</div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script>
    $(document).ready(function(){

        $('#addmore').click(function(){
            event.preventDefault();
            $('#showmore').append('<div class="row"><div class="col-10"><input type="text" name="type[]" class="form-control col-10 " placeholder="add options" required></div><a href="#" class="remove col-2 p-2  btn btn-icon btn-danger" id="remove">delete</a></div><br>');

        });

        $('#showmore').on("click",".remove",function(e){
            e.preventDefault();
            $(this).parent('div').remove();

        });
    });
</script>
@stop
@section('footer')
@stop
