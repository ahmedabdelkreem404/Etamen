@extends('admin.layout')
@section('title')
{{__("message.Save Subscription")}} | {{__("message.Admin")}}
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
                  <h4 class="mb-0">{{__("message.View Subscription")}}</h4>
                  <div class="page-title-right">
                     <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{url('admin/subscriber_doc')}}">{{__("message.Subscription")}}</a></li>
                        <li class="breadcrumb-item active">{{__("message.View Subscription")}}</li>
                     </ol>
                  </div>
               </div>
            </div>
         </div>
         <div class="row" style="display: flex;justify-content: center;">
            <div class="col-8">
               <div class="card">
                  <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                   <label for="formrow-firstname-input"><b>{{__("message.Doctor Name")}}</b></label> {{isset($data)?$data->doctor_id:''}}
                                </div>
                            </div>
                             <div class="col-6">
                                <div class="form-group">
                                   <label for="formrow-firstname-input"><b>{{__("message.Payment Type")}}</b></label> 
                                   <?php
                                        if(isset($data->payment_type) && $data->payment_type==1){
                                            echo __("message.Braintree");           
                                        }
                                         if(isset($data->payment_type) && $data->payment_type==2){
                                            echo __("message.Bank Deposit");           
                                        }
                                   ?>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                   <label for="formrow-firstname-input"><b>{{__("message.Amount")}}</b></label>
                                    {{isset($data)?$data->amount:''}}
                                </div>
                            </div>
                             <div class="col-6">
                                <div class="form-group">
                                   <label for="formrow-firstname-input"><b>{{__("message.TransactionID")}}</b></label> 
                                   {{isset($data)?$data->transaction_id:''}}
                                </div>
                            </div>
                            
                            <div class="col-6">
                                <div class="form-group">
                                   <label for="formrow-firstname-input"><b>{{__("message.Subscription Plan")}}</b></label>
                                   
                                    <?php
                                        if(isset($data->subscription_id)){
                                            echo $data->subscription_id." ".__("message.Month");           
                                        }
                                    ?>
                                    
                                </div>
                            </div>
                             <div class="col-6">
                                <div class="form-group">
                                   <label for="formrow-firstname-input"><b>{{__("message.Status")}}</b></label> 
                                   <?php
                                        if(isset($data->status) && $data->status==1)
                                        {
                                            echo __("message.Not Active");           
                                        }
                                        if(isset($data->status) && $data->status==2)
                                        {
                                            echo __("message.Active");           
                                        }
                                        if(isset($data->status) && $data->status==3)
                                        {
                                            echo __("message.Expired");           
                                        }
                                         if(isset($data->status) && $data->status==4)
                                        {
                                            echo "Reject";           
                                        }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="col-6">
                                <div class="form-group">
                                   <label for="formrow-firstname-input"><b>{{__("message.Date")}}</b></label>
                                   
                                    <?php
                                        if(isset($data->date)){
                                            echo $data->date;           
                                        }
                                    ?>
                                    
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                   <label for="formrow-firstname-input"><b>{{__("message.Description")}}</b></label> 
                                   <?php
                                        if(isset($data->description))
                                        {
                                            echo $data->description;           
                                        }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-group">
                                   <label for="formrow-firstname-input"><b>{{__("message.Receipt File")}}</b></label> 
                                   <?php
                                        if(isset($data->deposit_image))
                                        {
                                            $folderName = '/upload/bank_receipt/';
                                            $destinationPath = asset("public/upload/bank_receipt").'/'.$data->deposit_image;
                                           ?>
                                            <!--<img src="<?= $destinationPath ?>"  height="300" width="600">-->
                                            <a href="<?php echo $destinationPath; ?>"  target="_blank">Show Receipt</a>
                                           <?php
                                        }else{
                                            echo "-";
                                        }
                                   ?>
                                   
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
@stop
@section('footer')
@stop