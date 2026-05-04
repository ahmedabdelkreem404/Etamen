console.log($("#siteurl").val());

$(document).ready(function () {
    $('#servicestable').DataTable({
        processing: true,
        serverSide: true,
        ajax: $("#siteurl").val()+'/servicestable',
        columns: [
            {data: 'id'    , name: 'id'},            
            {data: 'icon' , name:'icon'},
            {data: 'name'  , name: 'name'},
            {data: 'action', name:'action'}
        ],
         columnDefs: [
            { targets: 1,
              render: function(data) {
                    return '<img src="'+data+'" height="50">';
              }
            }   
        ],
        order: [
                [0, "desc"]
            ]     
         
    });
});

$(document).ready(function () {
    $('#subscriptiontable').DataTable({
        processing: true,
        serverSide: true,
        ajax: $("#siteurl").val()+'/subscriptiontable',
        columns: [
            {data: 'month' , name:'month'},
            {data: 'amount'  , name: 'amount'},
            {data: 'action', name:'action'}
        ],
        order: [
                [0, "desc"]
            ]
    });
});

$(document).ready(function () {
   
    $('#bannertable').DataTable({
        processing: true,
        serverSide: true,
        ajax: $("#siteurl").val()+'/bannertable',
        columns: [
            {data: 'id'    , name: 'id'},  
            {data: 'image'  , name: 'image'},          
            {data: 'action', name:'action'}
        ],
        columnDefs: [
            { targets: 1,
              render: function(data) {
                    return '<img src="'+data+'" height="50">';
              }
            }   
        ],
        order: [
                [0, "desc"]
            ]     
         
    });
});

$(document).ready(function () {
    $('#subscriber_doc').DataTable({
        processing: true,
        serverSide: true,
        ajax: $("#siteurl").val()+'/subscribetable',
        columns: [
            {data: 'id', name: 'id'},
            {data: 'doctor_id' , name:'doctor_id'},
            {data: 'payment_type'  , name: 'payment_type'},
            {data: 'amount', name:'amount'},
            /*{data: 'transaction_id', name:'transaction_id'},
            {data: 'description', name:'description'},
            {data: 'subscription_id', name:'subscription_id'},*/
            {data: 'status', name:'status'},
            // {data: 'date', name:'date'},
            {data: 'deposit_image', name:'deposit_image'},
            {data: 'action', name:'action'}
        ],
         columnDefs: [
            { targets: 4,
              render: function(data)
              {
                  if(data != "" && data != null)
                  {
                      return '<a href="'+data+'" target="_blank">Show Receipt</a>';
                  }
                  else{
                      return '-';
                  }
                // return '<a href="'+data+'" src="'+data+'" height="50" width="80">';

              }
            }
        ],
        order: [
                [0, "desc"]
            ]
    });
});

$(document).ready(function () {
    $('#pendingpaymenttable').DataTable({
        processing: true,
        serverSide: true,
        ajax: $("#siteurl").val()+'/pendingpaymenttable',
        columns: [           
            {data: 'doctor_name' , name:'doctor_name'},
            {data: 'amount'  , name: 'amount'},
            {data: 'total_booking',name:'total_booking'},
            {data: 'action', name:'action'}
        ],
        order: [
                [0, "desc"]
            ]
    });
});

$(document).ready(function () {
    $('#contactdatatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: $("#siteurl").val()+'/contact_list_table',
        columns: [           
            {data: 'id' , name:'id'},
            {data: 'name'  , name: 'name'},
            {data: 'email',name:'email'},
            {data: 'phone', name:'phone'},
            {data: 'subject', name:'subject'},
            {data: 'message', name:'message'}
        ],
        order: [
                [0, "desc"]
            ]
    });
});



$(document).ready(function () {
    $('#completepaymenttable').DataTable({
        processing: true,
        serverSide: true,
        ajax: $("#siteurl").val()+'/completepaymenttable',
        columns: [           
            {data: 'doctor_name' , name:'doctor_name'},
            {data: 'amount'  , name: 'amount'},
            {data: 'date',name:'date'},
            {data: 'translation_id', name:'translation_id'}
        ],
        order: [
                [0, "desc"]
            ]
    });
});

