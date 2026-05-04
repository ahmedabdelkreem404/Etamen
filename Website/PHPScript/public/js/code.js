

function addnewsletter(){
     var email=$("#emailnews").val();
     if(email==""){
         swal($("#Errorlabel").val(),'Please Enter Your Email',"error");      
     }else{
            if(ValidateEmail(email)){
                 $.ajax({
                            url: $("#siteurl").val()+"/addnewsletter"+"/"+email,
                            method:"get",
                            success: function( data ) {
                               $("#emailnews").val("");
                               swal($("#successlabel").val(),$("#contactsuccssmsg").val(),"success");  
                            }
                });
                
            }else{
                $("#emailnews").val("");
                swal($("#Errorlabel").val(),$("#emailinvaildlabel").val(),"error"); 
            }
        
     }
}


  function delete_record(url) {
    if (confirm($("#delete_record").val())) {
      
            window.location.href = url;
      
       
    } else {
        window.location.reload();
    }
}


$(document).ready(function () {
    var d = new Date();
    var monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"];
    today = monthNames[d.getMonth()] + ' ' + d.getDate() + ' ' + d.getFullYear();   
    
        var from = new Date();
        var date_diff = Math.ceil((from.getTime() - Date.parse(today)) / 86400000);
        var maxDate_d = date_diff+30+'d';
        date_diff = date_diff + 'd';
        $('#date').datepicker({
            dateFormat: 'yy-mm-dd',
            minDate: date_diff,
            maxDate: maxDate_d
        }); 


        var from = new Date();
        var date_diff = Math.ceil((from.getTime() - Date.parse(today)) / 86400000);
        var maxDate_d = date_diff+30+'d';
        date_diff = date_diff + 'd';
        $('#start_date').datepicker({
            dateFormat: 'yy-mm-dd',
            minDate: maxDate_d
        });

        $('#start_date').change(function () {

            var from = $('#start_date').datepicker('getDate');
            var date_diff = Math.ceil((from.getTime() - Date.parse(today)) / 86400000);
            var maxDate_d = date_diff+30+'d';
            date_diff = date_diff + 'd';
            $('#end_date').datepicker({
                dateFormat: 'yy-mm-dd',
                minDate: date_diff
            });
        });  

});



function completeappointment(id){
    $("#appointment_id").val(id);
}

function pleaselogin(){
    // swal($("#Errorlabel").val(),$("#loginmsg").val(),"error"); 
    //  Swal.fire({
    //   title: 'To book appointment you must login first, please proceed with login now.',
    //   showDenyButton: false,
    //   showCancelButton: true,
    //   confirmButtonColor: "#ff9136",
    //   confirmButtonText: `Yes`,
    //   cancelButtonText: 'No',
     
    // }).then((result) => {
    //   if (result.isConfirmed) {
    //     window.location.href = $("#siteurl").val()+'/patientlogin';
    //   }
    // })
    
     
    Swal.fire({
      title: 'you must login first',
      text: "To book appointment you must login first, please proceed with login now.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, Login Now!'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = $("#siteurl").val()+'/patientlogin';
      }
    })
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

function ValidateEmail(mail) 
{
 if (/^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/.test(mail))
  {
    return (true)
  }
  
  return (false)
}

function checkbothpassword(value){
   var pwd=$("#pwd").val();
   if(pwd!=value){
       var txt='<div class="col-sm-12"><div class="alert  alert-danger alert-dismissible fade show" role="alert">'+$("#pwdmatch").val()+'<button type="button"  class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div></div>';
       $("#registererror").html(txt);
       $("#cpwd").val("");
   }
}

function slotchange(s_id){
  $.ajax({
      url: $("#siteurl").val()+"/getslotlist",
      data:{s_id:s_id,date:$("#date").val(),"doctor_id":$("#doctor_id").val()},
      method:"get",
      success: function( data ) {
        console.log(data);
        if(data==0){
                $("#slotdiv").html('<p style="color: red;margin-left: 10px;">'+$("#doctornotavilable").val()+'</p>');
        }else{
             var str=JSON.parse(data);       
             var txt="";
             for(var i=0;i<str.length;i++){
                 txt=txt+'<li>';
                 if(str[i].is_book=='1'){
                    txt=txt+'<input type="radio" value="'+str[i].id+"#"+str[i].slot+'" name="slot" id="'+str[i].id+'" disabled/><label class="custom-radio-disabled" for="'+str[i].id+'">'+str[i].slot+'</label>';
                 }else{
                    txt=txt+'<input type="radio" value="'+str[i].id+"#"+str[i].slot+'" name="slot" id="'+str[i].id+'"/><label for="'+str[i].id+'">'+str[i].slot+'</label>';
                 }
                 txt=txt+'</li>';            
             }
             $("#slotdiv").html(txt);
        }
                   
      }
  });
}

function checkcurrentpwd(val){
  $.ajax({
      url: $("#siteurl").val()+"/checkuserpwd",
      data:{cpwd:val},
      method:"get",
      success: function( data ) {
         if(data=='0'){
            var txt='<div class="col-sm-12"><div class="alert  alert-danger alert-dismissible fade show" role="alert">'+$("#currentpwdwrong").val()+'<button type="button"  class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div></div>';
            $("#registererror").html(txt);
            $("#opwd").val("");
         }          
      }
  });
}

function checkdoctorcurrentpwd(val){
  $.ajax({
      url: $("#siteurl").val()+"/checkdoctorpwd",
      data:{cpwd:val},
      method:"get",
      success: function( data ) {
         if(data=='0'){
            var txt='<div class="col-sm-12"><div class="alert  alert-danger alert-dismissible fade show" role="alert">'+$("#currentpwdwrong").val()+'<button type="button"  class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div></div>';
            $("#registererror").html(txt);
            $("#opwd").val("");
         }          
      }
  });
}

