@extends('admin.layout')
@section('title')
{{__("message.Save Doctors Schedule")}} | {{__("message.admin")}} {{__("message.Save Doctors Schedule")}}
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
               <h4 class="mb-0">{{__("message.Save Doctors Schedule")}}</h4>
               <div class="page-title-right">
                  <ol class="breadcrumb m-0">
                    
                     <li class="breadcrumb-item"><a href="{{url('admin/doctors')}}">{{__("message.Doctors")}}</a></li>
                     <li class="breadcrumb-item active">{{__("message.Save Doctors Schedule")}}</li>
                  </ol>
               </div>
            </div>
         </div>
      </div>
      <div class="row" style="display: flex;justify-content: center;">
         <div class="col-8">
            <div class="card">
               <div class="card-body">
                  <form action="{{url('admin/savescheduledata')}}" method="post">
                       {{csrf_field()}}  
                     <input type="hidden" name="doc_id" id="doc_id" value="{{$id}}"> 
                     <div id="accordion" class="custom-accordion">
                       <?php $arr=array(0=>__("message.Monday"),1=>__("message.Tuesday"),2=>__("message.Wednesday"),3=>__("message.Thursday"),4=>__("message.Friday"),5=>__("message.Saturday"),6=>__("message.Sunday"));?>
                        @for($i=0;$i<7;$i++)
                        <div class="card mb-1 shadow-none">
                           <input type="hidden" name="arr[]" id="day_id_{{$i}}" value="{{$i}}"> 
                           <a href="#day{{$i}}" class="text-dark collapsed" data-toggle="collapse" aria-expanded="false" aria-controls="day{{$i}}">
                                 <div class="card-header" id="head{{$i}}">
                                       <h6 class="m-0">{{$arr[$i]}}<i class="mdi mdi-chevron-up float-right accor-down-icon"></i></h6>
                                 </div>
                           </a>
                           <div id="day{{$i}}" class="collapse" aria-labelledby="head{{$i}}" data-parent="#accordion" style="">
                              <div class="card-body">
                                 <div class="row">
                                    <div class="form-group">
                                       <button type="button" class="btn btn-primary" onclick="addnewslot('{{$i}}')">{{__("message.Add Time")}}</button>
                                    </div>                          
                                 </div>
                                    <div id="day_{{$i}}">
                                 <?php $j=0;$temp=0;?>
                                  @foreach($data as $d)
                                    @if(isset($d)&&$d->day_id==$i)
                                         
                                          @if($j==0)
                                                 <div class="slotdiv" id="slotdiv{{$i}}{{$j}}">
                                             @else
                                                 <div class="slotdiv slotsecond" id="slotdiv{{$i}}{{$j}}">
                                             @endif
                                             <div class="row">
                                                <div class="col-md-3">
                                                   <label for="formrow-firstname-input">{{__("message.Start Time")}}</label>
                                                   <input type="time" class="form-control" id="start_time_{{$i}}_{{$j}}" name="arr[{{$i}}][start_time][{{$j}}]" value="{{isset($d->start_time)?$d->start_time:''}}" onchange="checkduration('{{$i}}','{{$j}}')">
                                                </div> 
                                                <div class="col-md-3">
                                                   <label for="formrow-firstname-input">{{__("message.End Time")}}</label>
                                                   <input type="time" class="form-control" id="end_time_{{$i}}_{{$j}}" name="arr[{{$i}}][end_time][]" onchange="checkduration('{{$i}}','{{$j}}')" value="{{isset($d->end_time)?$d->end_time:''}}">
                                                </div>
                                                <div class="col-md-3">
                                                   <label for="formrow-firstname-input">{{__("message.Duration")}}</label>
                                                   <select class="form-control" name="arr[{{$i}}][duration][{{$j}}]" id="duration_{{$i}}_{{$j}}" onchange="getslot(this.value,'{{$i}}','{{$j}}')">
                                                      <option value="">{{__("message.Select Duration")}}</option>
                                                      <?php echo html_entity_decode($d->options);?>
                                                   </select>
                                                </div> 
                                                <div class="col-md-3" style="margin-top: 28px;">
                                                    @if($j!=0)
                                                     <button type="button" class="btn btn-danger" onclick="removescdehule('{{$i}}','{{$j}}')">{{__("message.delete")}}</button>
                                                   @endif
                                                </div>
                                             </div>
                                             <div class="row boxmargin" id="slot_{{$i}}_{{$j}}">
                                                <?php for($k=0;$k<count($d->getslotls);$k++){
                                                         echo '<div class="col-md-12 md25">';
                                                           
                                                             if(isset($d->getslotls[$k])){
                                                               echo '<span class="slotshow">'.$d->getslotls[$k]->slot.'</span>';
                                                                $k++;
                                                             }
                                                              if(isset($d->getslotls[$k])){
                                                               echo '<span class="slotshow">'.$d->getslotls[$k]->slot.'</span>';
                                                                $k++;
                                                             }
                                                              

                                                              if(isset($d->getslotls[$k])){
                                                               echo '<span class="slotshow">'.$d->getslotls[$k]->slot.'</span>';
                                                                $k++;
                                                             }
                                                              if(isset($d->getslotls[$k])){
                                                               echo '<span class="slotshow">'.$d->getslotls[$k]->slot.'</span>';
                                                                $k++;
                                                             }
                                                              if(isset($d->getslotls[$k])){
                                                               echo '<span class="slotshow">'.$d->getslotls[$k]->slot.'</span>';
                                                                
                                                             }
                                                                             
                                                         echo '</div>';
                                                      } ?>
                                             </div>
                                           </div>
                                       
                                      
                                        <?php $j++;$temp=1;?>
                                    @endif
                                  @endforeach
                                   @if($temp==0)
                                             <div id="day_{{$i}}">
                                                <div class="slotdiv" id="slotdiv{{$i}}0">
                                                   <div class="row">
                                                      <div class="col-md-3">
                                                         <label for="formrow-firstname-input">{{__("message.Start Time")}}</label>
                                                         <input type="time" class="form-control" id="start_time_{{$i}}_0" name="arr[{{$i}}][start_time][0]"  onchange="checkduration('{{$i}}','0')">
                                                      </div> 
                                                      <div class="col-md-3">
                                                         <label for="formrow-firstname-input">{{__("message.End Time")}}</label>
                                                         <input type="time" class="form-control" id="end_time_{{$i}}_0" name="arr[{{$i}}][end_time][]" onchange="checkduration('{{$i}}','0')" >
                                                      </div>
                                                      <div class="col-md-3">
                                                         <label for="formrow-firstname-input">{{__("message.Duration")}}</label>
                                                         <select class="form-control" name="arr[{{$i}}][duration][0]" id="duration_{{$i}}_0" onchange="getslot(this.value,'{{$i}}',0)" >
                                                            <option value="">{{__("message.Select Duration")}}</option>
                                                         </select>
                                                      </div> 
                                                      <div class="col-md-3" style="margin-top: 28px;"></div>
                                                   </div>
                                                   <div class="row boxmargin" id="slot_{{$i}}_0"></div>
                                                 </div>
                                             </div>
                                             
                                         @endif
                                          </div>
                                  <input type="hidden" id="total_slot_day_{{$i}}" value="{{$j+1}}">
                              </div>
                           </div>
                        </div> 
                        @endfor
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
@stop
@section('footer')
<input type="hidden" id="start1val" value='{{__("message.Please Select Start Time First")}}'>
<input type="hidden" id="sge" value='{{__("message.Start Time is greater than end time")}}'>
<input type="hidden" id="sequale" value='{{__("message.Start Time equals end time")}}'>
<input type="hidden" id="selduration" value='{{__("message.Please Select Any Duration")}}'>
<input type="hidden" id="startvaltext" value='{{__("message.Start Time")}}'>
<input type="hidden" id="endvaltext" value='{{__("message.End Time")}}'>
<input type="hidden" id="durationval" value='{{__("message.Duration")}}'>
<input type="hidden" id="seldurationval" value='{{__("message.Select Duration")}}'>
<input type="hidden" id="deletetext" value='{{__("message.delete")}}'>
@stop