function delete_record(url) {
    if (confirm($("#delete_record").val())) {
       if($("#demo").val()=='1'){
            window.location.href = url;
       }
       else{
           disablebtn();
        }
        
    } else {
        window.location.reload();
    }
}
$(document).ready(function () {
    $('#appointmenttable').DataTable({
        processing: true,
        serverSide: true,
        ajax: $("#siteurl").val()+'/appointmenttable',
        columns: [
            {data: 'id'    , name: 'id'},            
            {data: 'doctor_name' , name:'doctor_name'},
            {data: 'patient_name' , name:'patient_name'},
            {data: 'date' , name:'date'},
            {data: 'phone'  , name: 'phone'},
            {data: 'u_desc'  , name: 'u_desc'},
            {data: 'status'  , name: 'status'},
            {data: 'action'  , name: 'action'}
        ],
        order: [
                [0, "desc"]
            ]
    });
});

$(document).ready(function () {
    $('#compaintable').DataTable({
        processing: true,
        serverSide: true,
        ajax: $("#siteurl").val()+'/compaintable',
        columns: [
            {data: 'id'    , name: 'id'},            
            {data: 'username' , name:'username'},
            {data: 'title' , name:'title'},
            {data: 'description' , name:'description'},
            {data: 'action'  , name: 'action'}
        ],
        order: [
                [0, "desc"]
            ]
    });
});


$(document).ready(function () {
    $('#latsrappointmenttable').DataTable({
        processing: true,
        serverSide: true,
        ajax: $("#siteurl").val()+'/latsrappointmenttable',
        columns: [
            {data: 'id'    , name: 'id'},            
            {data: 'doctor_name' , name:'doctor_name'},
            {data: 'patient_name' , name:'patient_name'},
            {data: 'date' , name:'date'},
            {data: 'phone'  , name: 'phone'},
            {data: 'u_desc'  , name: 'u_desc'},
            {data: 'status'  , name: 'status'}
        ],
        order: [
                [0, "desc"]
            ],
           "language": {
              "emptyTable": $("#today_no_appointment_msg").val()
            }
    });
});




$(document).ready(function () {
    $('#notificationtable').DataTable({
        processing: true,
        serverSide: true,
        ajax: $("#siteurl").val()+'/notificationtable',
        columns: [
            {data: 'id'    , name: 'id'},            
            {data: 'message' , name:'message'}
        ],
        order: [
                [0, "desc"]
            ]
    });
});