function slotdivchange(date){
  $.ajax({
      url: $("#siteurl").val()+"/getschedule",
      data:{date:date,doctor_id:$("#doctor_id").val()},
      method:"get",
      success: function( data ) {
        $("#timerange").css("display","none");
        $("#slotdiv").html("");
        var firsttime="";
        var str=JSON.parse(data);    
        var txt='<select class="" name="slottime" id="slottime" required="" onchange="slotchange(this.value)">';   
        for(var i=0;i<str.length;i++){
            if(i==0){
              firsttime=str[i].start_time+" - "+str[i].end_time;
              slotchange(str[i].id);
            }
            txt=txt+'<option value="'+str[i].id+'">'+str[i].start_time+" - "+str[i].end_time+'</option>';
        }
        txt=txt+'</select>';
       /* txt=txt+'<div class="nice-select" tabindex="0"><span class="current">'+firsttime+'</span><ul class="list">';
        for(var i=0;i<str.length;i++){
            if(i==0){
                txt=txt+'<li data-value="'+str[i].id+'" class="option selected focus">'+str[i].start_time+" - "+str[i].end_time+'</li>';
            }else{
                txt=txt+'<li data-value="'+str[i].id+'" class="option">'+str[i].start_time+" - "+str[i].end_time+'</li>';
            }     
        }*/
        //txt=txt+'</ul></div>';       
        $("#timerange").html(txt);
        if(str.length!=0){
            $("#timerange").css("display","inline-block");
        }
         
         
       
      }
  });
}

function userfavorite(id){
  $.ajax({
      url: $("#siteurl").val()+"/userfavorite"+'/'+id,
      method:"get",
      success: function( data ) {
          var str=JSON.parse(data);
          var txt='<div class="col-sm-12"><div class="alert  '+str['class']+' alert-dismissible fade show" role="alert">'+str["msg"]+'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div></div>';
          $("#favmsg").html(txt);
          if(str['op']=='1'){
            $("#favdoc"+id).addClass("activefav");
          }else{
            $("#favdoc"+id).removeClass("activefav");
          }
      }
  });
}

function userfavoritedashboard(id){
  $.ajax({
      url: $("#siteurl").val()+"/userfavorite"+'/'+id,
      method:"get",
      success: function( data ) {
          var str=JSON.parse(data);
          var txt='<div class="col-sm-12"><div class="alert  '+str['class']+' alert-dismissible fade show" role="alert">'+str["msg"]+'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div></div>';
          $("#favmsg").html(txt);
          if(str['op']=='1'){
            window.location.reload();
          }else{
            $("#favdoc"+id).removeClass("activefav");
          }
      }
  });
}

   $(document).ready(function () {
       var SITEURL = $("#siteurl").val();
       $.ajaxSetup({
           headers: {
               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
           }
       });
   
       var calendar = $('#calendar').fullCalendar({
           editable: true,
           events: SITEURL + "/fullcalendareventmaster",
           displayEventTime: true,
           editable: true,
           eventRender: function (event, element, view) {
               if (event.allDay === 'true') {
                   event.allDay = true;
               } else {
                   event.allDay = false;
               }
           },
           selectable: true,
           selectHelper: true,
           select: function (start, end, allDay) {                
           },
            
           eventDrop: function (event, delta) {
           },
           eventClick: function (event) {
               console.log(event.id);
               window.location.href=SITEURL+"/userdashboard";
           }
       });
   });
   
   function displayMessage(message) {
       $(".response").css('display','block');
       $(".response").html(""+message+"");
       setInterval(function() { $(".response").fadeOut(); }, 4000);
   }

   function addnewslot(day_id){
    var slotid=$("#total_slot_day_"+day_id).val();
    var txt='<div class="timing-slot-main-box" id="slotdiv'+day_id+''+slotid+'"><div class="doctors-sidebar"><div class="form-widget"><div class="form-inner"><div class="appointment-time"><div class="form-group"><input type="time" id="start_time_'+day_id+'_'+slotid+'" placeholder="'+$("#startvaltext").val()+'" name="arr['+day_id+'][start_time]['+slotid+']" onchange="checkduration('+day_id+','+slotid+')"></div><div class="form-group"><input type="time" id="end_time_'+day_id+'_'+slotid+'" name="arr['+day_id+'][end_time][]" onchange="checkduration('+day_id+','+slotid+')"></div><div class="custom-dropdown" id="timerange"><select class=""  name="arr['+day_id+'][duration]['+slotid+']" id="duration_'+day_id+'_'+slotid+'" onchange="getslot(this.value,'+day_id+','+slotid+')" ><option value="">'+$("#seldurationval").val()+'</option></select></div><div class="custom-btn-box btn-box" style="margin-left: 15px;"><a href="javascript:removescdehule('+day_id+','+slotid+')" class="theme-btn-one">'+$("#deletetext").val()+'</a></div><div class="slot-doctor-profile-main-box"><ul id="slot_'+day_id+'_'+slotid+'"></ul></div></div></div></div></div></div>';
    $("#day_"+day_id).append(txt);
    $("#total_slot_day_"+day_id).val(parseInt(slotid)+parseInt(1));
}

function checkduration(day_id,cid){
  
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
  console.log(d);
  if(d!="undefined"||d.length!=""){
    var h = parseInt(d.split(':')[0]);
    
        if (d.split(':')[1].split(' ')[1] == "PM") {
          h = h + 12;
        }
    }

    
    return 0;
}
function GetMinutes(d) {
    if(d.length!=0){
       return parseInt(d.split(':')[1].split(' ')[0]);
    }else{
       return
    }
    
}
function removescdehule(day_id,cid){
    $("#slotdiv"+day_id+cid).remove();
}
