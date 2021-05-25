<?php 
$connect = new PDO('mysql:host=localhost;dbname=tms_db', 'root', '');
 
$today = new DateTime();
if(isset($_GET["year"]) && isset($_GET["week"])){
    $year = $_GET["year"];
    $week = $_GET["week"];
} else {
    $year = $today->format('Y');
    $week = $today->format('W');
}

function getStartAndEndDate($week, $year) {
    $dto = new DateTime();
    $dto->setISODate($year, $week);
    $ret['week_start'] = $dto->format('Y-m-d 00:00:00');
    $dto->modify('+6 days');
    $ret['week_end'] = $dto->format('Y-m-d 00:00:00');
    return $ret;
}
//get task for the week requested
    $data = array();
    $where = "";
    if($_SESSION['login_type'] != 4){
        $where = " p.manager_id = '{$_SESSION['login_id']}' ";
    }
    $start_date = getStartAndEndDate($week, $year)['week_start'];
    $end_date = getStartAndEndDate($week, $year)['week_end'];
    $query = 'SELECT t.*, p.id as project_id, p.name as project_name, u.firstname as fname, u.lastname as lname FROM task_list t join project_list p on p.id = t.project_id join users u on u.id = t.user_id WHERE t.start_event < :end_date AND t.end_event > :start_date AND'. $where.'order by t.project_id ASC';
    
    $statement = $connect->prepare($query);
    $statement->execute(
        array(
            'start_date' => $start_date,
            'end_date' => $end_date
        )
    );
    $plans = $statement->fetchAll();
    foreach($plans as $row){
        //calculate the time interval of each plan
        $plan_interval = abs(strtotime($row["start_event"]) - strtotime($row["end_event"])) / 60; //period of plan for minutes
        //calculate the available time interval of each plan for certain week
        $interval = $plan_interval;
        if((strtotime($start_date) - strtotime($row["start_event"])) > 0)
            $interval = $plan_interval - (strtotime($start_date) - strtotime($row["start_event"]))/60;
        if((strtotime($row["end_event"]) - strtotime($end_date)) > 0)
            $interval = $interval - (strtotime($row["end_event"]) - strtotime($end_date))/60;
        $data[] = array(
            'id' => $row["id"],
            'project_id' =>$row["project_id"],
            'project_name' => $row["project_name"],
            'start' => $row["start_event"],
            'end' => $row["end_event"],
            'title' => $row["task"],
            'plan_interval' => $plan_interval,
            'interval' => $interval,
            'status' => $row['status'],
            'user_name' => $row['fname']. ' '. $row['lname'],
            'description' => $row['description']
        );
    }
?>
<div class="row">
  <div class="col-md-3" style="margin-bottom: 1rem;"> 
    <a type="button" href="./index.php?page=reports&week=<?php echo $week-1?>&year=<?php echo $year?>" class="btn btn-primary">Prev Week</a>
    <span class="" style="margin-left: 1rem; margin-right: 1rem"><a href="./index.php?page=reports">today</a></span>
    <a type="button" href="./index.php?page=reports&week=<?php echo $week+1?>&year=<?php echo $year?>" class="btn btn-primary">Next Week</a>
  </div>
  <div class="col-md-9 text-center" style="margin-bottom: 1rem;">
    <h4><?php echo $start_date . ' - '. $end_date ?></h4>
  </div>
</div>
<div class="col-md-12">
  <div class="card card-outline card-success">
    <div class="card-header">
      <b>Project Planned Tasks</b>
      
      <div class="card-tools">
        <button class="btn btn-flat btn-sm bg-gradient-success btn-success" id="print1"><i class="fa fa-print"></i> Print</button>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive" id="printable1">
        <table class="table m-0 table-bordered">
          <!--  <colgroup>
            <col width="5%">
            <col width="30%">
            <col width="35%">
            <col width="15%">
            <col width="15%">
          </colgroup> -->
          <thead>
            <th>#</th>
            <th>Project</th>
            <th>Task</th>
            <th>Done By</th>
            <th>Work Duration</th>
          </thead>
          <tbody>
          <?php
          $i = 1;
          $stat = array("Pending","Started","On-Progress","On-Hold","Over Due","Done");
          foreach($data as $row){
            ?>
            <tr id="<?php echo $row['id'] ?>">
                <td>
                    <?php echo $i++ ?>
                </td>
                <td>
                    <a>
                        <?php echo ucwords($row['project_name']) ?>
                    </a>
                    <br>
                    <small>
                        Due: <?php echo date("Y-m-d",strtotime($row['end'])) ?>
                    </small>
                </td>
                <td class="text-center">
                  <?php echo $row['title'] ?>
                </td>
                <td class="text-center">
                  <?php echo $row['user_name'] ?>
                </td>
                <td class="text-center">
                  <?php echo number_format($row['interval']/60).' Hr/s.' ?>
                </td>
            </tr>
          <?php } ?>
          </tbody>  
        </table>
      </div>
    </div>
  </div>
