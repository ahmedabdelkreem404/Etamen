@extends('admin.layout')
@section('title')
{{__("message.Dashboard")}} | {{__("message.Admin")}} {{__("message.Dashboard")}}
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
                        <div class="page-title-box d-flex align-items-center justify-content-between">
                            <h4 class="mb-0">{{__("message.Dashboard")}}</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">                                   
                                    <li class="breadcrumb-item active">{{__("message.Dashboard")}}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="float-right mt-2" style="position: relative;">
                                    <div id="orders-chart" style="min-height: 45px;"> <div id="apexcharts636a2b" class="apexcharts-canvas apexcharts636a2b apexcharts-theme-light" style="width: 45px; height: 45px;"><i class="uil-shutter-alt" style="font-size: xx-large;"></i><div class="apexcharts-legend"></div></div></div>
                                </div>
                                <div>
                                    <h4 class="mb-1 mt-1"><span data-plugin="counterup">{{$totalappointment}}</span></h4>
                                    <p class="text-muted mb-0">{{__("message.New Appointment")}}</p>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-xl-3">
                         <div class="card">
                            <div class="card-body">
                                <div class="float-right mt-2" style="position: relative;">
                                    <div id="orders-chart" style="min-height: 45px;"> <div id="apexcharts636a2b" class="apexcharts-canvas apexcharts636a2b apexcharts-theme-light" style="width: 45px; height: 45px;"><i class="uil-flask" style="font-size: xx-large;"></i><div class="apexcharts-legend"></div></div></div>
                                </div>
                                <div>
                                    <h4 class="mb-1 mt-1"><span data-plugin="counterup">{{$totaldoctor}}</span></h4>
                                    <p class="text-muted mb-0">{{__("message.Total Doctors")}}</p>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-xl-3">
                         <div class="card">
                            <div class="card-body">
                                <div class="float-right mt-2" style="position: relative;">
                                    <div id="orders-chart" style="min-height: 45px;"> <div id="apexcharts636a2b" class="apexcharts-canvas apexcharts636a2b apexcharts-theme-light" style="width: 45px; height: 45px;"><i class="uil-file-alt" style="font-size: xx-large;"></i><div class="apexcharts-legend"></div></div></div>
                                </div>
                                <div>
                                    <h4 class="mb-1 mt-1"><span data-plugin="counterup">{{$totalpatient}}</span></h4>
                                    <p class="text-muted mb-0">{{__("message.Total Patients")}}</p>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-xl-3">
                         <div class="card">
                            <div class="card-body">
                                <div class="float-right mt-2" style="position: relative;">
                                    <div id="orders-chart" style="min-height: 45px;"> <div id="apexcharts636a2b" class="apexcharts-canvas apexcharts636a2b apexcharts-theme-light" style="width: 45px; height: 45px;"><i class="uil-star" style="font-size: xx-large;"></i><div class="apexcharts-legend"></div></div></div>
                                </div>
                                <div>
                                    <h4 class="mb-1 mt-1"><span data-plugin="counterup">{{$totalreview}}</span></h4>
                                    <p class="text-muted mb-0">{{__("message.Total Review")}}</p>
                                </div>

                            </div>
                        </div>
                    </div>
                    
                    
                </div>
                
               <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                       <h4>{{__("message.Today Appointment")}}</h4>
                                        
                                        <table id="latsrappointmenttable" class="table table-bordered dt-responsive tablels">
                                            <thead>
                                              <tr>
                              <th>{{__("message.Id")}}</th>
                              <th>{{__("message.Doctor Name")}}</th>
                              <th>{{__("message.Patient Name")}}</th>
                              <th>{{__("message.DateTime")}}</th>
                              <th>{{__("message.Phone")}}</th>
                              <th>{{__("message.User Description")}}</th>
                              <th>{{__("message.Status")}}</th>
                           </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div> 
                        </div> 
            </div> 
        </div>
@stop
@section('footer')
@stop