$(document).ready(function() {
            $('#upload_image').on('change', function(e) {
                    readURL(this,"basic_img");
            });
     });
     function readURL(input,field) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function (e) {
                $("#basic_img1").val(e.target.result);
                $('#'+field).attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    $('#us2').locationpicker({
    location: {
        latitude: $("#us2-lat").val(),
        longitude: $("#us2-lon").val()
    },
    radius: 300,
    inputBinding: {
        latitudeInput: $('#us2-lat'),
        longitudeInput: $('#us2-lon'),
        radiusInput: $('#us2-radius'),
        locationNameInput: $('#us2-address')
    },
    enableAutocomplete: true
});

$(document).ready(function () {
    $('#reviewtable').DataTable({
        processing: true,
        serverSide: true,
        ajax: $("#siteurl").val()+'/reviewtable',
        columns: [
            {data: 'id'    , name: 'id'},            
            {data: 'doctor_name' , name:'doctor_name'},
            {data: 'username'  , name: 'username'},
            {data: 'ratting' , name:'ratting'},
            {data: 'comment' , name:'comment'},
            {data: 'action', name:'action'}
        ],
        order: [
                [0, "desc"]
            ]
    });
});



$(document).ready(function () {
    $('#doctorstable').DataTable({
        processing: true,
        serverSide: true,
        ajax: $("#siteurl").val()+'/doctorstable',
        columns: [
            {data: 'id'    , name: 'id'},  
            {data: 'image'  , name: 'image'},          
            {data: 'name' , name:'name'},
            {data: 'email'  , name: 'email'},
            {data: 'phone'  , name: 'phone'},
            {data: 'service' , name:'service'},
            {data: 'action', name:'action'}
        ],
        columnDefs: [
            { targets: 1,
              render: function(data) {
                    return '<img src="'+data+'" height="50">';
              }
            }   
        ],
        order: [
                [0, "desc"]
            ]
         
    });
});
$(document).ready(function () {
    $('#lastestordertable').DataTable({
        processing: true,
        serverSide: true,
        ajax: $("#siteurl").val()+'/lastestordertable',
        columns: [
            {data: 'id'    , name: 'id'},            
            {data: 'name' , name:'name'},
            {data: 'email'  , name: 'email'},
            {data: 'payment_type' , name:'payment_type'},
            {data: 'subtotal' , name:'subtotal'},
            {data: 'action', name:'action'}
        ],
        order: [
                [0, "desc"]
            ]
    });
});



$(document).ready(function () {
    $('#userstable').DataTable({
        processing: true,
        serverSide: true,
        ajax: $("#siteurl").val()+'/userstable',
        columns: [
            {data: 'id'    , name: 'id'},            
            {data: 'name' , name:'name'},
            {data: 'email'  , name: 'email'},
            {data: 'phone' , name:'phone'},
            {data: 'action', name:'action'}
        ],
        order: [
                [0, "desc"]
            ]
    });
});
$(document).ready(function () {
    $('#contacttable').DataTable({
        processing: true,
        serverSide: true,
        ajax: $("#siteurl").val()+'/contacttable',
        columns: [
            {data: 'id'    , name: 'id'},            
            {data: 'name' , name:'name'},
            {data: 'email'  , name: 'email'},
            {data: 'subject' , name:'subject'},
            {data: 'message' , name:'message'},
            {data: 'action', name:'action'}
        ],
        order: [
                [0, "desc"]
            ]
    });
});

function checkduration(day_id,cid){
     console.log(day_id);
     console.log(cid);
    if($("#start_time_"+day_id+"_"+cid).val()==""){
        alert($("#start1val").val());

    }else{
      var strStartTime = $("#start_time_"+day_id+"_"+cid).val();
      var strEndTime = $("#end_time_"+day_id+"_"+cid).val();
      var startTime = new Date().setHours(GetHours(strStartTime), GetMinutes(strStartTime), 0);
      var endTime = new Date(startTime)
          endTime = endTime.setHours(GetHours(strEndTime), GetMinutes(strEndTime), 0);
            if (startTime > endTime) {
                alert($("#sge").val());
                $("#start_time_"+day_id+"_"+cid).val("");
                $("#end_time_"+day_id+"_"+cid).val("");
            }
            if (startTime == endTime) {
                alert($("#sequale").val());
                $("#start_time_"+day_id+"_"+cid).val("");
                $("#start_time_"+day_id+"_"+cid).val("");
            }
            if (startTime < endTime) {
                 $.ajax({
                            url: $("#siteurl").val()+"/findpossibletime",
                            method:"get",
                            data:{start_time:strStartTime,end_time:$("#end_time_"+day_id+"_"+cid).val(),duration:$("#duration_"+day_id+"_"+cid).val()},
                            success: function( data ) {
                                console.log(data);
                                 var duval=$("#duration_"+day_id+"_"+cid).val();
                                if($("#duration_"+day_id+"_"+cid).val()!=""){
                                    $.ajax({
                                            url: $("#siteurl").val()+"/generateslot",
                                            method:"get",
                                            data:{start_time:$("#start_time_"+day_id+"_"+cid).val(),end_time:$("#end_time_"+day_id+"_"+cid).val(),duration:$("#duration_"+day_id+"_"+cid).val()},
                                            success: function( data ) {
                                                 console.log(data);
                                                 $("#slot_"+day_id+"_"+cid).html(data);
                                            }
                                    });
                                }
                                $("#duration_"+day_id+"_"+cid).html(data);

                            }
                });
            }
    }
   
}

function getslot(value,day_id,cid){
     if($("#start_time_"+day_id+"_"+cid).val()==""){
        alert($("#start1val").val());

    }else{
      var strStartTime = $("#start_time_"+day_id+"_"+cid).val();
      var strEndTime = $("#end_time_"+day_id+"_"+cid).val();
      var startTime = new Date().setHours(GetHours(strStartTime), GetMinutes(strStartTime), 0);
      var endTime = new Date(startTime)
          endTime = endTime.setHours(GetHours(strEndTime), GetMinutes(strEndTime), 0);
            if (startTime > endTime) {
                alert($("#sge").val());
                $("#start_time_"+day_id+"_"+cid).val("");
                $("#end_time_"+day_id+"_"+cid).val("");
            }
            if (startTime == endTime) {
                alert($("#sequale").val());
                $("#start_time_"+day_id+"_"+cid).val("");
                $("#start_time_"+day_id+"_"+cid).val("");
            }
            if (startTime < endTime) {
                if($("#duration_"+day_id+"_"+cid)!=""){
                    $.ajax({
                            url: $("#siteurl").val()+"/generateslot",
                            method:"get",
                            data:{start_time:$("#start_time_"+day_id+"_"+cid).val(),end_time:$("#end_time_"+day_id+"_"+cid).val(),duration:$("#duration_"+day_id+"_"+cid).val()},
                            success: function( data ) {
                                 console.log(data);
                                 $("#slot_"+day_id+"_"+cid).html(data);
                            }
                    });
                }else{
                    alert($("#selduration").val());
                }
                
            }
    }       
           
}
 
 function GetHours(d) {
    var h = parseInt(d.split(':')[0]);
    if (d.split(':')[1].split(' ')[1] == "PM") {
        h = h + 12;
    }
    return h;
}
function GetMinutes(d) {
    return parseInt(d.split(':')[1].split(' ')[0]);
}

function addnewslot(day_id){
    var slotid=$("#total_slot_day_"+day_id).val();
    var txt='<div class="slotdiv slotsecond" id="slotdiv'+day_id+slotid+'"><div class="row"><div class="col-md-3"><label for="formrow-firstname-input">'+$("#startvaltext").val()+'</label><input type="time" class="form-control" id="start_time_'+day_id+'_'+slotid+'" required="" name="arr['+day_id+'][start_time][]" onchange="checkduration('+day_id+','+slotid+')"></div><div class="col-md-3"><label for="formrow-firstname-input">'+$("#endvaltext").val()+'</label><input type="time" required="" class="form-control" id="end_time_'+day_id+'_'+slotid+'" name="arr['+day_id+'][end_time][]"  onchange="checkduration('+day_id+','+slotid+')"></div><div class="col-md-3"><label for="formrow-firstname-input">'+$("#durationval").val()+'</label><select class="form-control" required="" name="arr['+day_id+'][duration][]" id="duration_'+day_id+'_'+slotid+'" onchange="getslot(this.value,'+day_id+','+slotid+')"><option value="">'+$("#seldurationval").val()+'</option></select></div><div class="col-md-3" style="margin-top: 28px;"><button type="button" class="btn btn-danger" onclick="removescdehule('+day_id+','+slotid+')">'+$("#deletetext").val()+'</button></div></div><div class="row boxmargin" id="slot_'+day_id+'_'+slotid+'"></div></div>';
    $("#day_"+day_id).append(txt);
    $("#total_slot_day_"+day_id).val(parseInt(slotid)+parseInt(1));
}

function removescdehule(day_id,cid){
    $("#slotdiv"+day_id+cid).remove();
}

$(document).ready( function () {
        $('#medicine').DataTable({
            "order": [[0, "desc"]]
        });
    });



/* function play_sound() {
            var source = $("#soundnotify").val();
            var audioElement = document.createElement('audio');
            audioElement.autoplay = true;
            audioElement.load();
            audioElement.addEventListener("load", function() { 
                audioElement.play(); 
            }, true);
            audioElement.src = source;
        }
    $(document).ready(function(){
            function have_notification(){
                $.ajax({
                    url:$("#siteurl").val()+"/notification/1",
                    method:"GET",
                    dataType:"json",
                    success:function(resp) {
                        var data = resp.response;
                        console.log(data);
                        if(resp.status == 200){
                            if(data.total > 0){
                                document.getElementById("ordercount").innerHTML=data.total;
                                document.getElementById("notificationmsg").innerHTML='You have <b>'+data.total+'</b> new orders';
                                $('#bell-animation').addClass('icon-anim-pulse');
                                $('.notification-badge').addClass('badge-danger');
                                play_sound();
                               
                            } else{
                                document.getElementById("ordercount").innerHTML=0;
                                document.getElementById("notificationmsg").innerHTML="You have not any pending orders";
                                   document.getElementById("notificationshow").style.display="none";
                               
                            }
                        } else {
                             document.getElementById("ordercount").innerHTML=0;
                            document.getElementById("notificationmsg").innerHTML="You have not any pending orders";
                            $('#bell-animation').removeClass('icon-anim-pulse');
                            $('.notification-badge').removeClass('badge-danger');
                        }
                    }
                });
            }
            have_notification();

            setInterval(function(){
                have_notification();
            },10000);
        });

        // load new notifications
        $('#bell-button').click(function(){
            $.ajax({
                url:$("#siteurl").val()+"/notification/0",
                method:"GET",
                dataType:"json",
                success:function(resp){
                    var data = resp.response;
                    if(resp.status == 200){
                        $('#notification-data').html(data.data);
                      //  document.getElementById("notify").style.display="none";
                        
                        $('#bell-animation').removeClass('icon-anim-pulse');
                        $('.notification-badge').removeClass('badge-danger');
                    }
                }
            });
        });*/