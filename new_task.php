<?php include 'db_connect.php' ?>
<link rel="stylesheet" href="calendar/css/fullcalendar.css" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
      <!-- <link rel="stylesheet" href="css/bootstrap.css" /> -->
<script src="calendar/javascript/moment.min.js"></script>
<script src="calendar/javascript/fullcalendar.min.js"></script>
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
<?php
if(isset($_GET['id'])){
	$type_arr = array('',"Admin","Project Manager","Employee");
	$qry = $conn->query("SELECT *,concat(firstname,' ',lastname) as name FROM users where id = ".$_GET['id'])->fetch_array();
foreach($qry as $k => $v){
	$$k = $v;
}
}
?>
<div class="container-fluid">
	<div class="container">
		<input type="hidden" name="project_id" value="<?php echo $_GET['pid']?>">
    	<div id="calendar" class="col-md-12">
		</div>
   	</div>
</div>
<div class="modal fade" id="editModal" role="dialog">
   <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title text-center">Edit Task</h4>
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
<style>
	#uni_modal .modal-footer{
		display: none
	}
	#uni_modal .modal-footer.display{
		display: flex
	}
</style>
<script>
   $(document).ready(function() {
      var mode = "month";
      var months = "Jan_Feb_Mar_Apr_May_Jun_Jul_Aug_Sep_Oct_Nov_Dec".split("_");
      //agenda mode change
      var calendar = $('#calendar').fullCalendar({
         editable:true,
         header:{
            left:'prev,next today',
            center:'title',
            right:'month,agendaWeek,agendaDay'
         },
         events: 'calendar/load.php?project_id='+$('input[name="project_id"]').val(),
		 data: {project_id: $('input[name="project_id"]').val()},
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
                  url:"calendar/insert.php",
                  type:"POST",
                  data:{title:title, start:start, end:end, project_id: $('input[name="project_id"]').val()},
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
               url:"calendar/update.php",
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
               url:"calendar/update.php",
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
               url:"calendar/read.php",
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
            $('#editModal').modal();
			$('#editModal').addClass('in');
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
            url:"calendar/update.php",
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
               url:"calendar/delete.php",
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