</div>
<div class="col-md-12">
  <div class="card card-outline card-success">
    <div class="card-header">
      <b>Project Current Tasks</b>
      
      <div class="card-tools">
        <button class="btn btn-flat btn-sm bg-gradient-success btn-success print_btn" id="print2" ><i class="fa fa-print"></i> Print</button>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive" id="printable2">
        <table class="table m-0 table-bordered">
          <!--  <colgroup>
            <col width="5%">
            <col width="30%">
            <col width="35%">
            <col width="15%">
            <col width="15%">
          </colgroup> -->
          <thead>
            <th>#</th>
            <th>Project</th>
            <th>Task</th>
            <th>Done By</th>
            <th>Work Duration</th>
            <th>Description</th>
            <th>Status</th>
          </thead>
          <tbody>
          <?php
          $i = 1;
          $stat = array("Pending","Started","On-Progress","On-Hold","Over Due","Done");
          foreach($data as $row){
            ?>
            <tr id="<?php echo $row['id'] ?>">
                <td>
                    <?php echo $i++ ?>
                </td>
                <td>
                    <a>
                        <?php echo ucwords($row['project_name']) ?>
                    </a>
                    <br>
                    <small>
                        Due: <?php echo date("Y-m-d",strtotime($row['end'])) ?>
                    </small>
                </td>
                <td class="text-center">
                  <?php echo $row['title'] ?>
                </td>
                <td class="text-center">
                  <?php echo $row['user_name'] ?>
                </td>
                <td class="text-center">
                  <?php echo number_format($row['interval']/60).' Hr/s.' ?>
                </td>
                <td class="text-center">
                  <?php echo $row['description'] ?>
                </td>
                <td class="project-state">
                    <?php
                      if($stat[$row['status']] =='Pending'){
                        echo "<span class='badge badge-secondary'>{$stat[$row['status']]}</span>";
                      }elseif($stat[$row['status']] =='Started'){
                        echo "<span class='badge badge-primary'>{$stat[$row['status']]}</span>";
                      }elseif($stat[$row['status']] =='On-Progress'){
                        echo "<span class='badge badge-info'>{$stat[$row['status']]}</span>";
                      }elseif($stat[$row['status']] =='On-Hold'){
                        echo "<span class='badge badge-warning'>{$stat[$row['status']]}</span>";
                      }elseif($stat[$row['status']] =='Over Due'){
                        echo "<span class='badge badge-danger'>{$stat[$row['status']]}</span>";
                      }elseif($stat[$row['status']] =='Done'){
                        echo "<span class='badge badge-success'>{$stat[$row['status']]}</span>";
                      }
                    ?>
                </td>
            </tr>
          <?php } ?>
          </tbody>  
        </table>
      </div>
    </div>
  </div>
</div>
<script>
	$('#print1').click(function(){
		start_load()
		var _h = $('head').clone()
		var _p = $('#printable1').clone()
		var _d = "<p class='text-center'><b>Project Progress Report as of (<?php echo date("F d, Y") ?>)</b></p>"
		_p.prepend(_d)
		_p.prepend(_h)
		var nw = window.open("","","width=900,height=600")
		nw.document.write(_p.html())
		nw.document.close()
		nw.print()
		setTimeout(function(){
			nw.close()
			end_load()
		},750)
	});
  $('#print2').click(function(){
		start_load()
		var _h = $('head').clone()
		var _p = $('#printable2').clone()
		var _d = "<p class='text-center'><b>Project Progress Report as of (<?php echo date("F d, Y") ?>)</b></p>"
		_p.prepend(_d)
		_p.prepend(_h)
		var nw = window.open("","","width=900,height=600")
		nw.document.write(_p.html())
		nw.document.close()
		nw.print()
		setTimeout(function(){
			nw.close()
			end_load()
		},750)
	})
</script>