<?php
//index.php

?>
<!DOCTYPE html>
<html>
   <head>
      <title>Project Management</title>
      <link rel="stylesheet" href="css/fullcalendar.css" />
     <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
      <!-- <link rel="stylesheet" href="css/bootstrap.css" /> -->
      <script src="javascript/jquery.min.js"></script>
      <script src="javascript/jquery-ui.min.js"></script>
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
      <script src="javascript/moment.min.js"></script>
      <script src="javascript/fullcalendar.min.js"></script>
      <style>
         button {
                  font-size:15px;
                  padding:5px;
                  border-radius:5px;
                  //margin:10px;
                  
         }
         #btn-container {
                  text-align:center;
         }
         #footer {  
            position: fixed;  
            bottom: 0;  
            z-index: 100;  
         }
         #plan_list {
            padding-top:10px;
         }
         .plan-item{
            text-align:center;
            padding: 5px;
         }
         .plan-item:hover{
            background: #eee;
         }
      </style>
   </head>
 <body>
   <br />
   <h2 align="center"><a href="#">MCI Project Management</a></h2>
   <br />
   <div class="container">
      <div id="left-side" class="col-md-2 text-center">
         <h4 style="color: red">Plans for a Week</h4>
         <div id="plan_list">
            
         </div>
      </div>
      <div id="calendar" class="col-md-10"></div>
   </div>
   
   <div class="footer">
      <p>
         <div id="btn-container"><br>
            <input type="button" class="btn btn-primary" onClick="location.href='timeSheet-master/index.html'" value='Event details'>
         </div>
      </p>
   </div>
<!-- Modal -->

<div class="modal fade" id="editModal" role="dialog">
   <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Edit Plan</h4>
         </div>
         <div class="modal-body">
            <form>
               <input type="hidden" name="id" value="">
               <input type="hidden" name="start" value="">
               <input type="hidden" name="end" value="">
               <div class="form-group">
                  <label for="title">Title:</label>
                  <input type="text" class="form-control" name="title" value="" id="title" placeholder="Title">
               </div>
               <div class="form-group">
                  <label for="content">Description:</label>
                  <textarea class="form-control" value="" name="content" id="content" rows="6" placeholder="Description"></textarea> 
               </div>
            </form>
         </div>
            <div class="modal-footer">
               <button type="button" name="save-btn" class="btn btn-primary" data-dismiss="modal">Save</button>
               <button type="button" name="delete-btn"class="btn btn-alert" data-dismiss="modal">Delete plan</button>
               <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
         </div>
      </div>
   </div>
