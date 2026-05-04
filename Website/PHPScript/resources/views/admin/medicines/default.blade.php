@extends('admin.layout')
@section('title')
{{__("Medicines")}} | {{__("Admin Dashboard")}} 
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
                    <h4 class="mb-0">{{__("Medicines")}}</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">{{__('System Name')}} / </a></li>
                            <li class="active"> {{__("Medicines")}}</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->
        @if(Session::has('message'))
            <div class="col-sm-12">
                <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                    <i class="uil uil-check me-2"></i>
                    {{ Session::get('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    </button>
                </div>
            </div>
         @endif
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <div>
                                <a href="{{url('admin/medicinesadd')}}" type="button" class="btn btn-success waves-effect waves-light mb-3"><i class="fas fa-user-plus"></i>Add Medicines</a> 
                            </div>

                            <div class="table-responsive mb-4">
                               <table class="table table-centered datatable dt-responsive nowrap table-card-list" id="medicine" style="border-collapse: collapse; width: 100%;">
                                    <thead>
                                        <tr>

                                            <th style="width: 120px;">{{__("ID")}} </th>
                                            <th>name</th>
                                            <th>Dosage</th>
                                            <th>Description</th>
                                            <th>Medicine Type</th>
                                            <th style="width: 120px;">{{__("Action")}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    
                                    @foreach($data as $user)
                                        <tr>

                                            <td><a href="javascript: void(0);" class="text-reset  fw-bold">{{$user->id}}</a> </td>

                                            {{-- <td>
                                                <img src="{{asset('public/upload/medicine/'.$user->image)}}" alt="" class="avatar-xs rounded-circle me-2" style="height: 4.2rem; width: 4.2rem;">
                                            </td> --}}
                                            <td>
                                                <span>{{$user->name}}</span>
                                            </td>
                                            <td>{{$user->dosage}}</td>
                                            <td>{{$user->description}}</td>
                                            <td>{{$user->medicine_type}}</td>
                                            <td>
                                                <a href="{{route('editmedicines', $user->id)}}" class="px-3 text-primary"><i class="uil uil-pen font-size-18"></i></a>
                                                
                                                <a href="{{route('deletemedicines', $user->id)}}" class="px-3 px-3 text-danger"><i class="uil uil-trash-alt font-size-18">
                                                        </i></a>
                                            </td>
                                           
                                        </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end row -->

    </div> <!-- container-fluid -->
</div
</div>

 


@stop
@section('footer')
@stop