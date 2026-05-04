@extends('admin.layout')
@section('title')
Doctor Subscription Report
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
                   <h4 class="mb-0">Doctor Subscription Report</h4>
                   <div class="page-title-right">
                      <ol class="breadcrumb m-0">
                         <li class="breadcrumb-item active">Doctor Subscription Report</li>
                      </ol>
                   </div>
                </div>
             </div>
          </div>
          <div class="row">
             <div class="col-12">
                <div class="card">
                   <div class="card-body">
                    <div class="row mb-4">
                        <div class="col">

                            <form action="do_sub_report" method="get" >
                                <div class="row">
                                    <div class="col-3">
                                        <div class="input-group">
                                                 <label for="" class="mt-2">Report : </label>
                                                <select name="data_filter" id="" class="form-control" onchange="showDiv('hidden_div', this)">
                                                    <option value="">All data</option>
                                                    <option value="1" {{Request::get('data_filter') == '1' ? 'selected':''}}>custom</option>
                                                    <option value="today" {{Request::get('data_filter') == 'today' ? 'selected':''}}>Today</option>
                                                    <option value="last_week" {{Request::get('data_filter') == 'last_week' ? 'selected':''}}>Last week</option>
                                                    <option value="this_month" {{Request::get('data_filter') == 'this_month' ? 'selected':''}}>This month</option>
                                                    <option value="last_month" {{Request::get('data_filter') == 'last_month' ? 'selected':''}}>Last month</option>
                                                    <option value="this_year" {{Request::get('data_filter') == 'this_year' ? 'selected':''}}>This year</option>
                                                    <option value="last_year" {{Request::get('data_filter') == 'last_year' ? 'selected':''}}>Last year</option>
                                                </select>
                                         </div>
                                        </div>
                                        @if(Request::get('data_filter') == '1')
                                        <div class="col-4" id="hidden_div">
                                             <div class="input-group">
                                                 <label for="" class="mt-2">start date :</label>
                                                 <input type="date" name="start_date" value="{{Request::get('start_date') ?? date('y-m-d')}}" class="form-control">

                                                 <label for="" class="mt-2 ml-3"> end date :</label>
                                                 <input type="date" name="end_date" value="{{Request::get('end_date') ?? date('y-m-d')}}" class="form-control" >
                                             </div>
                                       </div>
                                       @else
                                       <div class="col-4" id="hidden_div" style="display: none;">
                                             <div class="input-group">
                                                 <label for="" class="mt-2">start date :</label>
                                                 <input type="date" name="start_date"  class="form-control">

                                                 <label for="" class="mt-2 ml-3"> end date :</label>
                                                 <input type="date" name="end_date"  class="form-control" >
                                             </div>
                                       </div>
                                       @endif
                                      <div class="col-2">
                                          <input type="submit" class="btn btn-primary">
                                     </div>
                                </div>
                            </form><br>
                        </div>
                    </div>
                    <h4 class="card-title">
                        @if(Request::get('data_filter') )
                            @if ($total == null)
                            doctor subscribe not found
                                @else
                                Total doctor subscribe are {{$total}}
                            @endif
                            @else
                            Doctor Subscribe List
                        @endif
                        </h4>

                            <input type="hidden" name="count_data" value="{{$total}}" id="count_data">

                      <table id="myTable" class="table table-bordered dt-responsive tablels">
                         <thead>
                            <tr>
                               <th>Id</th>
                               <th>Doctor Name</th>
                               <th>payment Type</th>
                               <th>Amount</th>
                               <th>Status</th>
                               {{-- <th>Receipt File</th> --}}
                            </tr>
                         </thead>
                         <tbody>
                            @foreach ($doctorsubscription as $doc)
                            <tr>
                                <td>{{$doc->id}}</td>
                                <td>
                                    @if ($doc->doctors)
                                        {{$doc->doctors->name}}
                                    @endif
                                </td>
                                <td>
                                    @if($doc->payment_type == 1)
                                    Braintree
                                    @elseif ($doc->payment_type == 2)
                                    Bank Deposit
                                    @elseif ($doc->payment_type == 3)
                                    Razorpay
                                    @elseif ($doc->payment_type == 4)
                                    Paystack
                                    @endif
                                </td>
                                <td>${{$doc->amount}}</td>
                                <td>
                                    @if($doc->status == 1)
                                    not active
                                    @elseif ($doc->status == 2)
                                    active
                                    @elseif ($doc->status == 3)
                                    expiry
                                    @elseif ($doc->status == 4)
                                    reject
                                    @endif
                                </td>
                                {{-- <td></td> --}}
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
 </div>

 <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
 <!--<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css" />-->
 <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
 <script>
    function showDiv(divId, element) {
       document.getElementById(divId).style.display = element.value == 1 ? 'block' : 'none';
    }
 </script>
 <script>
    // $(document).ready(function(){
    //     let table = new DataTable('#myTable', {
    //                 order: [[0, 'desc']]
    //                 });
    // });
    
    $(document).ready(function() {

    var test = $("#count_data").val();
    if(test > 0){
        $('#myTable').DataTable( {
        dom: 'Bfrtip',
        order: [[0, 'desc']],
        buttons: [
            { 
      extend: 'excel',
      text: 'Download excel'
   }
        ]
    } );
    }else{
        let table = new DataTable('#myTable', {
            order: [[0, 'desc']]
            });
    }
    
    } );
</script>

@stop
@section('footer')

@stop