</div>
 
  
  <script>
   $(document).ready(function() {
      var mode = "month";
      var months = "Jan_Feb_Mar_Apr_May_Jun_Jul_Aug_Sep_Oct_Nov_Dec".split("_");
      //agenda mode change
      $('#calendar').on('click', '.fc-right button.fc-button', function(){
         mode = $(this).text();
      });
      $('#calendar').on('DOMSubtreeModified', '.fc-center h2', function() {
         if($(this).text() != "" && $("#calendar").find('.fc-state-active').text() == 'week'){
            //get the start day of the week
            let week_str = $(this).text();
            let year_str = Number(week_str.split(',')[1]).toString();
            let month_str = months.indexOf(week_str.split(' ')[0]) + 1;
            let day_str = week_str.split(' ')[1];
            let start_date = year_str + '-' + month_str + '-' + day_str;
            $.ajax({
               url:"read.php",
               type:"POST",
               data:{week:'week', start_date:start_date},
               success:function(res) {
                  let plans = JSON.parse(res);
                  let html_str = '';
                  let mins = 0;
                  for(var i = 0; i< plans.length; i++){
                     mins += plans[i]['interval'];
                     html_str += '<div class="plan-item"><h4>'+ plans[i]['title']  + ' :  ' + (plans[i]['interval']-plans[i]['interval']%60)/60 + ':' + plans[i]['interval']%60 +'</h4></div>';
                  }
                  html_str += '<br><b>Total: ' + plans.length + '</b><br><b>Sum of hours: ' + (mins-mins%60)/60 + ':' + mins%60;
                  $('#plan_list').html(html_str);
                  console.log(html_str);
               }
            });
         }
            
      });
      var calendar = $('#calendar').fullCalendar({
         editable:true,
         header:{
            left:'prev,next today',
            center:'title',
            right:'month,agendaWeek,agendaDay'
         },
         events: 'load.php',
         selectable:true,
         selectHelper:true,
         select: function(start, end, allDay)
         {
            var title = prompt("Enter Event Title");
            if(title)
            {
               var start = $.fullCalendar.formatDate(start, "Y-MM-DD HH:mm:ss");
               var end = $.fullCalendar.formatDate(end, "Y-MM-DD HH:mm:ss");
               $.ajax({
                  url:"insert.php",
                  type:"POST",
                  data:{title:title, start:start, end:end},
                  success:function()
                  {
                  calendar.fullCalendar('refetchEvents');
                  alert("Added Successfully");
                  }
               });
            }
         },
         editable:true,
         eventResize:function(event)
         {
            var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD HH:mm:ss");
            var end = $.fullCalendar.formatDate(event.end, "Y-MM-DD HH:mm:ss");
            var title = event.title;
            var id = event.id;
            $.ajax({
               url:"update.php",
               type:"POST",
               data:{title:title, start:start, end:end, id:id},
               success:function(){
               calendar.fullCalendar('refetchEvents');
               alert('Event Update');
               }
            });
         },

         eventDrop:function(event)
         {
            var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD HH:mm:ss");
            var end = $.fullCalendar.formatDate(event.end, "Y-MM-DD HH:mm:ss");
            var title = event.title;
            var id = event.id;
            $.ajax({
               url:"update.php",
               type:"POST",
               data:{title:title, start:start, end:end, id:id},
               success:function()
               {
               calendar.fullCalendar('refetchEvents');
               alert("Event Updated");
               }
            });
         },

         eventClick:function(event)
         {
            $.ajax({
               url:"read.php",
               type:"POST",
               data:{id:event.id},
               success:function(res)
               {
                  res_obj = JSON.parse(res);
                  $('input[name="id"]').val(event.id);
                  $('input[name="start"]').val(res_obj['start_event']);
                  $('input[name="end"]').val(res_obj['end_event']);
                  $('input[name="title"]').val(res_obj['title']);
                  $('textarea[name="content"]').text(res_obj['description']);
               }
            });
            $('#editModal').modal('show');
            // if(confirm("Are you sure you want to remove it?"))
            // {
            //    var id = event.id;
            //    $.ajax({
            //    url:"delete.php",
            //    type:"POST",
            //    data:{id:id},
            //    success:function()
            //    {
            //    calendar.fullCalendar('refetchEvents');
            //    alert("Event Removed");
            //    }
            //    })
            // }
         },
      });
      $('button[name="save-btn"]').click(function(){
         $.ajax({
            url:"update.php",
            type:"POST",
            data:{
               id:$('input[name="id"]').val(),
               title:$('input[name="title"]').val(),
               start:$('input[name="start"]').val(),
               end:$('input[name="end"]').val(),
               content:$('textarea[name="content"]').val(),
            },
            success:function()
            {
               calendar.fullCalendar('refetchEvents');
               alert("Updated Successfully");
            }
         });
      });

      $('button[name="delete-btn"]').click(function(){
         if(confirm("Are you sure you want to remove it?"))
         {
            var id = $('input[name="id"]').val();
            $.ajax({
               url:"delete.php",
               type:"POST",
               data:{id:id},
               success:function()
               {
               calendar.fullCalendar('refetchEvents');
               alert("Event Removed");
               }
            });
         }
      });
   });
   
  </script>
               
            
  

 </body>
</html>